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
     * Query results storage
     * 
     * @var array
     */
    private $query_results = array();

    /**
     * Constructor
     * 
     * @param array $settings Settings for the table
     */
    public function __construct($settings = array())
    {
        $defaults = array(
            'select_status' => array('completed', 'processing', 'on-hold', 'failed', 'cancelled'),
            'list_per_page' => 10,
            'order_time_format' => 'ago',
            'order_by' => 'desc',
        );

        $this->settings = wp_parse_args($settings, $defaults);

        // Initialize query results with default values
        $this->query_results = array(
            'orders' => array(),
            'found_posts' => 0,
            'max_num_pages' => 0,
            'current_page' => 1,
            'statuses' => array()
        );
    }

    /**
     * Get orders based on settings
     * 
     * @return array Array of WC_Order objects
     */
    public function get_orders()
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce') || !class_exists('WC_Order_Query')) {
            return array();
        }

        $current_page = max(1, get_query_var('paged'));
        if (!$current_page)
            $current_page = 1;

        // Format statuses (WC_Order_Query expects them without 'wc-' prefix)
        $statuses = $this->settings['select_status'];
        if (is_string($statuses)) {
            $statuses = array_map('trim', explode(',', $statuses));
        }

        // Remove 'wc-' prefix if it exists
        $formatted_statuses = array();
        foreach ($statuses as $status) {
            if (is_string($status)) {
                $formatted_statuses[] = str_replace('wc-', '', $status);
            } else {
                $formatted_statuses[] = $status;
            }
        }

        // Query args for WC_Order_Query
        $args = array(
            'limit' => $this->settings['list_per_page'],
            'paged' => $current_page,
            'orderby' => 'date',
            'order' => $this->settings['order_by'],
            'return' => 'objects',
        );

        // Only add status filter if we have valid statuses
        if (!empty($formatted_statuses)) {
            // Check if we need to try with 'any' status
            if (in_array('any', $formatted_statuses)) {
                // Don't set status - will use any status
            } else {
                $args['status'] = $formatted_statuses;
            }
        }

        // Apply filters
        $args = apply_filters('omp_order_query_args', $args, $this->settings);

        // Create WC_Order_Query
        $query = new WC_Order_Query($args);

        // Execute query and get results
        $orders = $query->get_orders();

        // If no results with specified statuses, try with 'any' status
        if (empty($orders) && !empty($args['status'])) {
            $any_args = $args;
            unset($any_args['status']); // Remove status filter to get any status
            $any_query = new WC_Order_Query($any_args);
            $orders = $any_query->get_orders();

            // If we got results, update formatted_statuses for debug info
            if (!empty($orders)) {
                $formatted_statuses = array('any');
            }
        }

        // Get total count for pagination
        $total_orders = 0;

        // If we have orders, we can count them directly for simple cases
        if (!empty($orders)) {
            if (count($orders) < $this->settings['list_per_page']) {
                // If we have fewer orders than the limit, that's our total
                $total_orders = count($orders);
            } else {
                // Otherwise get a count using WC API
                $count_args = array(
                    'return' => 'ids',
                    'limit' => -1,
                );

                // Only add status if we used it in the main query
                if (isset($args['status'])) {
                    $count_args['status'] = $args['status'];
                }

                $count_query = new WC_Order_Query($count_args);
                $count_results = $count_query->get_orders();
                $total_orders = count($count_results);
            }
        }

        // Store results in object property
        $this->query_results = array(
            'orders' => $orders,
            'found_posts' => $total_orders,
            'max_num_pages' => ceil($total_orders / $this->settings['list_per_page']),
            'current_page' => $current_page,
            'statuses' => $formatted_statuses
        );

        return $orders;
    }

    /**
     * Get query results data
     * 
     * @return array Query results data
     */
    public function get_query_results()
    {
        return $this->query_results;
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
        $query_results = $this->get_query_results();

        // Debug: Add status query info
        $debug_info = '';
        if (WP_DEBUG && current_user_can('manage_options')) {
            $debug_info = '<div class="omp-debug" style="margin-bottom: 15px; padding: 10px; background: #f8f8f8; border-left: 4px solid #0073aa; font-size: 12px;">
                <p><strong>Debug Info:</strong><br>
                Query found ' . $query_results['found_posts'] . ' orders<br>
                Queried statuses: ' . implode(', ', $query_results['statuses']) . '<br>
                Order count: ' . count($orders) . '</p>
            </div>';
        }

        // Check if we have orders
        if (empty($orders)) {
            return $debug_info . '<div class="omp-notice">' .
                __('No orders found.', 'order-manager-plus') .
                '</div>';
        }

        // Start output buffering
        ob_start();

        // Create a wrapper for WP_Query compatibility (for the template)
        $orders_query = new stdClass();
        $orders_query->posts = $orders;
        $orders_query->found_posts = $query_results['found_posts'];
        $orders_query->max_num_pages = $query_results['max_num_pages'];
        $orders_query->have_posts = function () use (&$orders_query) {
            return !empty($orders_query->posts);
        };
        $orders_query->the_post = function () use (&$orders_query) {
            $order = array_shift($orders_query->posts);
            array_push($orders_query->posts, $order);
            return $order;
        };

        // Include the template
        $orders = $orders_query; // Rename for template compatibility
        include OMP_PLUGIN_DIR . 'includes/templates/order-table-template.php';

        // Get the buffer and end buffering
        $output = ob_get_clean();

        // Add debug info if needed
        if (!empty($debug_info)) {
            $output = $debug_info . $output;
        }

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
            'select_status' => 'completed,processing,on-hold,failed,cancelled',
            'list_per_page' => 10,
            'order_time_format' => 'ago',
            'order_by' => 'desc',
        ), $atts, 'order_manager_table');

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