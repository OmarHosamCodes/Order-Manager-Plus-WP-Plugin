<?php
/**
 * Order Exporter Class
 * 
 * Handles exporting orders to various formats
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Order_Exporter Class
 */
class OMP_Order_Exporter
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register AJAX handlers
        add_action('wp_ajax_omp_export_orders', array($this, 'ajax_export_orders'));
        add_action('wp_ajax_nopriv_omp_export_orders', array($this, 'ajax_unauthorized'));
    }

    /**
     * Handle unauthorized access
     */
    public function ajax_unauthorized()
    {
        wp_send_json_error(array(
            'message' => __('You do not have permission to export orders.', 'order-manager-plus')
        ));
    }

    /**
     * AJAX handler for exporting orders
     */
    public function ajax_export_orders()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'omp-nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'order-manager-plus')
            ));
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to export orders.', 'order-manager-plus')
            ));
        }

        // Get order IDs
        $order_ids = isset($_POST['order_ids']) ? array_map('intval', (array) $_POST['order_ids']) : array();

        // If no specific orders, get all based on filters
        if (empty($order_ids)) {
            // Get filter parameters
            $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'any';
            $from_date = isset($_POST['from_date']) ? sanitize_text_field($_POST['from_date']) : '';
            $to_date = isset($_POST['to_date']) ? sanitize_text_field($_POST['to_date']) : '';

            // Build query args
            $args = array(
                'post_type' => 'shop_order',
                'post_status' => $status !== 'any' ? $status : array('wc-completed', 'wc-processing', 'wc-on-hold'),
                'posts_per_page' => -1, // Get all matching orders
                'fields' => 'ids', // Just get IDs for efficiency
            );

            // Add date query if dates are provided
            if (!empty($from_date) || !empty($to_date)) {
                $date_query = array();

                if (!empty($from_date)) {
                    $date_query['after'] = $from_date . ' 00:00:00';
                }

                if (!empty($to_date)) {
                    $date_query['before'] = $to_date . ' 23:59:59';
                }

                $args['date_query'] = array($date_query);
            }

            // Get the order IDs
            $query = new WP_Query($args);
            $order_ids = $query->posts;
        }

        // Generate CSV data
        $csv_data = $this->generate_csv($order_ids);

        // Send response
        wp_send_json_success(array(
            'csv' => $csv_data,
            'filename' => 'orders-export-' . date('Y-m-d') . '.csv'
        ));
    }

    /**
     * Generate CSV data from order IDs
     * 
     * @param array $order_ids Array of order IDs
     * @return string CSV data
     */
    private function generate_csv($order_ids)
    {
        // Set up CSV headers
        $headers = array(
            __('Order ID', 'order-manager-plus'),
            __('Date', 'order-manager-plus'),
            __('Status', 'order-manager-plus'),
            __('Customer', 'order-manager-plus'),
            __('Email', 'order-manager-plus'),
            __('Phone', 'order-manager-plus'),
            __('Billing Address', 'order-manager-plus'),
            __('Shipping Address', 'order-manager-plus'),
            __('Products', 'order-manager-plus'),
            __('Shipping', 'order-manager-plus'),
            __('Discount', 'order-manager-plus'),
            __('Total', 'order-manager-plus'),
            __('Payment Method', 'order-manager-plus'),
            __('Notes', 'order-manager-plus')
        );

        // Allow plugins to modify headers
        $headers = apply_filters('omp_export_csv_headers', $headers);

        // Initialize CSV data with headers
        $csv_data = array();
        $csv_data[] = $headers;

        // Process each order
        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);

            if (!$order) {
                continue;
            }

            // Prepare order data
            $row = array(
                $order->get_order_number(),
                $order->get_date_created()->date('Y-m-d H:i:s'),
                wc_get_order_status_name($order->get_status()),
                $order->get_formatted_billing_full_name(),
                $order->get_billing_email(),
                $order->get_billing_phone(),
                $this->format_address($order->get_address('billing')),
                $this->format_address($order->get_address('shipping')),
                $this->format_line_items($order->get_items()),
                wc_format_decimal($order->get_shipping_total(), 2),
                wc_format_decimal($order->get_discount_total(), 2),
                wc_format_decimal($order->get_total(), 2),
                $order->get_payment_method_title(),
                $order->get_customer_note()
            );

            // Allow plugins to modify row data
            $row = apply_filters('omp_export_csv_row', $row, $order);

            $csv_data[] = $row;
        }

        // Convert to CSV string
        return $this->array_to_csv($csv_data);
    }

    /**
     * Format address array to string
     * 
     * @param array $address Address array
     * @return string Formatted address
     */
    private function format_address($address)
    {
        $parts = array(
            $address['first_name'] . ' ' . $address['last_name'],
            $address['company'],
            $address['address_1'],
            $address['address_2'],
            $address['city'] . ($address['state'] ? ", {$address['state']}" : ''),
            $address['postcode'],
            $address['country']
        );

        // Filter out empty parts
        $parts = array_filter($parts);

        return implode(', ', $parts);
    }

    /**
     * Format line items to string
     * 
     * @param array $items Line items
     * @return string Formatted line items
     */
    private function format_line_items($items)
    {
        $output = array();

        foreach ($items as $item) {
            $output[] = $item->get_name() . ' × ' . $item->get_quantity();
        }

        return implode(' | ', $output);
    }

    /**
     * Convert array to CSV string
     * 
     * @param array $data 2D array of data
     * @return string CSV string
     */
    private function array_to_csv($data)
    {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}