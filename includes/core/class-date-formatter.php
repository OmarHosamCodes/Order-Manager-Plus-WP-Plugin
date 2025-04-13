<?php
/**
 * Date Formatter Class
 * 
 * Handles date formatting for orders in different formats
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Date_Formatter Class
 */
class OMP_Date_Formatter
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set timezone based on WordPress settings
        $this->set_timezone();
    }

    /**
     * Set the timezone based on WordPress settings
     */
    private function set_timezone()
    {
        if (get_option('timezone_string')) {
            $timezone_string = get_option('timezone_string');
            date_default_timezone_set($timezone_string);
        }
    }

    /**
     * Format a date as "time ago"
     * 
     * @param string|object $datetime The datetime to format (string or WC_DateTime)
     * @return string Formatted time ago string
     */
    public function time_ago($datetime)
    {
        // If WC_DateTime object is passed, convert to timestamp
        if (is_object($datetime) && method_exists($datetime, 'getTimestamp')) {
            $time_ago = $datetime->getTimestamp();
        } else {
            // Otherwise, assume it's a string
            $time_ago = strtotime($datetime);
        }

        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;

        $minutes = round($seconds / 60);
        $hours = round($seconds / 3600);
        $days = round($seconds / 86400);
        $weeks = round($seconds / 604800);
        $months = round($seconds / 2629440);
        $years = round($seconds / 31553280);

        if ($seconds <= 60) {
            return __('Just Now', 'order-manager-plus');
        } else if ($minutes <= 60) {
            if ($minutes == 1) {
                return __('1 minute ago', 'order-manager-plus');
            } else {
                return sprintf(__('%d minutes ago', 'order-manager-plus'), $minutes);
            }
        } else if ($hours <= 24) {
            if ($hours == 1) {
                return __('an hour ago', 'order-manager-plus');
            } else {
                return sprintf(__('%d hrs ago', 'order-manager-plus'), $hours);
            }
        } else if ($days <= 7) {
            if ($days == 1) {
                return __('Yesterday', 'order-manager-plus');
            } else {
                return sprintf(__('%d days ago', 'order-manager-plus'), $days);
            }
        } else if ($weeks <= 4.3) {
            if ($weeks == 1) {
                return __('a week ago', 'order-manager-plus');
            } else {
                return sprintf(__('%d weeks ago', 'order-manager-plus'), $weeks);
            }
        } else if ($months <= 12) {
            if ($months == 1) {
                return __('a month ago', 'order-manager-plus');
            } else {
                return sprintf(__('%d months ago', 'order-manager-plus'), $months);
            }
        } else {
            if ($years == 1) {
                return __('one year ago', 'order-manager-plus');
            } else {
                return sprintf(__('%d years ago', 'order-manager-plus'), $years);
            }
        }
    }

    /**
     * Format date according to specified format
     * 
     * @param string|object $datetime The datetime to format (string or WC_DateTime)
     * @param string $format Format to use (ago, mysql, human)
     * @return string Formatted date string
     */
    public function format_date($datetime, $format = 'ago')
    {
        if (empty($datetime)) {
            return '';
        }

        // If WC_DateTime object is passed, convert to timestamp
        if (is_object($datetime) && method_exists($datetime, 'getTimestamp')) {
            $timestamp = $datetime->getTimestamp();
        } else {
            // Otherwise, assume it's a string
            $timestamp = strtotime($datetime);
        }

        switch ($format) {
            case 'ago':
                return $this->time_ago($datetime);

            case 'mysql':
                return date('Y-m-d H:i:s', $timestamp);

            case 'human':
                return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);

            case 'short':
                return date_i18n('d/M/y h:i A', $timestamp);

            default:
                return date_i18n($format, $timestamp);
        }
    }

    /**
     * Get instance (for global access)
     * 
     * @return OMP_Date_Formatter
     */
    public static function get_instance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
}

/**
 * Helper function to format dates
 * 
 * @param string|object $datetime The datetime to format
 * @param string $format The format to use
 * @return string Formatted date
 */
function omp_format_date($datetime, $format = 'ago')
{
    return OMP_Date_Formatter::get_instance()->format_date($datetime, $format);
}