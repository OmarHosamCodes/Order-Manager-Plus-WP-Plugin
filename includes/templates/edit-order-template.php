<?php
/**
 * Edit Order Template
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get the order
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order = wc_get_order($order_id);

if (!$order) {
    echo '<div class="omp-error">
            <h2>' . esc_html__('Error', 'order-manager-plus') . '</h2>
            <p>' . esc_html__('Invalid order ID.', 'order-manager-plus') . '</p>
          </div>';
    return;
}

// Get billing info
$billing = $order->get_address('billing');
?>
<div class="wrap omp-edit-order-wrap">
    <h1><?php echo sprintf(esc_html__('Edit Order #%s', 'order-manager-plus'), $order->get_order_number()); ?></h1>

    <div class="omp-order-meta">
        <p>
            <strong><?php echo esc_html__('Order Date:', 'order-manager-plus'); ?></strong>
            <?php echo esc_html($order->get_date_created()->date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?>
        </p>
        <p>
            <strong><?php echo esc_html__('Payment Method:', 'order-manager-plus'); ?></strong>
            <?php echo esc_html($order->get_payment_method_title()); ?>
        </p>
    </div>

    <form method="post" id="omp-edit-order-form" class="omp-form">
        <?php wp_nonce_field('omp_edit_order', 'omp_edit_order_nonce'); ?>

        <div class="omp-form-sections">
            <div class="omp-form-section">
                <h2><?php echo esc_html__('Billing Information', 'order-manager-plus'); ?></h2>

                <div class="omp-form-row">
                    <div class="omp-form-field">
                        <label
                            for="billing_first_name"><?php echo esc_html__('First Name', 'order-manager-plus'); ?></label>
                        <input type="text" id="billing_first_name" name="billing_first_name"
                            value="<?php echo esc_attr($billing['first_name']); ?>" />
                    </div>

                    <div class="omp-form-field">
                        <label
                            for="billing_last_name"><?php echo esc_html__('Last Name', 'order-manager-plus'); ?></label>
                        <input type="text" id="billing_last_name" name="billing_last_name"
                            value="<?php echo esc_attr($billing['last_name']); ?>" />
                    </div>
                </div>

                <div class="omp-form-row">
                    <div class="omp-form-field">
                        <label for="billing_email"><?php echo esc_html__('Email', 'order-manager-plus'); ?></label>
                        <input type="email" id="billing_email" name="billing_email"
                            value="<?php echo esc_attr($billing['email']); ?>" />
                    </div>

                    <div class="omp-form-field">
                        <label for="billing_phone"><?php echo esc_html__('Phone', 'order-manager-plus'); ?></label>
                        <input type="tel" id="billing_phone" name="billing_phone"
                            value="<?php echo esc_attr($billing['phone']); ?>" />
                    </div>
                </div>

                <div class="omp-form-row">
                    <div class="omp-form-field">
                        <label
                            for="billing_address_1"><?php echo esc_html__('Address Line 1', 'order-manager-plus'); ?></label>
                        <input type="text" id="billing_address_1" name="billing_address_1"
                            value="<?php echo esc_attr($billing['address_1']); ?>" />
                    </div>

                    <div class="omp-form-field">
                        <label
                            for="billing_address_2"><?php echo esc_html__('Address Line 2', 'order-manager-plus'); ?></label>
                        <input type="text" id="billing_address_2" name="billing_address_2"
                            value="<?php echo esc_attr($billing['address_2']); ?>" />
                    </div>
                </div>

                <div class="omp-form-row">
                    <div class="omp-form-field">
                        <label for="billing_city"><?php echo esc_html__('City', 'order-manager-plus'); ?></label>
                        <input type="text" id="billing_city" name="billing_city"
                            value="<?php echo esc_attr($billing['city']); ?>" />
                    </div>

                    <div class="omp-form-field">
                        <label for="billing_state"><?php echo esc_html__('State', 'order-manager-plus'); ?></label>
                        <input type="text" id="billing_state" name="billing_state"
                            value="<?php echo esc_attr($billing['state']); ?>" />
                    </div>

                    <div class="omp-form-field">
                        <label
                            for="billing_postcode"><?php echo esc_html__('Postcode / ZIP', 'order-manager-plus'); ?></label>
                        <input type="text" id="billing_postcode" name="billing_postcode"
                            value="<?php echo esc_attr($billing['postcode']); ?>" />
                    </div>
                </div>
            </div>

            <div class="omp-form-section">
                <h2><?php echo esc_html__('Order Items', 'order-manager-plus'); ?></h2>

                <table class="omp-order-items order-edit-section">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Product', 'order-manager-plus'); ?></th>
                            <th><?php echo esc_html__('Quantity', 'order-manager-plus'); ?></th>
                            <th><?php echo esc_html__('Price', 'order-manager-plus'); ?></th>
                            <th><?php echo esc_html__('Total', 'order-manager-plus'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->get_items() as $item_id => $item):
                            $product = $item->get_product();
                            $price = $item->get_total() / $item->get_quantity();
                            ?>
                            <tr>
                                <td>
                                    <?php echo esc_html($item->get_name()); ?>
                                    <?php if ($product && $product->get_sku()): ?>
                                        <br><small><?php echo esc_html__('SKU:', 'order-manager-plus'); ?>
                                            <?php echo esc_html($product->get_sku()); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="number" name="item_qty[<?php echo esc_attr($item_id); ?>]"
                                        value="<?php echo esc_attr($item->get_quantity()); ?>" min="1"
                                        class="omp-quantity-input" />
                                </td>
                                <td><?php echo wp_kses_post(wc_price($price)); ?></td>
                                <td class="item-total"><?php echo wp_kses_post(wc_price($item->get_total())); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="omp-order-totals">
                    <div class="omp-total-row">
                        <span><?php echo esc_html__('Subtotal:', 'order-manager-plus'); ?></span>
                        <span><?php echo wp_kses_post(wc_price($order->get_subtotal())); ?></span>
                    </div>

                    <div class="omp-total-row">
                        <span><?php echo esc_html__('Shipping:', 'order-manager-plus'); ?></span>
                        <span><?php echo wp_kses_post(wc_price($order->get_shipping_total())); ?></span>
                    </div>

                    <?php if ($order->get_discount_total() > 0): ?>
                        <div class="omp-total-row">
                            <span><?php echo esc_html__('Discount:', 'order-manager-plus'); ?></span>
                            <span><?php echo wp_kses_post(wc_price($order->get_discount_total())); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($order->get_fees() as $fee): ?>
                        <div class="omp-total-row">
                            <span><?php echo esc_html($fee->get_name()); ?>:</span>
                            <span><?php echo wp_kses_post(wc_price($fee->get_total())); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($order->get_total_tax() > 0): ?>
                        <div class="omp-total-row">
                            <span><?php echo esc_html__('Tax:', 'order-manager-plus'); ?></span>
                            <span><?php echo wp_kses_post(wc_price($order->get_total_tax())); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="omp-total-row omp-grand-total">
                        <span><?php echo esc_html__('Total:', 'order-manager-plus'); ?></span>
                        <span id="order-total"><?php echo wp_kses_post(wc_price($order->get_total())); ?></span>
                    </div>
                </div>
            </div>

            <div class="omp-form-section">
                <h2><?php echo esc_html__('Order Status', 'order-manager-plus'); ?></h2>

                <div class="omp-form-field">
                    <label for="order_status"><?php echo esc_html__('Status', 'order-manager-plus'); ?></label>
                    <select id="order_status" name="order_status">
                        <option value=""><?php echo esc_html__('— No Change —', 'order-manager-plus'); ?></option>
                        <?php foreach (wc_get_order_statuses() as $status => $label):
                            $status_key = str_replace('wc-', '', $status);
                            $selected = $order->get_status() === $status_key ? 'selected="selected"' : '';
                            ?>
                            <option value="<?php echo esc_attr($status_key); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="omp-form-field">
                    <label for="order_note"><?php echo esc_html__('Order Note', 'order-manager-plus'); ?></label>
                    <textarea id="order_note" name="order_note" rows="3"
                        placeholder="<?php echo esc_attr__('Add a note about this edit (optional)', 'order-manager-plus'); ?>"></textarea>
                </div>
            </div>
        </div>

        <div class="omp-form-actions">
            <input type="hidden" name="omp_save_order" value="1" />
            <button type="submit"
                class="button button-primary"><?php echo esc_html__('Save Changes', 'order-manager-plus'); ?></button>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=shop_order')); ?>"
                class="button"><?php echo esc_html__('Cancel', 'order-manager-plus'); ?></a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=omp_order_invoice&order_id=' . $order_id)); ?>"
                class="button" target="_blank"><?php echo esc_html__('View Invoice', 'order-manager-plus'); ?></a>
        </div>
    </form>
</div>