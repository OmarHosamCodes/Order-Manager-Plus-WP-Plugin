<?php
/**
 * Plugin Name: Order Manager Plus
 * Description: Enhanced WooCommerce order management with customizable tables, invoice generation, and order editing features.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: order-manager-plus
 * Domain Path: /languages
 * Requires at least: 5.6
 * Requires PHP: 7.3
 * WC requires at least: 5.0
 * 
 * @package OrderManagerPlus
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('OMP_VERSION', '1.0.0');
define('OMP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OMP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OMP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Check if WooCommerce is active
 */
function omp_is_woocommerce_active()
{
    $active_plugins = (array) get_option('active_plugins', array());

    if (is_multisite()) {
        $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
    }

    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}

/**
 * Display notice if WooCommerce is not active
 */
function omp_woocommerce_missing_notice()
{
    ?>
    <div class="notice notice-error">
        <p><?php _e('Order Manager Plus requires WooCommerce to be installed and active. You can download <a href="https://woocommerce.com/" target="_blank">WooCommerce</a> here.', 'order-manager-plus'); ?>
        </p>
    </div>
    <?php
}

// Initialize the plugin
function omp_init()
{
    // Check for WooCommerce
    if (!omp_is_woocommerce_active()) {
        add_action('admin_notices', 'omp_woocommerce_missing_notice');
        return;
    }

    // Load text domain
    load_plugin_textdomain('order-manager-plus', false, dirname(OMP_PLUGIN_BASENAME) . '/languages');

    // Include core files - always needed
    require_once OMP_PLUGIN_DIR . 'includes/core/class-date-formatter.php';
    require_once OMP_PLUGIN_DIR . 'includes/core/class-order-table.php';
    require_once OMP_PLUGIN_DIR . 'includes/core/class-order-exporter.php';
    require_once OMP_PLUGIN_DIR . 'includes/core/class-theme-customizer.php';

    // Include admin files
    require_once OMP_PLUGIN_DIR . 'includes/admin/class-admin-menu.php';
    require_once OMP_PLUGIN_DIR . 'includes/admin/class-invoice-generator.php';

    // Initialize date formatter (used throughout)
    $date_formatter = new OMP_Date_Formatter();

    // Register shortcode
    add_shortcode('order_manager_table', array('OMP_Order_Table', 'shortcode'));

    // Initialize admin menu
    $admin_menu = new OMP_Admin_Menu();

    // Initialize invoice generator (needed for front-end as well)
    $invoice_generator = new OMP_Invoice_Generator();

    // Initialize order exporter (needed for AJAX)
    $order_exporter = new OMP_Order_Exporter();

    // Initialize order table (needed for AJAX)
    $theme_customizer = new OMP_Theme_Customizer();

    // Register block editor integration if available
    if (function_exists('register_block_type')) {
        require_once OMP_PLUGIN_DIR . 'includes/integrations/class-block-editor.php';
        $block_editor = new OMP_Block_Editor();
    }

    // Register Elementor widget if Elementor is active (optional integration)
    if (did_action('elementor/loaded')) {
        require_once OMP_PLUGIN_DIR . 'includes/integrations/class-elementor-widget.php';
        add_action('elementor/widgets/register', array('OMP_Elementor_Widget', 'register_widget'));
    }
}

// Initialize the plugin after all plugins have loaded
add_action('plugins_loaded', 'omp_init');

// Register activation hook
register_activation_hook(__FILE__, 'omp_activate');

/**
 * Plugin activation function
 */
function omp_activate()
{
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

    // Only set options if they don't already exist
    if (!get_option('omp_settings')) {
        update_option('omp_settings', $default_options);
    }

    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'omp_deactivate');

/**
 * Plugin deactivation function
 */
function omp_deactivate()
{
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register uninstall hook
register_uninstall_hook(__FILE__, 'omp_uninstall');

/**
 * Plugin uninstall function
 */
function omp_uninstall()
{
    // Delete plugin options
    delete_option('omp_settings');
}

// Register script and style enqueues
function omp_enqueue_scripts()
{
    // Only enqueue on pages with our shortcode or block
    global $post;
    if (
        is_a($post, 'WP_Post') &&
        (has_shortcode($post->post_content, 'order_manager_table') ||
            has_block('order-manager-plus/order-table'))
    ) {

        wp_enqueue_style(
            'omp-public-styles',
            OMP_PLUGIN_URL . 'assets/css/public.css',
            array(),
            OMP_VERSION
        );

        wp_enqueue_script(
            'omp-table-script',
            OMP_PLUGIN_URL . 'assets/js/order-table.js',
            array('jquery'),
            OMP_VERSION,
            true
        );

        wp_enqueue_script(
            'omp-export-script',
            OMP_PLUGIN_URL . 'assets/js/order-export.js',
            array('jquery'),
            OMP_VERSION,
            true
        );

        wp_enqueue_script(
            'omp-admin-script',
            OMP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            OMP_VERSION,
            true
        );

        // Localize script with plugin data
        wp_localize_script('omp-table-script', 'ompData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('omp-nonce'),
            'i18n' => array(
                'select_orders' => __('Please select at least one order or set filter criteria.', 'order-manager-plus'),
                'exporting' => __('Exporting...', 'order-manager-plus'),
                'export_error' => __('Error exporting orders.', 'order-manager-plus'),
                'export_selected' => __('Export Selected', 'order-manager-plus')
            )
        ));
    }
}
add_action('wp_enqueue_scripts', 'omp_enqueue_scripts');

// Register admin styles
function omp_admin_enqueue_styles($hook)
{
    // Only enqueue on our plugin pages
    if (strpos($hook, 'order_manager_plus') === false) {
        return;
    }

    wp_enqueue_style(
        'omp-admin-styles',
        OMP_PLUGIN_URL . 'assets/css/admin.css',
        array(),
        OMP_VERSION
    );
}
add_action('admin_enqueue_scripts', 'omp_admin_enqueue_styles');

// Implement sanitize_hex_color if not available
if (!function_exists('sanitize_hex_color')) {
    /**
     * Sanitizes a hex color.
     *
     * @param string $color Hex color code.
     * @return string|null Sanitized hex color, or null if not a hex color.
     */
    function sanitize_hex_color($color)
    {
        if ('' === $color) {
            return '';
        }

        // 3 or 6 hex digits, or the empty string.
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }

        return null;
    }
}