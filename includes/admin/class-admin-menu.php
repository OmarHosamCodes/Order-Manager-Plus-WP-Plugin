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

        // Add submenu pages
        add_submenu_page(
            'order_manager_plus',
            __('Dashboard', 'order-manager-plus'),
            __('Dashboard', 'order-manager-plus'),
            'manage_woocommerce',
            'order_manager_plus',
            array($this, 'render_dashboard_page')
        );

        // Add hidden admin pages for invoice and order editing
        add_submenu_page(
            null, // No parent menu - hidden page
            __('Order Invoice', 'order-manager-plus'),
            __('Order Invoice', 'order-manager-plus'),
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