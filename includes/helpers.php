<?php
/**
 * Helper Functions
 *
 * @package VIN_Decoder_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Validate VIN format
 *
 * @param string $vin The VIN to validate
 * @return bool True if valid, false otherwise
 */
function vin_decoder_validate_vin($vin) {
    // Remove whitespace
    $vin = trim($vin);
    
    // VIN must be exactly 17 characters
    if (strlen($vin) !== 17) {
        return false;
    }
    
    // VIN should only contain alphanumeric characters (excluding I, O, Q)
    if (!preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', strtoupper($vin))) {
        return false;
    }
    
    return true;
}

/**
 * Sanitize VIN
 *
 * @param string $vin The VIN to sanitize
 * @return string Sanitized VIN
 */
function vin_decoder_sanitize_vin($vin) {
    // Remove whitespace and convert to uppercase
    $vin = strtoupper(trim($vin));
    
    // Remove any non-alphanumeric characters
    $vin = preg_replace('/[^A-Z0-9]/', '', $vin);
    
    return $vin;
}

/**
 * Get plugin settings
 *
 * @param string $key Optional. Specific setting key to retrieve
 * @return mixed All settings or specific setting value
 */
function vin_decoder_get_settings($key = null) {
    $settings = get_option('vin_decoder_addon_settings', array());
    
    if ($key !== null) {
        return isset($settings[$key]) ? $settings[$key] : null;
    }
    
    return $settings;
}

/**
 * Update plugin settings
 *
 * @param array $settings Settings to update
 * @return bool True on success, false on failure
 */
function vin_decoder_update_settings($settings) {
    $current_settings = vin_decoder_get_settings();
    $updated_settings = array_merge($current_settings, $settings);
    
    return update_option('vin_decoder_addon_settings', $updated_settings);
}

/**
 * Get field mapping
 *
 * @return array Field mapping configuration
 */
function vin_decoder_get_field_mapping() {
    $mapping = vin_decoder_get_settings('field_mapping');
    
    if (!is_array($mapping)) {
        return array();
    }
    
    return $mapping;
}

/**
 * Format VIN data for display
 *
 * @param array $data Raw VIN data from API
 * @return array Formatted VIN data
 */
function vin_decoder_format_vin_data($data) {
    if (!is_array($data)) {
        return array();
    }
    
    $formatted = array();
    
    // Common fields to format
    $fields = array(
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
    
    foreach ($fields as $api_key => $formatted_key) {
        if (isset($data[$api_key]) && !empty($data[$api_key])) {
            $formatted[$formatted_key] = sanitize_text_field($data[$api_key]);
        }
    }
    
    return $formatted;
}

/**
 * Get available API providers
 *
 * @return array List of available API providers
 */
function vin_decoder_get_api_providers() {
    return array(
        'nhtsa' => array(
            'name' => __('NHTSA (Free)', 'vin-decoder-addon'),
            'requires_key' => false,
            'description' => __('National Highway Traffic Safety Administration - Free API', 'vin-decoder-addon'),
        ),
        'rapidapi' => array(
            'name' => __('RapidAPI VIN Decoder', 'vin-decoder-addon'),
            'requires_key' => true,
            'description' => __('Comprehensive VIN data via RapidAPI', 'vin-decoder-addon'),
        ),
        'carmd' => array(
            'name' => __('CarMD', 'vin-decoder-addon'),
            'requires_key' => true,
            'description' => __('Detailed vehicle information and diagnostics', 'vin-decoder-addon'),
        ),
    );
}

/**
 * Check if API provider requires an API key
 *
 * @param string $provider Provider name
 * @return bool True if API key is required
 */
function vin_decoder_provider_requires_key($provider) {
    $providers = vin_decoder_get_api_providers();
    
    if (isset($providers[$provider])) {
        return $providers[$provider]['requires_key'];
    }
    
    return false;
}

/**
 * Get available VIN data fields
 *
 * @return array List of available VIN data fields
 */
function vin_decoder_get_available_fields() {
    return array(
        'make' => __('Make', 'vin-decoder-addon'),
        'model' => __('Model', 'vin-decoder-addon'),
        'year' => __('Year', 'vin-decoder-addon'),
        'trim' => __('Trim', 'vin-decoder-addon'),
        'engine' => __('Engine', 'vin-decoder-addon'),
        'vehicle_type' => __('Vehicle Type', 'vin-decoder-addon'),
        'body_class' => __('Body Class', 'vin-decoder-addon'),
        'manufacturer' => __('Manufacturer', 'vin-decoder-addon'),
        'plant_city' => __('Plant City', 'vin-decoder-addon'),
        'plant_country' => __('Plant Country', 'vin-decoder-addon'),
        'series' => __('Series', 'vin-decoder-addon'),
        'drive_type' => __('Drive Type', 'vin-decoder-addon'),
        'fuel_type' => __('Fuel Type', 'vin-decoder-addon'),
        'transmission' => __('Transmission', 'vin-decoder-addon'),
        'doors' => __('Doors', 'vin-decoder-addon'),
        'displacement' => __('Displacement', 'vin-decoder-addon'),
        'cylinders' => __('Cylinders', 'vin-decoder-addon'),
    );
}
