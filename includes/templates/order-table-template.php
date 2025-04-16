<?php
/**
 * Order Table Template
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin settings
$options = get_option('omp_settings', array());
$theme_settings = get_option('omp_theme_settings', array());
$enable_invoice = isset($options['enable_invoice']) ? $options['enable_invoice'] : true;
$enable_edit = isset($options['enable_edit']) ? $options['enable_edit'] : true;
$enable_export = isset($options['enable_export']) ? $options['enable_export'] : true;

// Check for RTL direction
$is_rtl = is_rtl();
$dir_attribute = $is_rtl ? 'rtl' : 'ltr';

// Get theme colors from settings (to be used as CSS variables)
$primary_color = !empty($theme_settings['primary_color']) ? $theme_settings['primary_color'] : '#2c3e50';
$secondary_color = !empty($theme_settings['secondary_color']) ? $theme_settings['secondary_color'] : '#3498db';
$success_color = !empty($theme_settings['success_color']) ? $theme_settings['success_color'] : '#27ae60';
$danger_color = !empty($theme_settings['danger_color']) ? $theme_settings['danger_color'] : '#e74c3c';
$border_radius = isset($theme_settings['border_radius']) ? absint($theme_settings['border_radius']) : 4;
$font_family = !empty($theme_settings['font_family']) ? $theme_settings['font_family'] : '';

// Apply RTL specific font settings
if ($is_rtl) {
    // Common RTL font stacks if no custom font is set
    if (empty($font_family)) {
        if (strpos(get_locale(), 'he_IL') !== false) {
            $font_family = 'Arial, sans-serif';
        } elseif (strpos(get_locale(), 'ar') === 0) {
            $font_family = 'Tahoma, Arial, sans-serif';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" dir="<?php echo $dir_attribute; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --omp-primary-color:
                <?php echo $primary_color; ?>
            ;
            --omp-secondary-color:
                <?php echo $secondary_color; ?>
            ;
            --omp-success-color:
                <?php echo $success_color; ?>
            ;
            --omp-danger-color:
                <?php echo $danger_color; ?>
            ;
            --omp-border-radius:
                <?php echo $border_radius; ?>
                px;
            <?php if (!empty($font_family)): ?>
                --omp-font-family:
                    <?php echo $font_family; ?>
                ;
            <?php endif; ?>
        }

        <?php if (!empty($font_family)): ?>
            .omp-order-table-container {
                font-family: var(--omp-font-family);
            }

        <?php endif; ?>

        /* RTL-specific alignments */
        html[dir="rtl"] .omp-actions-column {
            text-align: left;
        }

        html[dir="rtl"] .omp-checkbox-column {
            text-align: right;
        }

        html[dir="rtl"] .omp-export-filters label {
            margin-left: 20px;
            margin-right: 0;
        }

        html[dir="rtl"] .omp-action-buttons {
            flex-direction: row-reverse;
        }
    </style>
</head>

<body>
    <div class="omp-order-table-container">
        <?php if ($enable_export && current_user_can('manage_woocommerce')): ?>
            <div class="omp-action-buttons">
                <button id="omp-export-btn" class="omp-button omp-export-button">
                    <?php _e('Export Selected', 'order-manager-plus'); ?>
                </button>

                <div class="omp-export-filters">
                    <label>
                        <?php _e('Status:', 'order-manager-plus'); ?>
                        <select id="omp-export-status">
                            <option value="any"><?php _e('Any', 'order-manager-plus'); ?></option>
                            <?php foreach (wc_get_order_statuses() as $status => $label): ?>
                                <option value="<?php echo esc_attr($status); ?>"><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        <?php _e('From:', 'order-manager-plus'); ?>
                        <input type="text" id="omp-export-from" class="omp-datepicker" placeholder="YYYY-MM-DD">
                    </label>

                    <label>
                        <?php _e('To:', 'order-manager-plus'); ?>
                        <input type="text" id="omp-export-to" class="omp-datepicker" placeholder="YYYY-MM-DD">
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <div class="omp-order-table-wrap">
            <table id="omp-order-table">
                <thead>
                    <tr>
                        <th class="omp-checkbox-column">
                            <input type="checkbox" id="omp-select-all"
                                aria-label="<?php esc_attr_e('Select All Orders', 'order-manager-plus'); ?>" />
                        </th>
                        <th><?php _e('Order', 'order-manager-plus'); ?></th>
                        <th><?php _e('Date', 'order-manager-plus'); ?></th>
                        <th><?php _e('Status', 'order-manager-plus'); ?></th>
                        <th><?php _e('Customer', 'order-manager-plus'); ?></th>
                        <th><?php _e('Email', 'order-manager-plus'); ?></th>
                        <th><?php _e('Address', 'order-manager-plus'); ?></th>
                        <th><?php _e('Products', 'order-manager-plus'); ?></th>
                        <th><?php _e('Phone', 'order-manager-plus'); ?></th>
                        <th><?php _e('Notes', 'order-manager-plus'); ?></th>
                        <th><?php _e('Total', 'order-manager-plus'); ?></th>
                        <th><?php _e('Actions', 'order-manager-plus'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($orders->posts)) {
                        // Use custom loop for our orders object
                        foreach ($orders->posts as $order) {
                            if (!$order instanceof WC_Order) {
                                continue;
                            }

                            $order_date = $order->get_date_created();
                            $is_cancelled = ($order->get_status() === 'cancelled');
                            $row_class = $is_cancelled ? 'omp-order-cancelled' : '';
                            ?>
                            <tr class="<?php echo esc_attr($row_class); ?>">
                                <td class="omp-checkbox-column">
                                    <input type="checkbox" class="omp-order-checkbox"
                                        value="<?php echo esc_attr($order->get_id()); ?>" />
                                </td>
                                <td>
                                    <?php echo esc_html($order->get_order_number()); ?>
                                </td>
                                <td>
                                    <?php
                                    $date_formatter = new OMP_Date_Formatter();
                                    echo esc_html($date_formatter->format_date($order_date, isset($settings['order_time_format']) ? $settings['order_time_format'] : 'ago'));
                                    ?>
                                </td>
                                <td>
                                    <span class="omp-order-status omp-status-<?php echo esc_attr($order->get_status()); ?>">
                                        <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html($order->get_formatted_billing_full_name() ?: __('Guest', 'order-manager-plus')); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($order->get_billing_email()); ?>
                                </td>
                                <td>
                                    <?php
                                    $address_parts = array_filter([
                                        esc_html($order->get_billing_address_1()),
                                        esc_html($order->get_billing_address_2()),
                                        esc_html($order->get_billing_city()),
                                        esc_html(WC()->countries->get_states($order->get_billing_country())[$order->get_billing_state()] ?? ''),
                                        esc_html($order->get_billing_postcode()),
                                        esc_html(WC()->countries->get_countries()[$order->get_billing_country()] ?? '')
                                    ]);
                                    echo implode('<br>', $address_parts);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    foreach ($order->get_items() as $item) {
                                        echo esc_html($item->get_name()) . ' × ' . esc_html($item->get_quantity()) . '<br>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo esc_html($order->get_billing_phone()); ?>
                                </td>
                                <td>
                                    <?php echo esc_html($order->get_customer_note()); ?>
                                </td>
                                <td>
                                    <?php echo wp_kses_post($order->get_formatted_order_total()); ?>
                                </td>
                                <td class="omp-actions-column">
                                    <?php
                                    // Add invoice action if enabled
                                    if ($enable_invoice) {
                                        echo '<a href="' . esc_url(admin_url('admin.php?page=omp_order_invoice&order_id=' . $order->get_id())) . '" 
                                             class="omp-button omp-invoice-button" target="_blank" data-order-id="' . esc_attr($order->get_id()) . '">' .
                                            esc_html__('Invoice', 'order-manager-plus') .
                                            '</a>';
                                    }

                                    // Add edit action if enabled
                                    if ($enable_edit && current_user_can('manage_woocommerce')) {
                                        echo '<a href="' . esc_url(admin_url('post.php?post=' . $order->get_id() . '&action=edit')) . '" 
                                             class="omp-button omp-edit-button" target="_blank">' .
                                            esc_html__('Edit', 'order-manager-plus') .
                                            '</a>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="omp-pagination">
            <?php
            $pagenum_link = get_pagenum_link(999999999) ?: '';
            echo paginate_links([
                'base' => str_replace(999999999, '%#%', esc_url($pagenum_link)),
                'format' => '?paged=%#%',
                'current' => max(1, get_query_var('paged')),
                'total' => $orders->max_num_pages,
            ]);
            ?>
        </div>
    </div>
    <script>
        jQuery(document).ready(function ($) {
            // Initialize datepickers
            if ($.fn.datepicker) {
                $('.omp-datepicker').datepicker({
                    dateFormat: "yy-mm-dd",
                    changeMonth: true,
                    changeYear: true,
                    isRTL: <?php echo $is_rtl ? 'true' : 'false'; ?>
                });
            }

            // Handle select all checkboxes
            $('#omp-select-all').on('change', function () {
                $('.omp-order-checkbox').prop('checked', $(this).prop('checked'));
                if ($(this).prop('checked')) {
                    $('.omp-order-checkbox').closest('tr').addClass('omp-selected');
                } else {
                    $('.omp-order-checkbox').closest('tr').removeClass('omp-selected');
                }
            });

            // Handle individual checkboxes
            $('.omp-order-checkbox').on('change', function () {
                if ($(this).prop('checked')) {
                    $(this).closest('tr').addClass('omp-selected');
                } else {
                    $(this).closest('tr').removeClass('omp-selected');
                }

                // Update select all checkbox
                var allChecked = $('.omp-order-checkbox:checked').length === $('.omp-order-checkbox').length;
                $('#omp-select-all').prop('checked', allChecked);
            });

            // Handle export button
            $('#omp-export-btn').on('click', function () {
                var orderIds = [];
                var status = $('#omp-export-status').val();
                var fromDate = $('#omp-export-from').val();
                var toDate = $('#omp-export-to').val();

                // Get selected order IDs
                $('.omp-order-checkbox:checked').each(function () {
                    orderIds.push($(this).val());
                });

                // If no orders selected and no filters set, show message
                if (orderIds.length === 0 && status === 'any' && !fromDate && !toDate) {
                    alert(ompData.i18n?.select_orders || 'Please select at least one order or set filter criteria.');
                    return;
                }

                // Show loading state
                $(this).addClass('omp-loading').text(ompData.i18n?.exporting || 'Exporting...');

                // Make AJAX request
                $.ajax({
                    url: ompData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'omp_export_orders',
                        nonce: ompData.nonce,
                        order_ids: orderIds,
                        status: status,
                        from_date: fromDate,
                        to_date: toDate
                    },
                    success: function (response) {
                        if (response.success) {
                            // Create download link
                            var blob = new Blob([response.data.csv], { type: 'text/csv' });
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = response.data.filename;
                            link.style.display = 'none';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            alert(response.data.message || ompData.i18n?.export_error || 'Error exporting orders.');
                        }
                    },
                    error: function () {
                        alert(ompData.i18n?.export_error || 'Error exporting orders.');
                    },
                    complete: function () {
                        // Reset button state
                        $('#omp-export-btn').removeClass('omp-loading').text(ompData.i18n?.export_selected || 'Export Selected');
                    }
                });
            });
        });</script>
</body>

</html>