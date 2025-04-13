<?php
/**
 * Order Editor Class
 * 
 * Handles editing of WooCommerce orders
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Order_Editor Class
 */
class OMP_Order_Editor
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register AJAX handlers
        add_action('wp_ajax_omp_update_order', array($this, 'ajax_update_order'));
        add_action('wp_ajax_nopriv_omp_update_order', array($this, 'ajax_unauthorized'));

        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Enqueue scripts for order editing
     * 
     * @param string $hook Current admin page
     */
    public function enqueue_scripts($hook)
    {
        // Only enqueue on our order edit page
        if ($hook != 'admin_page_omp_order_edit') {
            return;
        }

        // Enqueue edit script with WooCommerce formatting options
        wp_enqueue_script(
            'omp-order-edit',
            OMP_PLUGIN_URL . 'assets/js/order-edit.js',
            array('jquery'),
            OMP_VERSION,
            true
        );

        // Localize script with currency formatting info
        wp_localize_script('omp-order-edit', 'ompEditData', array(
            'confirmMessage' => __('Are you sure you want to save these changes?', 'order-manager-plus'),
            'currencySymbol' => get_woocommerce_currency_symbol(),
            'currencyPosition' => get_option('woocommerce_currency_pos'),
            'decimalSeparator' => wc_get_price_decimal_separator(),
            'thousandSeparator' => wc_get_price_thousand_separator(),
            'decimals' => wc_get_price_decimals(),
        ));
    }

    /**
     * Handle unauthorized access
     */
    public function ajax_unauthorized()
    {
        wp_send_json_error(array(
            'message' => __('You do not have permission to edit orders.', 'order-manager-plus')
        ));
    }

    /**
     * AJAX handler for updating orders
     */
    public function ajax_update_order()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'omp-edit-nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'order-manager-plus')
            ));
        }

        // Check permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to edit orders.', 'order-manager-plus')
            ));
        }

        // Get order ID
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

        if (!$order_id) {
            wp_send_json_error(array(
                'message' => __('No order ID provided.', 'order-manager-plus')
            ));
        }

        // Get order
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(array(
                'message' => __('Invalid order ID.', 'order-manager-plus')
            ));
        }

        // Process item quantities
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $item_data) {
                // Validate data
                if (!isset($item_data['item_id']) || !isset($item_data['qty'])) {
                    continue;
                }

                $item_id = absint($item_data['item_id']);
                $qty = max(1, absint($item_data['qty'])); // Ensure positive quantity

                $order_item = $order->get_item($item_id);
                if ($order_item) {
                    // Use WC_Order_Item methods
                    if (method_exists($order_item, 'set_quantity')) {
                        $order_item->set_quantity($qty);
                        $order_item->save();
                    } else {
                        // Fallback for older WooCommerce versions
                        wc_update_order_item_meta($item_id, '_qty', $qty);
                    }
                }
            }
        }

        // Update billing information if provided
        if (isset($_POST['billing'])) {
            $billing = $_POST['billing'];

            if (isset($billing['first_name'])) {
                $order->set_billing_first_name(sanitize_text_field($billing['first_name']));
            }

            if (isset($billing['last_name'])) {
                $order->set_billing_last_name(sanitize_text_field($billing['last_name']));
            }

            if (isset($billing['email'])) {
                $order->set_billing_email(sanitize_email($billing['email']));
            }

            if (isset($billing['phone'])) {
                $order->set_billing_phone(sanitize_text_field($billing['phone']));
            }

            if (isset($billing['address_1'])) {
                $order->set_billing_address_1(sanitize_text_field($billing['address_1']));
            }

            if (isset($billing['address_2'])) {
                $order->set_billing_address_2(sanitize_text_field($billing['address_2']));
            }

            if (isset($billing['city'])) {
                $order->set_billing_city(sanitize_text_field($billing['city']));
            }

            if (isset($billing['state'])) {
                $order->set_billing_state(sanitize_text_field($billing['state']));
            }

            if (isset($billing['postcode'])) {
                $order->set_billing_postcode(sanitize_text_field($billing['postcode']));
            }

            if (isset($billing['country'])) {
                $order->set_billing_country(sanitize_text_field($billing['country']));
            }
        }

        // Update shipping information if provided
        if (isset($_POST['shipping'])) {
            $shipping = $_POST['shipping'];

            if (isset($shipping['first_name'])) {
                $order->set_shipping_first_name(sanitize_text_field($shipping['first_name']));
            }

            if (isset($shipping['last_name'])) {
                $order->set_shipping_last_name(sanitize_text_field($shipping['last_name']));
            }

            if (isset($shipping['address_1'])) {
                $order->set_shipping_address_1(sanitize_text_field($shipping['address_1']));
            }

            if (isset($shipping['address_2'])) {
                $order->set_shipping_address_2(sanitize_text_field($shipping['address_2']));
            }

            if (isset($shipping['city'])) {
                $order->set_shipping_city(sanitize_text_field($shipping['city']));
            }

            if (isset($shipping['state'])) {
                $order->set_shipping_state(sanitize_text_field($shipping['state']));
            }

            if (isset($shipping['postcode'])) {
                $order->set_shipping_postcode(sanitize_text_field($shipping['postcode']));
            }

            if (isset($shipping['country'])) {
                $order->set_shipping_country(sanitize_text_field($shipping['country']));
            }
        }

        // Update order status if provided
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $new_status = sanitize_text_field($_POST['status']);
            if (array_key_exists('wc-' . $new_status, wc_get_order_statuses())) {
                $order->set_status($new_status);
            }
        }

        // Add order note if provided
        if (isset($_POST['note']) && !empty($_POST['note'])) {
            $note = sanitize_textarea_field($_POST['note']);
            $is_customer_note = isset($_POST['customer_note']) && $_POST['customer_note'] === 'yes';

            $order->add_order_note($note, $is_customer_note);
        }

        // Allow plugins to modify the order before saving
        do_action('omp_before_order_update', $order, $_POST);

        // Calculate totals and save
        $order->calculate_totals();
        $order->save();

        // Allow plugins to perform actions after order update
        do_action('omp_after_order_update', $order, $_POST);

        // Prepare response data
        $response_data = array(
            'success' => true,
            'message' => __('Order updated successfully.', 'order-manager-plus'),
            'order_total' => html_entity_decode(wp_strip_all_tags($order->get_formatted_order_total())),
            'order_items' => array()
        );

        // Add updated line item totals to response
        foreach ($order->get_items() as $item_id => $item) {
            $response_data['order_items'][$item_id] = array(
                'total' => html_entity_decode(wp_strip_all_tags(wc_price(
                    method_exists($item, 'get_total') ? $item->get_total() : $item['line_total']
                ))),
                'subtotal' => html_entity_decode(wp_strip_all_tags(wc_price(
                    method_exists($item, 'get_subtotal') ? $item->get_subtotal() : $item['line_subtotal']
                )))
            );
        }

        wp_send_json_success($response_data);
    }

    /**
     * Update order item quantity
     * 
     * @param WC_Order $order Order object
     * @param int $item_id Item ID
     * @param int $quantity New quantity
     * @return bool Success status
     */
    public function update_item_quantity($order, $item_id, $quantity)
    {
        if (!$order || !$item_id) {
            return false;
        }

        // Get the order item
        $item = $order->get_item($item_id);

        if (!$item) {
            return false;
        }

        // Ensure quantity is at least 1
        $quantity = max(1, intval($quantity));

        // Update quantity
        if (method_exists($item, 'set_quantity')) {
            $item->set_quantity($quantity);
            $item->save();
        } else {
            // Fallback for older WooCommerce versions
            wc_update_order_item_meta($item_id, '_qty', $quantity);
        }

        return true;
    }

    /**
     * Recalculate order totals
     * 
     * @param WC_Order $order Order object
     * @return bool Success status
     */
    public function recalculate_order($order)
    {
        if (!$order) {
            return false;
        }

        // Recalculate totals
        $order->calculate_totals();
        $order->save();

        return true;
    }
}