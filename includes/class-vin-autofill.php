<?php
/**
 * VIN Autofill Class
 *
 * Handles automatic population of Gravity Forms fields with VIN data
 *
 * @package VIN_Decoder_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VIN_Autofill class
 */
class VIN_Autofill {
    /**
     * Singleton instance
     *
     * @var VIN_Autofill
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
     * @return VIN_Autofill
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
        
        // Hook into Gravity Forms submission
        add_filter('gform_pre_submission', array($this, 'populate_fields_on_submission'), 10, 1);
    }
    
    /**
     * Populate fields on form submission
     *
     * @param array $form Gravity Forms form object
     * @return array Modified form object
     */
    public function populate_fields_on_submission($form) {
        // Get form ID
        $form_id = isset($form['id']) ? absint($form['id']) : 0;
        
        if (empty($form_id)) {
            return $form;
        }
        
        // Find VIN field in submission
        $vin = $this->find_vin_in_submission($form);
        
        if (empty($vin)) {
            return $form;
        }
        
        $this->logger->info('Processing VIN on form submission', array(
            'form_id' => $form_id,
            'vin' => $vin,
        ));
        
        // Decode VIN
        $decoded_data = $this->api_client->decode_vin($vin);
        
        if (is_wp_error($decoded_data)) {
            $this->logger->error('Failed to decode VIN on submission', array(
                'form_id' => $form_id,
                'vin' => $vin,
                'error' => $decoded_data->get_error_message(),
            ));
            return $form;
        }
        
        // Populate fields
        $this->populate_fields($decoded_data, $form_id);
        
        return $form;
    }
    
    /**
     * Find VIN in form submission
     *
     * @param array $form Gravity Forms form object
     * @return string|null VIN value or null
     */
    private function find_vin_in_submission($form) {
        if (!isset($form['fields']) || !is_array($form['fields'])) {
            return null;
        }
        
        foreach ($form['fields'] as $field) {
            // Check if field is marked as VIN field
            if (isset($field->cssClass) && strpos($field->cssClass, 'vin-field') !== false) {
                $field_id = $field->id;
                $input_name = 'input_' . $field_id;
                
                if (isset($_POST[$input_name])) {
                    return sanitize_text_field($_POST[$input_name]);
                }
            }
            
            // Check field label for "VIN"
            if (isset($field->label) && stripos($field->label, 'vin') !== false) {
                $field_id = $field->id;
                $input_name = 'input_' . $field_id;
                
                if (isset($_POST[$input_name])) {
                    $value = sanitize_text_field($_POST[$input_name]);
                    
                    // Validate it's actually a VIN
                    if (vin_decoder_validate_vin($value)) {
                        return $value;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Populate form fields with decoded VIN data
     *
     * @param array $decoded_data Decoded VIN data
     * @param int $form_id Form ID
     * @return bool Success status
     */
    public function populate_fields($decoded_data, $form_id) {
        if (empty($decoded_data) || empty($form_id)) {
            return false;
        }
        
        // Get field mapping for this form
        $field_mapping = $this->get_form_field_mapping($form_id);
        
        if (empty($field_mapping)) {
            $this->logger->warning('No field mapping found for form', array('form_id' => $form_id));
            return false;
        }
        
        $populated_count = 0;
        
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
                
                // Set the field value in $_POST for Gravity Forms to process
                $input_name = 'input_' . $gf_field_id;
                $_POST[$input_name] = sanitize_text_field($value);
                
                $populated_count++;
            }
        }
        
        $this->logger->info('Fields populated on submission', array(
            'form_id' => $form_id,
            'populated_count' => $populated_count,
        ));
        
        return $populated_count > 0;
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
     * Get available fields for mapping
     *
     * @param int $form_id Form ID
     * @return array Available form fields
     */
    public function get_available_form_fields($form_id) {
        if (!class_exists('GFAPI')) {
            return array();
        }
        
        $form = GFAPI::get_form($form_id);
        
        if (!$form || !isset($form['fields'])) {
            return array();
        }
        
        $available_fields = array();
        
        foreach ($form['fields'] as $field) {
            // Only include text-based fields
            $allowed_types = array('text', 'select', 'number', 'hidden');
            
            if (in_array($field->type, $allowed_types)) {
                $available_fields[] = array(
                    'id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type,
                );
            }
        }
        
        return $available_fields;
    }
    
    /**
     * Validate field mapping configuration
     *
     * @param array $mapping Field mapping to validate
     * @return bool|WP_Error True if valid, error otherwise
     */
    public function validate_field_mapping($mapping) {
        if (!is_array($mapping)) {
            return new WP_Error(
                'invalid_mapping',
                __('Field mapping must be an array.', 'vin-decoder-addon')
            );
        }
        
        // Check required fields
        $required_fields = array('form_id', 'vin_field', 'gf_field_id');
        
        foreach ($required_fields as $field) {
            if (!isset($mapping[$field]) || empty($mapping[$field])) {
                return new WP_Error(
                    'missing_field',
                    sprintf(
                        __('Required field "%s" is missing.', 'vin-decoder-addon'),
                        $field
                    )
                );
            }
        }
        
        // Validate form exists
        if (class_exists('GFAPI')) {
            $form = GFAPI::get_form($mapping['form_id']);
            
            if (!$form) {
                return new WP_Error(
                    'invalid_form',
                    __('Form does not exist.', 'vin-decoder-addon')
                );
            }
        }
        
        // Validate VIN field is a known field
        $available_vin_fields = vin_decoder_get_available_fields();
        
        if (!isset($available_vin_fields[$mapping['vin_field']])) {
            return new WP_Error(
                'invalid_vin_field',
                __('Invalid VIN data field.', 'vin-decoder-addon')
            );
        }
        
        return true;
    }
    
    /**
     * Export field mapping configuration
     *
     * @return string JSON encoded mapping
     */
    public function export_field_mapping() {
        $mapping = vin_decoder_get_field_mapping();
        return wp_json_encode($mapping, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import field mapping configuration
     *
     * @param string $json JSON encoded mapping
     * @return bool|WP_Error True on success, error otherwise
     */
    public function import_field_mapping($json) {
        $mapping = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'invalid_json',
                __('Invalid JSON format.', 'vin-decoder-addon')
            );
        }
        
        if (!is_array($mapping)) {
            return new WP_Error(
                'invalid_format',
                __('Mapping must be an array.', 'vin-decoder-addon')
            );
        }
        
        // Validate each mapping
        foreach ($mapping as $map) {
            $validation = $this->validate_field_mapping($map);
            
            if (is_wp_error($validation)) {
                return $validation;
            }
        }
        
        // Save mapping
        return vin_decoder_update_settings(array('field_mapping' => $mapping));
    }
}
