<?php
/**
 * Plugin Name: Gravity Forms VIN Decoder Add-On
 * Plugin URI: https://example.com/vin-decoder-addon
 * Description: Decodes VIN numbers using third-party APIs and automatically fills mapped Gravity Forms fields
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: vin-decoder-addon
 * Domain Path: /languages
 * Requires at least: 6.6
 * Requires PHP: 8.2
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('VIN_DECODER_ADDON_VERSION', '1.0.0');
define('VIN_DECODER_ADDON_PATH', plugin_dir_path(__FILE__));
define('VIN_DECODER_ADDON_URL', plugin_dir_url(__FILE__));
define('VIN_DECODER_ADDON_BASENAME', plugin_basename(__FILE__));

/**
 * Load plugin text domain for translations
 */
function vin_decoder_addon_load_textdomain() {
    load_plugin_textdomain(
        'vin-decoder-addon',
        false,
        dirname(VIN_DECODER_ADDON_BASENAME) . '/languages'
    );
}
add_action('plugins_loaded', 'vin_decoder_addon_load_textdomain');

/**
 * Check if Gravity Forms is active
 */
function vin_decoder_addon_check_dependencies() {
    if (!class_exists('GFForms')) {
        add_action('admin_notices', 'vin_decoder_addon_missing_gf_notice');
        deactivate_plugins(VIN_DECODER_ADDON_BASENAME);
        return false;
    }
    return true;
}

/**
 * Display admin notice if Gravity Forms is not active
 */
function vin_decoder_addon_missing_gf_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            echo esc_html__(
                'Gravity Forms VIN Decoder Add-On requires Gravity Forms to be installed and activated.',
                'vin-decoder-addon'
            );
            ?>
        </p>
    </div>
    <?php
}

/**
 * Include required files
 */
function vin_decoder_addon_include_files() {
    // Helper functions
    require_once VIN_DECODER_ADDON_PATH . 'includes/helpers.php';
    
    // Core classes
    require_once VIN_DECODER_ADDON_PATH . 'includes/class-logger.php';
    require_once VIN_DECODER_ADDON_PATH . 'includes/class-vin-api-client.php';
    require_once VIN_DECODER_ADDON_PATH . 'includes/class-vin-autofill.php';
    require_once VIN_DECODER_ADDON_PATH . 'includes/class-ajax-handler.php';
    require_once VIN_DECODER_ADDON_PATH . 'includes/class-admin-settings.php';
    require_once VIN_DECODER_ADDON_PATH . 'includes/class-vin-decoder-addon.php';
    
    // Optional: License verifier for Envato
    if (file_exists(VIN_DECODER_ADDON_PATH . 'includes/class-license-verifier.php')) {
        require_once VIN_DECODER_ADDON_PATH . 'includes/class-license-verifier.php';
    }
}

/**
 * Initialize the plugin
 */
function vin_decoder_addon_init() {
    // Check dependencies
    if (!vin_decoder_addon_check_dependencies()) {
        return;
    }
    
    // Include files
    vin_decoder_addon_include_files();
    
    // Initialize main add-on class
    VIN_Decoder_Addon::get_instance();
    
    // Initialize admin settings
    VIN_Admin_Settings::get_instance();
    
    // Initialize AJAX handler
    VIN_Ajax_Handler::get_instance();
}
add_action('plugins_loaded', 'vin_decoder_addon_init');

/**
 * Enqueue admin assets
 */
function vin_decoder_addon_enqueue_admin_assets($hook) {
    // Only load on our settings page
    if ($hook !== 'settings_page_vin-decoder-addon') {
        return;
    }
    
    // Enqueue admin CSS
    wp_enqueue_style(
        'vin-decoder-admin',
        VIN_DECODER_ADDON_URL . 'assets/css/admin.css',
        array(),
        VIN_DECODER_ADDON_VERSION
    );
    
    // Enqueue admin JS
    wp_enqueue_script(
        'vin-decoder-admin',
        VIN_DECODER_ADDON_URL . 'assets/js/admin.js',
        array('jquery'),
        VIN_DECODER_ADDON_VERSION,
        true
    );
    
    // Localize script for AJAX
    wp_localize_script('vin-decoder-admin', 'vinDecoderAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vin_decoder_admin_nonce'),
        'strings' => array(
            'confirmDelete' => __('Are you sure you want to delete this mapping?', 'vin-decoder-addon'),
            'savingChanges' => __('Saving changes...', 'vin-decoder-addon'),
            'changesSaved' => __('Changes saved successfully!', 'vin-decoder-addon'),
            'errorOccurred' => __('An error occurred. Please try again.', 'vin-decoder-addon'),
        ),
    ));
}
add_action('admin_enqueue_scripts', 'vin_decoder_addon_enqueue_admin_assets');

/**
 * Enqueue frontend assets
 */
function vin_decoder_addon_enqueue_frontend_assets() {
    // Only load when Gravity Forms is present on the page
    if (!class_exists('GFForms')) {
        return;
    }
    
    // Enqueue frontend JS
    wp_enqueue_script(
        'vin-decoder-frontend',
        VIN_DECODER_ADDON_URL . 'assets/js/frontend.js',
        array('jquery'),
        VIN_DECODER_ADDON_VERSION,
        true
    );
    
    // Localize script for AJAX
    wp_localize_script('vin-decoder-frontend', 'vinDecoderFrontend', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('vin_decoder_frontend_nonce'),
        'strings' => array(
            'decoding' => __('Decoding VIN...', 'vin-decoder-addon'),
            'decodingComplete' => __('VIN decoded successfully!', 'vin-decoder-addon'),
            'decodingError' => __('Error decoding VIN. Please check the VIN and try again.', 'vin-decoder-addon'),
            'invalidVin' => __('Invalid VIN format. VIN must be 17 characters.', 'vin-decoder-addon'),
        ),
    ));
}
add_action('wp_enqueue_scripts', 'vin_decoder_addon_enqueue_frontend_assets');

/**
 * Activation hook
 */
function vin_decoder_addon_activate() {
    // Set default options
    $default_options = array(
        'api_provider' => 'nhtsa',
        'api_key' => '',
        'enable_logging' => false,
        'log_level' => 'error',
        'field_mapping' => array(),
    );
    
    add_option('vin_decoder_addon_settings', $default_options);
    
    // Create log directory if logging is enabled
    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/vin-decoder-logs';
    
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
        
        // Add .htaccess to protect log files
        $htaccess_content = "Order deny,allow\nDeny from all";
        file_put_contents($log_dir . '/.htaccess', $htaccess_content);
    }
}
register_activation_hook(__FILE__, 'vin_decoder_addon_activate');

/**
 * Deactivation hook
 */
function vin_decoder_addon_deactivate() {
    // Clean up if needed
    // Note: We don't delete options here in case user wants to reactivate
}
register_deactivation_hook(__FILE__, 'vin_decoder_addon_deactivate');
