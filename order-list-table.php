<?php
/**
 * Plugin Name: WooCommerce Order List Table
 * Description: To show Woocommerce recent order list on a table with edit and invoice functionality. 
 * Version:     3.2
 * Author:      SoM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Original enqueues and requires stay the same
function oltew_order_list_table_enqueue_scripts() {
    $dir = plugin_dir_url(__FILE__);
    wp_enqueue_style('order-list-table', $dir . '/css/custom-style.css', array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'oltew_order_list_table_enqueue_scripts');

require_once( __DIR__ . '/woo-table-block.php' );
require_once( __DIR__ . '/shortcode.php' );
require_once( __DIR__ . '/widgets-loader.php' );
require_once( __DIR__ . '/date-ago-function.php' );

function enqueue_datepicker_scripts() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script('custom-export', plugin_dir_url(__FILE__) . 'js/export.js', array('jquery', 'jquery-ui-datepicker'), '', true);
}
add_action('wp_enqueue_scripts', 'enqueue_datepicker_scripts');

// Register admin pages
add_action('admin_menu', 'register_order_pages');

function register_order_pages() {
    // Invoice Page
    add_submenu_page(
        null,
        'Order Invoice',
        'Order Invoice',
        'manage_woocommerce',
        'wc_order_invoice',
        'render_invoice_page'
    );
    
    // Edit Page
    add_submenu_page(
        null,
        'Edit Order',
        'Edit Order',
        'manage_woocommerce',
        'wc_order_edit',
        'render_edit_page'
    );
}

// Add action buttons
add_filter('woocommerce_my_account_my_orders_actions', 'add_custom_order_actions', 10, 2);
function add_custom_order_actions($actions, $order) {
    if (current_user_can('manage_woocommerce')) {
        // Add Invoice action
        $actions['invoice'] = array(
            'url'  => admin_url('admin.php?page=wc_order_invoice&order_id=' . $order->get_id()),
            'name' => __('Invoice', 'woocommerce')
        );
        
        // Add Edit action
        $actions['edit'] = array(
            'url'  => admin_url('admin.php?page=wc_order_edit&order_id=' . $order->get_id()),
            'name' => __('Edit', 'woocommerce')
        );
    }
    return $actions;
}

// Render Invoice Page Function
function render_invoice_page() {
    if (!isset($_GET['order_id'])) {
        return;
    }

    $order_id = intval($_GET['order_id']);
    $order = wc_get_order($order_id);

    if (!$order) {
        echo '<div style="text-align: center; padding: 50px; color: #721c24; background-color: #f8d7da; border-radius: 8px;">
                <h2>خطأ</h2>
                <p>رقم الطلب غير صحيح.</p>
              </div>';
        return;
    }

    $billing = $order->get_address('billing');
    ?>
    <div style="font-family: MadaniArabic-Regular, sans-serif; max-width: 800px; margin: 40px auto; padding: 30px; box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 8px; background-color: #fff;">
        <!-- Header Section -->
        <div style="display: flex;justify-content: space-between;align-items: center;margin-bottom: 0px;border-bottom: 2px solid #004C4C;padding-bottom: 8px; direction: rtl;">
            <div style="text-align: right;">
                <img src="/wp-content/uploads/2025/01/logo-e1735811891138.webp" alt="pureness shea" style="max-width: 150px;"><br>
                <p style="color: #666; font-size: 14px; margin: .5em 0;">أول متجـر  مصري متخصص فــي زبـــدة الشيـــــا بس!</p>
            </div>
            <div>
                <h1 style="color: #004C4C; margin: 0; font-size: 24px;">فاتورة #<?php echo esc_html($order->get_order_number()); ?></h1>
                <p style="color: #666; margin: 5px 0;">تاريخ الطلب: <?php echo esc_html($order->get_date_created()->format('Y-m-d')); ?></p>
            </div>
        </div>

        <!-- Billing Information Section -->
        <div style="background-color: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin: 10px 0; overflow: hidden;">
            <div style="background-color: #f8f9fa; padding: 15px 20px;">
                <h2 style="color: #333; margin: 0; font-size: 20px; text-align: right;">
                    معلومات الفاتورة
                </h2>
            </div>

            <div style="padding: 10px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; direction: rtl;">
                <!-- Personal Info Section -->
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <div style="margin-bottom: 15px;">
                        <label style="color: #666; font-size: 14px; display: block; margin-bottom: 5px;">الاسم</label>
                        <div style="color: #333; font-size: 16px; font-weight: 500;">
                            <?php echo esc_html($billing['first_name'] . ' ' . $billing['last_name']); ?>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="color: #666; font-size: 14px; display: block; margin-bottom: 5px;">البريد الإلكتروني</label>
                        <div style="color: #333; font-size: 16px; direction: ltr; text-align: right;">
                            <?php echo esc_html($billing['email']); ?>
                        </div>
                    </div>
                    
                    <div>
                        <label style="color: #666; font-size: 14px; display: block; margin-bottom: 5px;">الهاتف</label>
                        <div style="color: #333; font-size: 16px; direction: ltr; text-align: right;">
                            <?php echo esc_html($billing['phone']); ?>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px;">
                    <div style="margin-bottom: 15px;">
                        <label style="color: #666; font-size: 14px; display: block; margin-bottom: 5px;">العنوان</label>
                        <div style="color: #333; font-size: 16px;">
                            <?php echo esc_html($billing['address_1']); ?>
                            <?php if (!empty($billing['address_2'])): ?>
                                <br><?php echo esc_html($billing['address_2']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="color: #666; font-size: 14px; display: block; margin-bottom: 5px;">المحافظة</label>
                            <div style="color: #333; font-size: 16px;">
                                <?php 
                                    $state_code = $order->get_billing_state();
                                    $country_code = $order->get_billing_country();
                                    echo esc_html(WC()->countries->get_states($country_code)[$state_code] ?? '');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; direction: rtl; background-color: #fff; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="background-color: #004C4C; color: white;">
                    <th style="padding: 15px; text-align: right;">المنتج</th>
                    <th style="padding: 15px; text-align: center;">الكمية</th>
                    <th style="padding: 15px; text-align: center;">السعر</th>
                    <th style="padding: 15px; text-align: center;">السعر الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $alternate = false;
                foreach ($order->get_items() as $item): 
                    $alternate = !$alternate;
                    $bg_color = $alternate ? '#f8f9fa' : '#fff';
                ?>
                    <tr style="background-color: <?php echo $bg_color; ?>;">
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: right;">
                            <?php echo esc_html($item->get_name()); ?>
                        </td>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">
                            <?php echo esc_html($item->get_quantity()); ?>
                        </td>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">
                            <?php echo wc_price($item->get_total() / $item->get_quantity()); ?>
                        </td>
                        <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">
                            <?php echo wc_price($item->get_total()); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Order Summary -->
        <div style="display: flex;justify-content: flex-start;margin-bottom: 10px;gap: 20px;direction: rtl;">
            <div style="width: 50%; background-color: #f8f9fa; padding: 20px; border-radius: 8px; direction: ltr;">
                <h3 style="margin-top: 0; color: #333; text-align: right;">ملخص الطلب</h3>
                <div style="border-top: 1px solid #dee2e6; margin-top: 10px; padding-top: 10px;">
                    <!-- Subtotal -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span><?php echo wc_price($order->get_subtotal()); ?></span>
                        <strong style="direction: rtl;">المجموع الفرعي:</strong>
                    </div>

                    <!-- Shipping -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span><?php echo wc_price($order->get_shipping_total()); ?></span>
                        <strong style="direction: rtl;">الشحن:</strong>
                    </div>

                    <!-- Discount -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span><?php echo wc_price($order->get_discount_total()); ?></span>
                        <strong style="direction: rtl;">الخصم:</strong>
                    </div>

                    <!-- Fees -->
                    <?php foreach ($order->get_fees() as $fee): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span><?php echo wc_price($fee->get_total()); ?></span>
                            <strong style="direction: rtl;"><?php echo esc_html($fee->get_name()); ?>:</strong>
                        </div>
                    <?php endforeach; ?>

                    <!-- Total -->
                    <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 2px solid #004C4C;">
                        <span style="font-size: 18px; font-weight: bold; color: #004C4C;">
                            <?php echo wc_price($order->get_total()); ?>
                        </span>
                        <strong style="font-size: 18px; direction: rtl;">المجموع:</strong>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; text-align: right; width: 50%; direction: rtl;">
                <p style="margin: 5px 0;">
                    <strong>طريقة الدفع:</strong> <?php echo esc_html($order->get_payment_method_title()); ?>
                </p>
                
                <?php if ($order->get_customer_note()): ?>
                    <p style="margin: 5px 0;">
                        <strong>ملاحظات العميل:</strong> <?php echo esc_html($order->get_customer_note()); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// Keep the Edit functionality from previous code...
// [Previous render_edit_page and handle_order_edit_form functions remain the same]