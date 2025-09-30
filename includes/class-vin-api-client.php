<?php
/**
 * VIN API Client Class
 *
 * Handles communication with VIN decoder APIs
 *
 * @package VIN_Decoder_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VIN_API_Client class
 */
class VIN_API_Client {
    /**
     * Singleton instance
     *
     * @var VIN_API_Client
     */
    private static $instance = null;
    
    /**
     * Logger instance
     *
     * @var VIN_Logger
     */
    private $logger;
    
    /**
     * API provider
     *
     * @var string
     */
    private $provider;
    
    /**
     * API key
     *
     * @var string
     */
    private $api_key;
    
    /**
     * Get singleton instance
     *
     * @return VIN_API_Client
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
        $this->provider = vin_decoder_get_settings('api_provider') ?: 'nhtsa';
        $this->api_key = vin_decoder_get_settings('api_key') ?: '';
    }
    
    /**
     * Decode VIN using configured API provider
     *
     * @param string $vin VIN to decode
     * @return array|WP_Error Decoded VIN data or error
     */
    public function decode_vin($vin) {
        // Validate VIN
        if (!vin_decoder_validate_vin($vin)) {
            $this->logger->error('Invalid VIN format', array('vin' => $vin));
            return new WP_Error(
                'invalid_vin',
                __('Invalid VIN format. VIN must be 17 alphanumeric characters.', 'vin-decoder-addon')
            );
        }
        
        // Sanitize VIN
        $vin = vin_decoder_sanitize_vin($vin);
        
        $this->logger->info('Decoding VIN', array(
            'vin' => $vin,
            'provider' => $this->provider
        ));
        
        // Call appropriate API based on provider
        switch ($this->provider) {
            case 'nhtsa':
                $result = $this->decode_nhtsa($vin);
                break;
            
            case 'rapidapi':
                $result = $this->decode_rapidapi($vin);
                break;
            
            case 'carmd':
                $result = $this->decode_carmd($vin);
                break;
            
            default:
                $this->logger->error('Unknown API provider', array('provider' => $this->provider));
                return new WP_Error(
                    'unknown_provider',
                    __('Unknown API provider configured.', 'vin-decoder-addon')
                );
        }
        
        // Log result
        if (is_wp_error($result)) {
            $this->logger->error('VIN decode failed', array(
                'vin' => $vin,
                'error' => $result->get_error_message()
            ));
        } else {
            $this->logger->info('VIN decoded successfully', array(
                'vin' => $vin,
                'data_fields' => array_keys($result)
            ));
        }
        
        return $result;
    }
    
    /**
     * Decode VIN using NHTSA API
     *
     * @param string $vin VIN to decode
     * @return array|WP_Error Decoded data or error
     */
    private function decode_nhtsa($vin) {
        $url = sprintf(
            'https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/%s?format=json',
            urlencode($vin)
        );
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf(
                    __('API returned status code %d', 'vin-decoder-addon'),
                    $status_code
                )
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_error',
                __('Failed to parse API response.', 'vin-decoder-addon')
            );
        }
        
        // NHTSA returns results in an array
        if (!isset($data['Results'][0])) {
            return new WP_Error(
                'no_data',
                __('No data returned from API.', 'vin-decoder-addon')
            );
        }
        
        $result = $data['Results'][0];
        
        // Check for errors in response
        if (isset($result['ErrorCode']) && $result['ErrorCode'] !== '0') {
            return new WP_Error(
                'api_error',
                isset($result['ErrorText']) ? $result['ErrorText'] : __('API error occurred.', 'vin-decoder-addon')
            );
        }
        
        // Format and return data
        return $this->format_nhtsa_data($result);
    }
    
    /**
     * Decode VIN using RapidAPI
     *
     * @param string $vin VIN to decode
     * @return array|WP_Error Decoded data or error
     */
    private function decode_rapidapi($vin) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'missing_api_key',
                __('RapidAPI key is required but not configured.', 'vin-decoder-addon')
            );
        }
        
        $url = sprintf(
            'https://vin-decoder-by-api-ninjas.p.rapidapi.com/v1/vindecoder?vin=%s',
            urlencode($vin)
        );
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'X-RapidAPI-Key' => $this->api_key,
                'X-RapidAPI-Host' => 'vin-decoder-by-api-ninjas.p.rapidapi.com',
                'Accept' => 'application/json',
            ),
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf(
                    __('API returned status code %d', 'vin-decoder-addon'),
                    $status_code
                )
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_error',
                __('Failed to parse API response.', 'vin-decoder-addon')
            );
        }
        
        // Format and return data
        return $this->format_rapidapi_data($data);
    }
    
    /**
     * Decode VIN using CarMD API
     *
     * @param string $vin VIN to decode
     * @return array|WP_Error Decoded data or error
     */
    private function decode_carmd($vin) {
        if (empty($this->api_key)) {
            return new WP_Error(
                'missing_api_key',
                __('CarMD API key is required but not configured.', 'vin-decoder-addon')
            );
        }
        
        $url = sprintf(
            'https://api.carmd.com/v3.0/decode?vin=%s',
            urlencode($vin)
        );
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json',
            ),
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return new WP_Error(
                'api_error',
                sprintf(
                    __('API returned status code %d', 'vin-decoder-addon'),
                    $status_code
                )
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_error',
                __('Failed to parse API response.', 'vin-decoder-addon')
            );
        }
        
        // Format and return data
        return $this->format_carmd_data($data);
    }
    
    /**
     * Format NHTSA API response
     *
     * @param array $data Raw API data
     * @return array Formatted data
     */
    private function format_nhtsa_data($data) {
        $formatted = array();
        
        // Map NHTSA fields to our standard format
        $field_map = array(
            'Make' => 'make',
            'Model' => 'model',
            'ModelYear' => 'year',
            'Trim' => 'trim',
            'EngineModel' => 'engine',
            'VehicleType' => 'vehicle_type',
            'BodyClass' => 'body_class',
            'Manufacturer' => 'manufacturer',
            'PlantCity' => 'plant_city',
            'PlantCountry' => 'plant_country',
            'Series' => 'series',
            'DriveType' => 'drive_type',
            'FuelTypePrimary' => 'fuel_type',
            'TransmissionStyle' => 'transmission',
            'Doors' => 'doors',
            'DisplacementL' => 'displacement',
            'EngineCylinders' => 'cylinders',
        );
        
        foreach ($field_map as $api_key => $standard_key) {
            if (isset($data[$api_key]) && !empty($data[$api_key]) && $data[$api_key] !== 'Not Applicable') {
                $formatted[$standard_key] = sanitize_text_field($data[$api_key]);
            }
        }
        
        return apply_filters('vin_decoder_formatted_data', $formatted, $data, 'nhtsa');
    }
    
    /**
     * Format RapidAPI response
     *
     * @param array $data Raw API data
     * @return array Formatted data
     */
    private function format_rapidapi_data($data) {
        $formatted = array();
        
        // Map RapidAPI fields to our standard format
        $field_map = array(
            'make' => 'make',
            'model' => 'model',
            'year' => 'year',
            'trim' => 'trim',
            'engine' => 'engine',
            'style' => 'body_class',
            'made_in' => 'plant_country',
            'transmission' => 'transmission',
            'fuel_type' => 'fuel_type',
            'drive_type' => 'drive_type',
        );
        
        foreach ($field_map as $api_key => $standard_key) {
            if (isset($data[$api_key]) && !empty($data[$api_key])) {
                $formatted[$standard_key] = sanitize_text_field($data[$api_key]);
            }
        }
        
        return apply_filters('vin_decoder_formatted_data', $formatted, $data, 'rapidapi');
    }
    
    /**
     * Format CarMD API response
     *
     * @param array $data Raw API data
     * @return array Formatted data
     */
    private function format_carmd_data($data) {
        $formatted = array();
        
        // CarMD has a nested structure
        if (isset($data['data'])) {
            $vehicle_data = $data['data'];
            
            $field_map = array(
                'make' => 'make',
                'model' => 'model',
                'year' => 'year',
                'trim' => 'trim',
                'engine' => 'engine',
                'manufacturer' => 'manufacturer',
                'transmission' => 'transmission',
            );
            
            foreach ($field_map as $api_key => $standard_key) {
                if (isset($vehicle_data[$api_key]) && !empty($vehicle_data[$api_key])) {
                    $formatted[$standard_key] = sanitize_text_field($vehicle_data[$api_key]);
                }
            }
        }
        
        return apply_filters('vin_decoder_formatted_data', $formatted, $data, 'carmd');
    }
    
    /**
     * Test API connection
     *
     * @return bool|WP_Error True if connection successful, error otherwise
     */
    public function test_connection() {
        // Use a known valid VIN for testing
        $test_vin = '1HGBH41JXMN109186'; // Honda Accord test VIN
        
        $result = $this->decode_vin($test_vin);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        if (empty($result)) {
            return new WP_Error(
                'empty_response',
                __('API connection successful but no data returned.', 'vin-decoder-addon')
            );
        }
        
        return true;
    }
    
    /**
     * Get API usage statistics (if supported by provider)
     *
     * @return array|WP_Error Usage stats or error
     */
    public function get_usage_stats() {
        // This would be implemented based on provider's API
        // For now, return a placeholder
        return array(
            'provider' => $this->provider,
            'message' => __('Usage statistics not available for this provider.', 'vin-decoder-addon'),
        );
    }
}
