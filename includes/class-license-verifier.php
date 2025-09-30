<?php
/**
 * License Verifier Class (Optional)
 *
 * Handles Envato purchase code verification for premium features
 *
 * @package VIN_Decoder_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VIN_License_Verifier class
 */
class VIN_License_Verifier {
    /**
     * Singleton instance
     *
     * @var VIN_License_Verifier
     */
    private static $instance = null;
    
    /**
     * Envato API endpoint
     *
     * @var string
     */
    private $api_endpoint = 'https://api.envato.com/v3/market/author/sale';
    
    /**
     * Logger instance
     *
     * @var VIN_Logger
     */
    private $logger;
    
    /**
     * Get singleton instance
     *
     * @return VIN_License_Verifier
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
        $this->logger = VIN_Logger::get_instance();
    }
    
    /**
     * Verify purchase code
     *
     * @param string $purchase_code Purchase code to verify
     * @param string $personal_token Envato personal token
     * @return bool|WP_Error True if valid, error otherwise
     */
    public function verify_purchase_code($purchase_code, $personal_token) {
        if (empty($purchase_code) || empty($personal_token)) {
            return new WP_Error(
                'missing_credentials',
                __('Purchase code and personal token are required.', 'vin-decoder-addon')
            );
        }
        
        $this->logger->info('Verifying purchase code');
        
        $url = $this->api_endpoint . '?code=' . urlencode($purchase_code);
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $personal_token,
                'User-Agent' => 'VIN Decoder Add-On',
            ),
        ));
        
        if (is_wp_error($response)) {
            $this->logger->error('License verification failed', array(
                'error' => $response->get_error_message(),
            ));
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code !== 200) {
            $this->logger->error('License verification failed', array(
                'status_code' => $status_code,
            ));
            return new WP_Error(
                'verification_failed',
                __('Invalid purchase code or API token.', 'vin-decoder-addon')
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_error',
                __('Failed to parse verification response.', 'vin-decoder-addon')
            );
        }
        
        // Store license information
        $this->store_license_data($purchase_code, $data);
        
        $this->logger->info('License verified successfully');
        
        return true;
    }
    
    /**
     * Store license data
     *
     * @param string $purchase_code Purchase code
     * @param array $data License data
     */
    private function store_license_data($purchase_code, $data) {
        $license_data = array(
            'purchase_code' => $purchase_code,
            'verified_at' => current_time('timestamp'),
            'buyer' => isset($data['buyer']) ? $data['buyer'] : '',
            'license' => isset($data['license']) ? $data['license'] : '',
            'item_name' => isset($data['item']['name']) ? $data['item']['name'] : '',
        );
        
        update_option('vin_decoder_license_data', $license_data);
    }
    
    /**
     * Check if license is valid
     *
     * @return bool True if valid
     */
    public function is_license_valid() {
        $license_data = get_option('vin_decoder_license_data');
        
        if (!$license_data || !isset($license_data['verified_at'])) {
            return false;
        }
        
        // Check if license was verified within last 30 days
        $verified_at = $license_data['verified_at'];
        $thirty_days_ago = current_time('timestamp') - (30 * DAY_IN_SECONDS);
        
        return $verified_at > $thirty_days_ago;
    }
    
    /**
     * Get license data
     *
     * @return array|false License data or false
     */
    public function get_license_data() {
        return get_option('vin_decoder_license_data', false);
    }
    
    /**
     * Deactivate license
     *
     * @return bool Success status
     */
    public function deactivate_license() {
        $this->logger->info('License deactivated');
        return delete_option('vin_decoder_license_data');
    }
}
