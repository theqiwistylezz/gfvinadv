<?php
/**
 * Logger Class
 *
 * Handles logging of plugin events and errors
 *
 * @package VIN_Decoder_Addon
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * VIN_Logger class
 */
class VIN_Logger {
    /**
     * Singleton instance
     *
     * @var VIN_Logger
     */
    private static $instance = null;
    
    /**
     * Log levels
     */
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    
    /**
     * Log file path
     *
     * @var string
     */
    private $log_file;
    
    /**
     * Get singleton instance
     *
     * @return VIN_Logger
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
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/vin-decoder-logs';
        $this->log_file = $log_dir . '/vin-decoder-' . date('Y-m-d') . '.log';
    }
    
    /**
     * Check if logging is enabled
     *
     * @return bool
     */
    private function is_logging_enabled() {
        return (bool) vin_decoder_get_settings('enable_logging');
    }
    
    /**
     * Get configured log level
     *
     * @return string
     */
    private function get_log_level() {
        $level = vin_decoder_get_settings('log_level');
        return $level ? $level : self::LEVEL_ERROR;
    }
    
    /**
     * Check if message should be logged based on level
     *
     * @param string $level Message level
     * @return bool
     */
    private function should_log($level) {
        if (!$this->is_logging_enabled()) {
            return false;
        }
        
        $configured_level = $this->get_log_level();
        
        $levels = array(
            self::LEVEL_INFO => 1,
            self::LEVEL_WARNING => 2,
            self::LEVEL_ERROR => 3,
        );
        
        return $levels[$level] >= $levels[$configured_level];
    }
    
    /**
     * Write log message
     *
     * @param string $message Log message
     * @param string $level Log level
     * @param array $context Additional context
     */
    private function write_log($message, $level, $context = array()) {
        if (!$this->should_log($level)) {
            return;
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $level_upper = strtoupper($level);
        
        $log_entry = sprintf(
            "[%s] [%s] %s\n",
            $timestamp,
            $level_upper,
            $message
        );
        
        // Add context if provided
        if (!empty($context)) {
            $log_entry .= "Context: " . wp_json_encode($context, JSON_PRETTY_PRINT) . "\n";
        }
        
        $log_entry .= str_repeat('-', 80) . "\n";
        
        // Write to file
        error_log($log_entry, 3, $this->log_file);
    }
    
    /**
     * Log info message
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function info($message, $context = array()) {
        $this->write_log($message, self::LEVEL_INFO, $context);
    }
    
    /**
     * Log warning message
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function warning($message, $context = array()) {
        $this->write_log($message, self::LEVEL_WARNING, $context);
    }
    
    /**
     * Log error message
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function error($message, $context = array()) {
        $this->write_log($message, self::LEVEL_ERROR, $context);
    }
    
    /**
     * Get recent log entries
     *
     * @param int $lines Number of lines to retrieve
     * @return array Log entries
     */
    public function get_recent_logs($lines = 50) {
        if (!file_exists($this->log_file)) {
            return array();
        }
        
        $file = new SplFileObject($this->log_file, 'r');
        $file->seek(PHP_INT_MAX);
        $last_line = $file->key();
        
        $start_line = max(0, $last_line - $lines);
        
        $logs = array();
        $file->seek($start_line);
        
        while (!$file->eof()) {
            $line = $file->current();
            if (!empty(trim($line))) {
                $logs[] = $line;
            }
            $file->next();
        }
        
        return $logs;
    }
    
    /**
     * Clear log file
     *
     * @return bool
     */
    public function clear_logs() {
        if (file_exists($this->log_file)) {
            return unlink($this->log_file);
        }
        return true;
    }
}
