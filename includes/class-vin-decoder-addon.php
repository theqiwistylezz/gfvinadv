<?php
/**
 * Main VIN Decoder Add-On Class
 *
 * Integrates with Gravity Forms Add-On Framework
 *
 * @package VIN_Decoder_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VIN_Decoder_Addon class
 */
class VIN_Decoder_Addon {
    /**
     * Singleton instance
     *
     * @var VIN_Decoder_Addon
     */
    private static $instance = null;
    
    /**
     * API client instance
     *
     * @var VIN_API_Client
     */
    private $api_client;
    
    /**
     * Autofill instance
     *
     * @var VIN_Autofill
     */
    private $autofill;
    
    /**
     * Logger instance
     *
     * @var VIN_Logger
     */
    private $logger;
    
    /**
     * Get singleton instance
     *
     * @return VIN_Decoder_Addon
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->api_client = VIN_API_Client::get_instance();
        $this->autofill = VIN_Autofill::get_instance();
        $this->logger = VIN_Logger::get_instance();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Add custom field settings to Gravity Forms
        add_action('gform_field_standard_settings', array($this, 'add_vin_field_settings'), 10, 2);
        add_action('gform_editor_js', array($this, 'add_vin_field_settings_js'));
        
        // Add VIN decoder meta box to form editor
        add_filter('gform_tooltips', array($this, 'add_vin_tooltips'));
        
        // Add custom CSS class to VIN fields
        add_filter('gform_field_css_class', array($this, 'add_vin_field_class'), 10, 3);
        
        // Add data attribute to VIN fields
        add_filter('gform_field_content', array($this, 'add_vin_field_attribute'), 10, 5);
    }
    
    /**
     * Add VIN field settings to Gravity Forms editor
     *
     * @param int $position Position in settings
     * @param int $form_id Form ID
     */
    public function add_vin_field_settings($position, $form_id) {
        if ($position === 25) {
            ?>
            <li class="vin_decoder_setting field_setting">
                <input type="checkbox" id="field_vin_decoder_enable" onclick="SetFieldProperty('vinDecoderEnable', this.checked);" />
                <label for="field_vin_decoder_enable" class="inline">
                    <?php esc_html_e('Enable VIN Decoder', 'vin-decoder-addon'); ?>
                    <?php gform_tooltip('vin_decoder_enable'); ?>
                </label>
            </li>
            <?php
        }
    }
    
    /**
     * Add JavaScript for VIN field settings
     */
    public function add_vin_field_settings_js() {
        ?>
        <script type="text/javascript">
            // Add VIN decoder setting to text fields
            fieldSettings.text += ', .vin_decoder_setting';
            
            // Bind to the load field settings event
            jQuery(document).on('gform_load_field_settings', function(event, field, form) {
                jQuery('#field_vin_decoder_enable').prop('checked', field.vinDecoderEnable == true);
            });
        </script>
        <?php
    }
    
    /**
     * Add VIN decoder tooltips
     *
     * @param array $tooltips Existing tooltips
     * @return array Modified tooltips
     */
    public function add_vin_tooltips($tooltips) {
        $tooltips['vin_decoder_enable'] = sprintf(
            '<h6>%s</h6>%s',
            esc_html__('VIN Decoder', 'vin-decoder-addon'),
            esc_html__('Enable automatic VIN decoding for this field. When a valid 17-character VIN is entered, the plugin will automatically decode it and populate mapped fields.', 'vin-decoder-addon')
        );
        
        return $tooltips;
    }
    
    /**
     * Add custom CSS class to VIN fields
     *
     * @param string $classes Existing classes
     * @param object $field Field object
     * @param array $form Form object
     * @return string Modified classes
     */
    public function add_vin_field_class($classes, $field, $form) {
        if (isset($field->vinDecoderEnable) && $field->vinDecoderEnable) {
            $classes .= ' vin-field';
        }
        
        return $classes;
    }
    
    /**
     * Add data attribute to VIN fields
     *
     * @param string $field_content Field HTML content
     * @param object $field Field object
     * @param string $value Field value
     * @param int $entry_id Entry ID
     * @param int $form_id Form ID
     * @return string Modified field content
     */
    public function add_vin_field_attribute($field_content, $field, $value, $entry_id, $form_id) {
        if (isset($field->vinDecoderEnable) && $field->vinDecoderEnable) {
            // Add data attribute to input field
            $field_content = str_replace(
                '<input ',
                '<input data-vin-decoder="true" ',
                $field_content
            );
        }
        
        return $field_content;
    }
    
    /**
     * Get plugin version
     *
     * @return string Plugin version
     */
    public function get_version() {
        return VIN_DECODER_ADDON_VERSION;
    }
    
    /**
     * Get plugin status
     *
     * @return array Plugin status information
     */
    public function get_status() {
        $settings = vin_decoder_get_settings();
        
        return array(
            'version' => $this->get_version(),
            'api_provider' => isset($settings['api_provider']) ? $settings['api_provider'] : 'nhtsa',
            'api_configured' => !empty($settings['api_key']) || $settings['api_provider'] === 'nhtsa',
            'logging_enabled' => isset($settings['enable_logging']) ? $settings['enable_logging'] : false,
            'field_mappings_count' => count(vin_decoder_get_field_mapping()),
        );
    }
}
