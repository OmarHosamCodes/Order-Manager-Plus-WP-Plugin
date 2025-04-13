<?php
/**
 * Invoice Template
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin options
$options = get_option('omp_settings', array());

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
$shipping = $order->get_address('shipping');

// Get the company info from WordPress settings
$company_name = get_bloginfo('name');
$company_description = get_bloginfo('description');
$company_logo_id = get_theme_mod('custom_logo');
$company_logo_url = '';

if ($company_logo_id) {
    $company_logo_data = wp_get_attachment_image_src($company_logo_id, 'full');
    if ($company_logo_data) {
        $company_logo_url = $company_logo_data[0];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Invoice', 'order-manager-plus'); ?>
        #<?php echo esc_html($order->get_order_number()); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            color: #333;
            line-height: 1.5;
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: #fff;
        }

        h1,
        h2,
        h3 {
            color: #2c3e50;
            margin-top: 0;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 15px;
        }

        .company-info {
            text-align: left;
        }

        .invoice-info {
            text-align: right;
        }

        .company-logo {
            max-width: 200px;
            max-height: 80px;
        }

        .info-section {
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .info-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }

        .info-value {
            color: #333;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th {
            background-color: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .order-summary {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }

        .summary-box {
            width: 350px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .summary-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #2c3e50;
            font-weight: bold;
            font-size: 18px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .print-button {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }

        @media print {
            body {
                box-shadow: none;
                margin: 0;
                padding: 10px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: right;">
        <button class="print-button" onclick="window.print();">
            <?php echo esc_html__('Print Invoice', 'order-manager-plus'); ?>
        </button>
    </div>

    <div class="invoice-header">
        <div class="company-info">
            <?php if ($company_logo_url): ?>
                <img src="<?php echo esc_url($company_logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>"
                    class="company-logo"><br>
            <?php else: ?>
                <h2><?php echo esc_html($company_name); ?></h2>
            <?php endif; ?>

            <?php if ($company_description): ?>
                <p><?php echo esc_html($company_description); ?></p>
            <?php endif; ?>
        </div>

        <div class="invoice-info">
            <h1><?php echo esc_html__('Invoice', 'order-manager-plus'); ?>
                #<?php echo esc_html($order->get_order_number()); ?></h1>
            <p><?php echo esc_html__('Date:', 'order-manager-plus'); ?>
                <?php echo esc_html($order->get_date_created()->date_i18n(get_option('date_format'))); ?>
            </p>
        </div>
    </div>

    <div class="info-section">
        <h2><?php echo esc_html__('Billing Information', 'order-manager-plus'); ?></h2>

        <div class="info-grid">
            <div class="info-box">
                <span class="info-label"><?php echo esc_html__('Customer', 'order-manager-plus'); ?></span>
                <div class="info-value">
                    <?php echo esc_html($billing['first_name'] . ' ' . $billing['last_name']); ?>
                </div>

                <span class="info-label"><?php echo esc_html__('Email', 'order-manager-plus'); ?></span>
                <div class="info-value">
                    <?php echo esc_html($billing['email']); ?>
                </div>

                <span class="info-label"><?php echo esc_html__('Phone', 'order-manager-plus'); ?></span>
                <div class="info-value">
                    <?php echo esc_html($billing['phone']); ?>
                </div>
            </div>

            <div class="info-box">
                <span class="info-label"><?php echo esc_html__('Billing Address', 'order-manager-plus'); ?></span>
                <div class="info-value">
                    <?php echo esc_html($billing['address_1']); ?>
                    <?php if (!empty($billing['address_2'])): ?>
                        <br><?php echo esc_html($billing['address_2']); ?>
                    <?php endif; ?>
                    <br><?php echo esc_html($billing['city']); ?>
                    <?php if (!empty($billing['state'])): ?>
                        ,
                        <?php echo esc_html(WC()->countries->get_states($billing['country'])[$billing['state']] ?? $billing['state']); ?>
                    <?php endif; ?>
                    <?php if (!empty($billing['postcode'])): ?>
                        , <?php echo esc_html($billing['postcode']); ?>
                    <?php endif; ?>
                    <?php if (!empty($billing['country'])): ?>
                        <br><?php echo esc_html(WC()->countries->get_countries()[$billing['country']] ?? $billing['country']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <h2><?php echo esc_html__('Order Details', 'order-manager-plus'); ?></h2>

    <table>
        <thead>
            <tr>
                <th><?php echo esc_html__('Product', 'order-manager-plus'); ?></th>
                <th><?php echo esc_html__('Quantity', 'order-manager-plus'); ?></th>
                <th><?php echo esc_html__('Price', 'order-manager-plus'); ?></th>
                <th><?php echo esc_html__('Total', 'order-manager-plus'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order->get_items() as $item): ?>
                <tr>
                    <td><?php echo esc_html($item->get_name()); ?></td>
                    <td><?php echo esc_html($item->get_quantity()); ?></td>
                    <td><?php echo wp_kses_post(wc_price($item->get_total() / $item->get_quantity())); ?></td>
                    <td><?php echo wp_kses_post(wc_price($item->get_total())); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="order-summary">
        <div class="summary-box">
            <h3><?php echo esc_html__('Order Summary', 'order-manager-plus'); ?></h3>

            <div class="summary-row">
                <span><?php echo esc_html__('Subtotal:', 'order-manager-plus'); ?></span>
                <span><?php echo wp_kses_post(wc_price($order->get_subtotal())); ?></span>
            </div>

            <div class="summary-row">
                <span><?php echo esc_html__('Shipping:', 'order-manager-plus'); ?></span>
                <span><?php echo wp_kses_post(wc_price($order->get_shipping_total())); ?></span>
            </div>

            <?php if ($order->get_discount_total() > 0): ?>
                <div class="summary-row">
                    <span><?php echo esc_html__('Discount:', 'order-manager-plus'); ?></span>
                    <span><?php echo wp_kses_post(wc_price($order->get_discount_total())); ?></span>
                </div>
            <?php endif; ?>

            <?php foreach ($order->get_fees() as $fee): ?>
                <div class="summary-row">
                    <span><?php echo esc_html($fee->get_name()); ?>:</span>
                    <span><?php echo wp_kses_post(wc_price($fee->get_total())); ?></span>
                </div>
            <?php endforeach; ?>

            <?php if ($order->get_total_tax() > 0): ?>
                <div class="summary-row">
                    <span><?php echo esc_html__('Tax:', 'order-manager-plus'); ?></span>
                    <span><?php echo wp_kses_post(wc_price($order->get_total_tax())); ?></span>
                </div>
            <?php endif; ?>

            <div class="summary-row summary-total">
                <span><?php echo esc_html__('Total:', 'order-manager-plus'); ?></span>
                <span><?php echo wp_kses_post(wc_price($order->get_total())); ?></span>
            </div>
        </div>
    </div>

    <?php if ($order->get_customer_note()): ?>
        <div class="info-section">
            <h3><?php echo esc_html__('Order Notes', 'order-manager-plus'); ?></h3>
            <p><?php echo esc_html($order->get_customer_note()); ?></p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p><?php echo esc_html__('Thank you for your business!', 'order-manager-plus'); ?></p>
        <p><?php echo esc_html($company_name); ?> &copy; <?php echo date('Y'); ?></p>
    </div>
</body>

</html>
<?php
// Exit to prevent WordPress footer output
exit;