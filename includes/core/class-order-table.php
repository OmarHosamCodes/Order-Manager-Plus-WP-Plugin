<?php
/**
 * Order Table Class
 * 
 * Handles retrieving and displaying orders in a customizable table
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Order_Table Class
 */
class OMP_Order_Table
{

    /**
     * Settings for the table
     * 
     * @var array
     */
    private $settings = array();

    /**
     * Constructor
     * 
     * @param array $settings Settings for the table
     */
    public function __construct($settings = array())
    {
        $defaults = array(
            'select_status' => array('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-failed', 'wc-cancelled'),
            'list_per_page' => 10,
            'order_time_format' => 'ago',
            'order_by' => 'desc',
        );

        $this->settings = wp_parse_args($settings, $defaults);
    }

    /**
     * Get orders based on settings
     * 
     * @return WP_Query The query with orders
     */
    public function get_orders()
    {
        $current_page = max(1, get_query_var('paged'));

        $args = array(
            'post_type' => 'shop_order',
            'post_status' => $this->settings['select_status'],
            'posts_per_page' => min($this->settings['list_per_page'], 100), // Limit to 100 max
            'paged' => $current_page,
            'order' => strtoupper($this->settings['order_by']),
        );

        // Apply filters to allow customization
        $args = apply_filters('omp_order_query_args', $args, $this->settings);

        return new WP_Query($args);
    }

    /**
     * Render the order table
     * 
     * @return string HTML output
     */
    public function render()
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return '<div class="omp-error">' .
                __('WooCommerce plugin is not active.', 'order-manager-plus') .
                '</div>';
        }

        // Get orders
        $orders = $this->get_orders();

        // Check if we have orders
        if (!$orders instanceof WP_Query || !$orders->have_posts()) {
            return '<div class="omp-notice">' .
                __('No orders found.', 'order-manager-plus') .
                '</div>';
        }

        // Start output buffering
        ob_start();

        // Include the template
        include OMP_PLUGIN_DIR . 'includes/templates/order-table-template.php';

        // Get the buffer and end buffering
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Shortcode handler
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public static function shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'select_status' => array('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-failed', 'wc-cancelled'),
            'list_per_page' => 10,
            'order_time_format' => 'ago',
            'order_by' => 'desc',
        ), $atts);

        // Convert comma-separated list to array if string
        if (is_string($atts['select_status'])) {
            $atts['select_status'] = array_map('trim', explode(',', $atts['select_status']));
        }

        // Create an instance of the table
        $table = new self($atts);

        // Render the table
        return $table->render();
    }
}