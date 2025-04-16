<?php
/**
 * Theme Customizer Class
 * 
 * Handles applying theme customizations to the plugin
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Theme_Customizer Class
 */
class OMP_Theme_Customizer
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Add dynamic CSS to admin head
        add_action('admin_head', array($this, 'add_admin_custom_css'));

        // Add dynamic CSS to frontend head if enabled
        add_action('wp_head', array($this, 'add_frontend_custom_css'));

        // Add AJAX handler for clearing cache
        add_action('wp_ajax_omp_clear_cache', array($this, 'ajax_clear_cache'));

        // Add AJAX handler for resetting settings
        add_action('wp_ajax_omp_reset_settings', array($this, 'ajax_reset_settings'));

        // Localize script with data
        add_action('admin_enqueue_scripts', array($this, 'localize_admin_script'));

        // Filter the body classes to add RTL class if needed
        add_filter('admin_body_class', array($this, 'add_rtl_admin_body_class'));
        add_filter('body_class', array($this, 'add_rtl_body_class'));
    }

    /**
     * Add RTL class to admin body if needed
     * 
     * @param string $classes Space-separated list of classes
     * @return string Modified list of classes
     */
    public function add_rtl_admin_body_class($classes)
    {
        if (is_rtl()) {
            $classes .= ' omp-rtl';

            // Add locale-specific class
            $locale = get_locale();
            if (!empty($locale)) {
                $classes .= ' omp-locale-' . sanitize_html_class(strtolower($locale));
            }
        }

        return $classes;
    }

    /**
     * Add RTL class to frontend body if needed
     * 
     * @param array $classes Array of body classes
     * @return array Modified array of body classes
     */
    public function add_rtl_body_class($classes)
    {
        if (is_rtl()) {
            $classes[] = 'omp-rtl';

            // Add locale-specific class
            $locale = get_locale();
            if (!empty($locale)) {
                $classes[] = 'omp-locale-' . sanitize_html_class(strtolower($locale));
            }
        }

        return $classes;
    }

    /**
     * Localize admin script with data
     * 
     * @param string $hook Current admin page
     */
    public function localize_admin_script($hook)
    {
        // Only on our plugin pages
        if (!$hook || (is_string($hook) && strpos($hook, 'order_manager_plus') === false)) {
            return;
        }

        wp_localize_script('omp-admin-script', 'ompAdminData', array(
            'nonce' => wp_create_nonce('omp-admin-nonce'),
            'resetConfirmText' => __('Are you sure you want to reset theme settings to defaults?', 'order-manager-plus'),
            'resetAllConfirmText' => __('Are you sure you want to reset ALL plugin settings? This cannot be undone.', 'order-manager-plus'),
            'clearingCacheText' => __('Clearing...', 'order-manager-plus'),
            'resettingText' => __('Resetting...', 'order-manager-plus'),
            'errorText' => __('An error occurred.', 'order-manager-plus'),
            'isRTL' => is_rtl()
        ));
    }

    /**
     * Add custom CSS to admin head
     */
    public function add_admin_custom_css()
    {
        // Only on admin pages
        if (!is_admin()) {
            return;
        }

        // Get theme settings
        $theme_settings = get_option('omp_theme_settings', array());

        // If no settings, return
        if (empty($theme_settings)) {
            return;
        }

        // Generate CSS
        $css = $this->generate_theme_css($theme_settings);

        // Output CSS
        if (!empty($css)) {
            echo '<style type="text/css">' . $css . '</style>';
        }
    }

    /**
     * Add custom CSS to frontend head
     */
    public function add_frontend_custom_css()
    {
        // Only on frontend
        if (is_admin()) {
            return;
        }

        // Get theme settings
        $theme_settings = get_option('omp_theme_settings', array());

        // If no settings or frontend not enabled, return
        if (empty($theme_settings) || empty($theme_settings['apply_to_frontend'])) {
            return;
        }

        // Generate CSS
        $css = $this->generate_theme_css($theme_settings);

        // Output CSS
        if (!empty($css)) {
            echo '<style type="text/css">' . $css . '</style>';
        }
    }

    /**
     * Generate theme CSS based on settings
     * 
     * @param array $settings Theme settings
     * @return string CSS rules
     */
    private function generate_theme_css($settings)
    {
        // Initialize CSS
        $css = '';

        // Check if RTL
        $is_rtl = is_rtl();

        // Start with CSS variables for easy theme application
        $css .= '
            :root {
                --omp-primary-color: ' . sanitize_hex_color($settings['primary_color'] ?? '#2c3e50') . ';
                --omp-secondary-color: ' . sanitize_hex_color($settings['secondary_color'] ?? '#3498db') . ';
                --omp-success-color: ' . sanitize_hex_color($settings['success_color'] ?? '#27ae60') . ';
                --omp-danger-color: ' . sanitize_hex_color($settings['danger_color'] ?? '#e74c3c') . ';
                --omp-border-radius: ' . absint($settings['border_radius'] ?? 4) . 'px;
        ';

        // Add font family if specified
        if (!empty($settings['font_family'])) {
            $css .= '--omp-font-family: ' . sanitize_text_field($settings['font_family']) . ';';
        } elseif ($is_rtl) {
            // Set default RTL fonts based on locale
            $locale = get_locale();
            if (strpos($locale, 'he_IL') !== false) {
                $css .= '--omp-font-family: Arial, sans-serif;';
            } elseif (strpos($locale, 'ar') === 0) {
                $css .= '--omp-font-family: Tahoma, Arial, sans-serif;';
            }
        }

        $css .= '}
        ';

        // Primary color
        if (!empty($settings['primary_color'])) {
            $css .= '
                /* Primary Color */
                .omp-button,
                #omp-order-table th,
                .omp-pagination .page-numbers.current {
                    background-color: var(--omp-primary-color) !important;
                }
                .omp-pagination .page-numbers.current {
                    border-color: var(--omp-primary-color) !important;
                }
            ';
        }

        // Secondary color
        if (!empty($settings['secondary_color'])) {
            $css .= '
                /* Secondary Color */
                .omp-invoice-button,
                .omp-status-processing {
                    background-color: var(--omp-secondary-color) !important;
                }
            ';
        }

        // Success color
        if (!empty($settings['success_color'])) {
            $css .= '
                /* Success Color */
                .omp-export-button,
                .omp-status-completed {
                    background-color: var(--omp-success-color) !important;
                }
            ';
        }

        // Danger color
        if (!empty($settings['danger_color'])) {
            $css .= '
                /* Danger Color */
                .omp-edit-button,
                .omp-status-cancelled,
                .omp-status-failed {
                    background-color: var(--omp-danger-color) !important;
                }
            ';
        }

        // Font family
        if (!empty($settings['font_family'])) {
            $css .= '
                /* Font Family */
                .omp-order-table-container,
                .omp-invoice {
                    font-family: var(--omp-font-family) !important;
                }
            ';
        }

        // Border radius
        if (isset($settings['border_radius'])) {
            $css .= '
                /* Border Radius */
                .omp-button, 
                .omp-order-status,
                #omp-order-table,
                .omp-pagination .page-numbers,
                .omp-info-box,
                .omp-invoice,
                .omp-notice,
                .omp-error {
                    border-radius: var(--omp-border-radius) !important;
                }
            ';
        }

        // RTL-specific styles
        if ($is_rtl) {
            $css .= '
                /* RTL-specific styles */
                .omp-order-table-container,
                .omp-invoice {
                    direction: rtl;
                    text-align: right;
                }
                
                .omp-actions-column {
                    text-align: left;
                }
                
                .omp-checkbox-column {
                    text-align: right;
                }
                
                .omp-export-filters label {
                    margin-left: 20px;
                    margin-right: 0;
                }
                
                .omp-action-buttons {
                    flex-direction: row-reverse;
                }
                
                .omp-order-summary {
                    justify-content: flex-start;
                }
            ';

            // Add language-specific font stacks
            $locale = get_locale();
            if (strpos($locale, 'he_IL') !== false) {
                $css .= '
                    /* Hebrew font styles */
                    .omp-order-table-container,
                    .omp-invoice,
                    .omp-button,
                    .omp-modal input,
                    .omp-modal textarea,
                    .omp-modal select {
                        font-family: Arial, sans-serif !important;
                    }
                ';
            } elseif (strpos($locale, 'ar') === 0) {
                $css .= '
                    /* Arabic font styles */
                    .omp-order-table-container,
                    .omp-invoice,
                    .omp-button,
                    .omp-modal input,
                    .omp-modal textarea,
                    .omp-modal select {
                        font-family: Tahoma, Arial, sans-serif !important;
                    }
                ';
            }
        }

        // Get custom CSS from advanced settings
        $options = get_option('omp_settings', array());
        if (isset($options['custom_css'])) {
            $css .= '
                /* Custom CSS */
                ' . $options['custom_css'] . '
            ';
        }

        return $css;
    }

    /**
     * AJAX handler for clearing cache
     */
    public function ajax_clear_cache()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'omp-admin-nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'order-manager-plus')
            ));
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'order-manager-plus')
            ));
        }

        // Clear all transients with our prefix
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_omp_%' OR option_name LIKE '%_transient_timeout_omp_%'");

        // Send success response
        wp_send_json_success(array(
            'message' => __('Cache cleared successfully!', 'order-manager-plus')
        ));
    }

    /**
     * AJAX handler for resetting settings
     */
    public function ajax_reset_settings()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'omp-admin-nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'order-manager-plus')
            ));
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'order-manager-plus')
            ));
        }

        // Delete all plugin options
        delete_option('omp_settings');
        delete_option('omp_theme_settings');

        // Set default options
        $default_options = array(
            'table_per_page' => 10,
            'default_statuses' => array('wc-completed', 'wc-processing', 'wc-on-hold'),
            'date_format' => 'ago',
            'order_sort' => 'desc',
            'enable_invoice' => true,
            'enable_edit' => true,
            'enable_export' => true,
        );

        update_option('omp_settings', $default_options);

        // Send success response
        wp_send_json_success(array(
            'message' => __('All settings have been reset to defaults!', 'order-manager-plus')
        ));
    }
}