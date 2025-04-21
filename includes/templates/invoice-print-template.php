<?php
/**
 * Invoice Print Template
 * 
 * @package OrderManagerPlus
 * A clean, full-width template specifically for printing
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// This template should only be included from invoice-template.php
if (!isset($order) || !isset($theme_settings)) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" dir="<?php echo $dir_attribute; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Invoice', 'order-manager-plus'); ?>
        #<?php echo esc_html($order->get_order_number()); ?></title>
    <style>
        body {
            font-family:
                <?php echo $theme_settings['font_family']; ?>
            ;
            color: #0f0f0f;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            direction:
                <?php echo $dir_attribute; ?>
            ;
            width: 100%;
        }

        #omp-body {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        h1,
        h2,
        h3 {
            color:
                <?php echo $theme_settings['primary_color']; ?>
            ;
            margin-top: 0;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid
                <?php echo $theme_settings['primary_color']; ?>
            ;
            padding-bottom: 15px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .company-info {
            text-align:
                <?php echo $is_rtl ? 'right' : 'left'; ?>
            ;
        }

        .invoice-info {
            text-align:
                <?php echo $is_rtl ? 'left' : 'right'; ?>
            ;
        }

        .company-logo {
            max-width: 200px;
            max-height: 80px;
        }

        .info-section {
            margin-bottom: 30px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius:
                <?php echo $theme_settings['border_radius']; ?>
                px;
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
            background-color:
                <?php echo $theme_settings['primary_color']; ?>
            ;
            color: white;
            padding: 12px;
            text-align:
                <?php echo $is_rtl ? 'right' : 'left'; ?>
            ;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .order-details {
            padding-left: 20px;
            padding-right: 20px;
        }

        .order-summary {
            display: flex;
            justify-content:
                <?php echo $is_rtl ? 'flex-start' : 'flex-end'; ?>
            ;
            margin-bottom: 30px;
            padding-right: 20px;
        }

        .summary-box {
            width: 350px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius:
                <?php echo $theme_settings['border_radius']; ?>
                px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .summary-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid
                <?php echo $theme_settings['primary_color']; ?>
            ;
            font-weight: bold;
            font-size: 18px;
        }

        .footer {
            margin-top: 40px;
            padding: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
            }

            #omp-body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            html,
            body {
                width: 100%;
                height: 100%;
            }
        }
    </style>
    <script>
        // Auto-print functionality
        window.onload = function () {
            // Wait for all resources to load before printing
            setTimeout(function () {
                window.print();
            }, 500);
        }
    </script>
</head>

<body>
    <section id="omp-body">
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

        <div class="order-details">
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
                            <td><?php echo wp_kses_post(wc_price($order->get_item_subtotal($item, false, true))); ?></td>
                            <td><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

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
    </section>
</body>

</html>

<?php
// Exit to prevent WordPress footer output
exit;