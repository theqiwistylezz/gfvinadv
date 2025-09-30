<?php
/**
 * AJAX Handler Class
 *
 * Handles AJAX requests for VIN decoding
 *
 * @package VIN_Decoder_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VIN_Ajax_Handler class
 */
class VIN_Ajax_Handler {
    /**
     * Singleton instance
     *
     * @var VIN_Ajax_Handler
     */
    private static $instance = null;
    
    /**
     * API client instance
     *
     * @var VIN_API_Client
     */
    private $api_client;
    
    /**
     * Logger instance
     *
     * @var VIN_Logger
     */
    private $logger;
    
    /**
     * Get singleton instance
     *
     * @return VIN_Ajax_Handler
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
        $this->logger = VIN_Logger::get_instance();
        
        // Register AJAX handlers
        add_action('wp_ajax_vin_decode_and_autofill', array($this, 'handle_decode_request'));
        add_action('wp_ajax_nopriv_vin_decode_and_autofill', array($this, 'handle_decode_request'));
        
        // Admin AJAX handlers
        add_action('wp_ajax_vin_test_api_connection', array($this, 'handle_test_connection'));
        add_action('admin_post_vin_decoder_clear_logs', array($this, 'handle_clear_logs'));
    }
    
    /**
     * Handle VIN decode and autofill request
     */
    public function handle_decode_request() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vin_decoder_frontend_nonce')) {
            $this->logger->warning('AJAX request failed nonce verification');
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'vin-decoder-addon'),
            ), 403);
        }
        
        // Get VIN from request
        $vin = isset($_POST['vin']) ? sanitize_text_field($_POST['vin']) : '';
        
        if (empty($vin)) {
            wp_send_json_error(array(
                'message' => __('VIN is required.', 'vin-decoder-addon'),
            ), 400);
        }
        
        // Validate VIN format
        if (!vin_decoder_validate_vin($vin)) {
            wp_send_json_error(array(
                'message' => __('Invalid VIN format. VIN must be 17 characters.', 'vin-decoder-addon'),
            ), 400);
        }
        
        // Get form ID
        $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
        
        if (empty($form_id)) {
            wp_send_json_error(array(
                'message' => __('Form ID is required.', 'vin-decoder-addon'),
            ), 400);
        }
        
        $this->logger->info('Processing AJAX VIN decode request', array(
            'vin' => $vin,
            'form_id' => $form_id,
        ));
        
        // Decode VIN using API
        $decoded_data = $this->api_client->decode_vin($vin);
        
        if (is_wp_error($decoded_data)) {
            $this->logger->error('VIN decode failed in AJAX handler', array(
                'vin' => $vin,
                'error' => $decoded_data->get_error_message(),
            ));
            
            wp_send_json_error(array(
                'message' => $decoded_data->get_error_message(),
            ), 500);
        }
        
        // Get field mapping for this form
        $field_mapping = $this->get_form_field_mapping($form_id);
        
        if (empty($field_mapping)) {
            $this->logger->warning('No field mapping found for form', array('form_id' => $form_id));
            wp_send_json_error(array(
                'message' => __('No field mapping configured for this form.', 'vin-decoder-addon'),
            ), 400);
        }
        
        // Map decoded data to form fields
        $mapped_fields = $this->map_data_to_fields($decoded_data, $field_mapping);
        
        $this->logger->info('VIN decoded and mapped successfully', array(
            'vin' => $vin,
            'form_id' => $form_id,
            'mapped_fields_count' => count($mapped_fields),
        ));
        
        // Return success with mapped data
        wp_send_json_success(array(
            'message' => __('VIN decoded successfully!', 'vin-decoder-addon'),
            'fields' => $mapped_fields,
            'raw_data' => $decoded_data,
        ));
    }
    
    /**
     * Handle API connection test
     */
    public function handle_test_connection() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vin_decoder_admin_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'vin-decoder-addon'),
            ), 403);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('Insufficient permissions.', 'vin-decoder-addon'),
            ), 403);
        }
        
        $this->logger->info('Testing API connection');
        
        // Test connection
        $result = $this->api_client->test_connection();
        
        if (is_wp_error($result)) {
            $this->logger->error('API connection test failed', array(
                'error' => $result->get_error_message(),
            ));
            
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
            ), 500);
        }
        
        $this->logger->info('API connection test successful');
        
        wp_send_json_success(array(
            'message' => __('API connection successful!', 'vin-decoder-addon'),
        ));
    }
    
    /**
     * Handle clear logs request
     */
    public function handle_clear_logs() {
        // Verify nonce
        if (!isset($_POST['vin_decoder_clear_logs_nonce']) || 
            !wp_verify_nonce($_POST['vin_decoder_clear_logs_nonce'], 'vin_decoder_clear_logs_nonce')) {
            wp_die(__('Security check failed.', 'vin-decoder-addon'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'vin-decoder-addon'));
        }
        
        // Clear logs
        $this->logger->clear_logs();
        
        // Redirect back
        wp_redirect(add_query_arg(
            array(
                'page' => 'vin-decoder-addon',
                'tab' => 'logging',
                'message' => 'logs_cleared',
            ),
            admin_url('options-general.php')
        ));
        exit;
    }
    
    /**
     * Get field mapping for a specific form
     *
     * @param int $form_id Form ID
     * @return array Field mapping for the form
     */
    private function get_form_field_mapping($form_id) {
        $all_mappings = vin_decoder_get_field_mapping();
        $form_mappings = array();
        
        if (!is_array($all_mappings)) {
            return $form_mappings;
        }
        
        foreach ($all_mappings as $mapping) {
            if (isset($mapping['form_id']) && absint($mapping['form_id']) === $form_id) {
                $form_mappings[] = $mapping;
            }
        }
        
        return $form_mappings;
    }
    
    /**
     * Map decoded VIN data to form fields
     *
     * @param array $decoded_data Decoded VIN data
     * @param array $field_mapping Field mapping configuration
     * @return array Mapped fields with values
     */
    private function map_data_to_fields($decoded_data, $field_mapping) {
        $mapped_fields = array();
        
        foreach ($field_mapping as $mapping) {
            $vin_field = isset($mapping['vin_field']) ? $mapping['vin_field'] : '';
            $gf_field_id = isset($mapping['gf_field_id']) ? $mapping['gf_field_id'] : '';
            
            if (empty($vin_field) || empty($gf_field_id)) {
                continue;
            }
            
            // Check if we have data for this VIN field
            if (isset($decoded_data[$vin_field]) && !empty($decoded_data[$vin_field])) {
                $value = $decoded_data[$vin_field];
                
                // Apply filter to allow customization
                $value = apply_filters(
                    'vin_decoder_field_value',
                    $value,
                    $vin_field,
                    $gf_field_id,
                    $decoded_data
                );
                
                $mapped_fields[$gf_field_id] = sanitize_text_field($value);
            }
        }
        
        return $mapped_fields;
    }
}
