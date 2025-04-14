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
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Invoice', 'order-manager-plus'); ?>
        #<?php echo esc_html($order->get_order_number()); ?></title>
    <style>
        #omp-body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            color: #0f0f0f;
            line-height: 1.5;
            max-width: 80%;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: #f0f0f0;
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
            /* Already prefixed in HTML, added here for consistency */
            background-color: #2c3e50;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            height: 50px;
        }

        .back-button {
            background-color: transparent;
            color: #2c3e50;
            border: 2px solid #2c3e50;
            box-sizing: border-box;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            text-decoration: none;
            height: 50px;
            width: max-content;

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
    <script>
        function printInvoiceOnly() {
            // Create a new window with only invoice content
            var printWindow = window.open('', '_blank');

            // Clone the invoice content without actions buttons
            var contentClone = document.querySelector('#omp-body').cloneNode(true);
            var actionsDiv = contentClone.querySelector('.invoice-actions');
            if (actionsDiv) {
                actionsDiv.style.display = 'none';
            }

            // Add necessary styles and content
            printWindow.document.write('<html><head><title><?php echo esc_js(__("Invoice", "order-manager-plus")); ?> #<?php echo esc_js($order->get_order_number()); ?></title>');
            printWindow.document.write('<style>');

            // Get all styles from the current document
            var styles = document.getElementsByTagName('style');
            for (var i = 0; i < styles.length; i++) {
                printWindow.document.write(styles[i].innerHTML);
            }

            printWindow.document.write('.no-print { display: none !important; }');
            printWindow.document.write('</style></head><body>');
            printWindow.document.write(contentClone.outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            // Print after content is loaded
            printWindow.onload = function () {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        }
    </script>
</body>

</html>

<?php
// Exit to prevent WordPress footer output
exit;