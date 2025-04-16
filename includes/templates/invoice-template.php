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
$theme_settings = get_option('omp_theme_settings', array());

// Get the order
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order = wc_get_order($order_id);

if (!$order) {
    echo '<div class="error">
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

// Check for RTL direction
$is_rtl = is_rtl();
$dir_attribute = $is_rtl ? 'rtl' : 'ltr';

// Get theme colors from settings
$primary_color = !empty($theme_settings['primary_color']) ? $theme_settings['primary_color'] : '#2c3e50';
$secondary_color = !empty($theme_settings['secondary_color']) ? $theme_settings['secondary_color'] : '#3498db';
$success_color = !empty($theme_settings['success_color']) ? $theme_settings['success_color'] : '#27ae60';
$danger_color = !empty($theme_settings['danger_color']) ? $theme_settings['danger_color'] : '#e74c3c';
$border_radius = isset($theme_settings['border_radius']) ? absint($theme_settings['border_radius']) : 4;
$font_family = !empty($theme_settings['font_family']) ? $theme_settings['font_family'] : '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';

// Apply RTL specific font settings
if ($is_rtl) {
    // Common RTL font stacks
    if (strpos(get_locale(), 'he_IL') !== false) {
        $font_family = 'Arial, sans-serif';
    } elseif (strpos(get_locale(), 'ar') === 0) {
        $font_family = 'Tahoma, Arial, sans-serif';
    }
}

// Allow theme customization through filter
$theme_settings = apply_filters('omp_invoice_theme_settings', array(
    'primary_color' => $primary_color,
    'secondary_color' => $secondary_color,
    'success_color' => $success_color,
    'danger_color' => $danger_color,
    'border_radius' => $border_radius,
    'font_family' => $font_family,
    'is_rtl' => $is_rtl
));
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" dir="<?php echo $dir_attribute; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Invoice', 'order-manager-plus'); ?>
        #<?php echo esc_html($order->get_order_number()); ?></title>
    <style>
        #omp-body {
            font-family:
                <?php echo $theme_settings['font_family']; ?>
            ;
            color: #0f0f0f;
            line-height: 1.5;
            max-width: 80%;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius:
                <?php echo $theme_settings['border_radius']; ?>
                px;
            background-color: #f0f0f0;
            direction:
                <?php echo $dir_attribute; ?>
            ;
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

        .order-summary {
            display: flex;
            justify-content:
                <?php echo $is_rtl ? 'flex-start' : 'flex-end'; ?>
            ;
            margin-bottom: 30px;
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
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .print-button {
            /* Already prefixed in HTML, added here for consistency */
            background-color:
                <?php echo $theme_settings['primary_color']; ?>
            ;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius:
                <?php echo $theme_settings['border_radius']; ?>
                px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            height: 50px;
        }

        .back-button {
            background-color: transparent;
            color:
                <?php echo $theme_settings['primary_color']; ?>
            ;
            border: 2px solid
                <?php echo $theme_settings['primary_color']; ?>
            ;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            padding: 10px 20px;
            border-radius:
                <?php echo $theme_settings['border_radius']; ?>
                px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            text-decoration: none;
            height: 50px;
            width: max-content;
            margin-<?php echo $is_rtl ? 'left' : 'right'; ?>: 10px;
        }

        @media print {
            #omp-body {
                box-shadow: none;
                margin: 5px;
                padding: 10px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
    <script>
        function printInvoiceOnly() {
            window.print();
        }
    </script>
</head>

<body>

    <section id="omp-body">
        <div class="invoice-actions no-print">
            <button class="print-button" onclick="printInvoiceOnly();">
                <?php echo esc_html__('Print Invoice', 'order-manager-plus'); ?>
            </button>
            <button
                onclick="window.location.href='<?php echo esc_url(admin_url('admin.php?page=omp_order_invoices')); ?>'"
                class="back-button">
                <?php echo esc_html__('Back to Invoices', 'order-manager-plus'); ?>
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
                        <td><?php echo wp_kses_post(wc_price($order->get_item_subtotal($item, false, true))); // Price per item ?>
                        </td>
                        <td><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); // Total for line ?></td>
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
    </section>
</body>

</html>

<?php
// Exit to prevent WordPress footer output
exit;