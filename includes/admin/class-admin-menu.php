<?php
/**
 * Admin Menu Class
 * 
 * Handles administration menu and pages
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Admin_Menu Class
 */
class OMP_Admin_Menu
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register admin pages
        add_action('admin_menu', array($this, 'register_admin_pages'));
    }

    /**
     * Register the admin pages
     */
    public function register_admin_pages()
    {
        // Add main menu page
        add_menu_page(
            __('Order Manager Plus', 'order-manager-plus'),
            __('Order Manager', 'order-manager-plus'),
            'manage_woocommerce',
            'order_manager_plus',
            array($this, 'render_dashboard_page'),
            'dashicons-list-view',
            58 // Position after WooCommerce
        );

        add_submenu_page(
            'order_manager_plus',
            __('Order Invoices', 'order-manager-plus'),
            __('Invoices', 'order-manager-plus'),
            'manage_woocommerce',
            'omp_order_invoices',
            array($this, 'render_invoices_page')
        );

        // Keep a hidden page for individual invoice viewing/printing

        add_submenu_page(
            null, // No parent menu - hidden page
            __('View Invoice', 'order-manager-plus'),
            __('View Invoice', 'order-manager-plus'),
            'manage_woocommerce',
            'omp_order_invoice',
            array($this, 'render_invoice_page')
        );

        add_submenu_page(
            null, // No parent menu - hidden page
            __('Edit Order', 'order-manager-plus'),
            __('Edit Order', 'order-manager-plus'),
            'manage_woocommerce',
            'omp_order_edit',
            array($this, 'render_edit_page')
        );
    }


    /**
     * Render the invoices listing page
     */
    public function render_invoices_page()
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p>' . esc_html__('WooCommerce is required for this feature.', 'order-manager-plus') . '</p></div>';
            return;
        }

        // Get recent orders
        $args = array(
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'objects',
        );

        $orders = wc_get_orders($args);

        // Start output
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Order Invoices', 'order-manager-plus') . '</h1>';
        echo '<p>' . esc_html__('View and print invoices for recent orders.', 'order-manager-plus') . '</p>';

        if (!empty($orders)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . esc_html__('Order', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Date', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Customer', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Status', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Total', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Actions', 'order-manager-plus') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($orders as $order) {
                echo '<tr>';
                echo '<td>' . esc_html($order->get_order_number()) . '</td>';
                echo '<td>' . esc_html($order->get_date_created()->date_i18n(get_option('date_format') . ' ' . get_option('time_format'))) . '</td>';
                echo '<td>' . esc_html($order->get_formatted_billing_full_name()) . '</td>';
                echo '<td><span class="omp-status-' . esc_attr($order->get_status()) . '">' . esc_html(wc_get_order_status_name($order->get_status())) . '</span></td>';
                echo '<td>' . wp_kses_post($order->get_formatted_order_total()) . '</td>';
                echo '<td>';
                echo '<a href="' . esc_url(admin_url('admin.php?page=omp_order_invoice&order_id=' . $order->get_id())) . '" class="button" target="_blank">' . esc_html__('View Invoice', 'order-manager-plus') . '</a>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="notice notice-info"><p>' . esc_html__('No orders found.', 'order-manager-plus') . '</p></div>';
        }

        echo '</div>';
    }

    /**
     * Render the dashboard page
     */
    public function render_dashboard_page()
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Order Manager Plus', 'order-manager-plus') . '</h1>';
        echo '<p>' . esc_html__('Welcome to Order Manager Plus for WooCommerce.', 'order-manager-plus') . '</p>';
        echo '</div>';
    }

    /**
     * Render the invoice page
     */
    public function render_invoice_page()
    {
        // Include the invoice template
        $invoice_generator = new OMP_Invoice_Generator();
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        echo $invoice_generator->generate_invoice($order_id);
    }

    /**
     * Render the edit page
     */
    public function render_edit_page()
    {
        // Include the edit order template
        include_once OMP_PLUGIN_DIR . 'includes/templates/edit-order-template.php';
    }
}