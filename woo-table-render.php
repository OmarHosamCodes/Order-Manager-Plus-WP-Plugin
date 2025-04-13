<?php
// Ensure WooCommerce is active
if (!class_exists('WooCommerce')) {
    echo 'WooCommerce plugin is not active.';
    return;
}

$select_status = $settings['select_status'] ?? ['wc-completed', 'wc-processing', 'wc-on-hold', 'wc-failed', 'wc-cancelled'];
$list_per_page = $settings['list_per_page'] ?? 10;
$order_time_format = $settings['order_time_format'] ?? 'ago';
$order_by = $settings['order_by'] ?? 'desc';

$current_page = max(1, get_query_var('paged'));

$args = [
    'post_type' => 'shop_order',
    'post_status' => $select_status,
    'posts_per_page' => min($list_per_page, 100),
    'paged' => $current_page,
    'order' => $order_by,
];

$orders = new WP_Query($args);

// Check if the query returns results
if (!$orders instanceof WP_Query || !$orders->have_posts()) {
    echo 'No orders found or query failed.';
    return;
}
?>

<style>
.admin-origin {
    background-color: #F3CC9E !important;
}
.cancelled-order {
    background-color: #ef5a59 !important;
    color: white;
}
/* Ensure text remains visible in cancelled rows */
.cancelled-order td {
    color: white;
}
</style>

<div class="oltew-order-list-table" style="overflow-x:auto;">
    <table id="dataTable">
        <thead>
            <tr>
                <th class="column-checkbox">
                    <input type="checkbox" id="select-all" aria-label="Select All Orders" />
                </th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Billing Address</th>
                <th>Order Date</th>
                <th>Products</th>
                <th>Phone</th>
                <th>2nd Phone</th>
                <th>Notes</th>
                <th>Shipping</th>
                <th>Discount</th>
                <th>Total Amount</th>
                <th>Origin</th>
                <th>Status</th>
                <th>Print Invoice</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($orders->have_posts()) {
                $orders->the_post();
                $order = wc_get_order(get_the_ID());
                if (!$order) continue;
                $order_date = $order->get_date_created();
                $origin = $order->get_meta('_wc_order_attribution_source_type');
                $is_admin_origin = (strtolower($origin) === 'admin');
                $is_cancelled = ($order->get_status() === 'cancelled');
                
                // Combine classes based on conditions
                $row_classes = [];
                if ($is_admin_origin) $row_classes[] = 'admin-origin';
                if ($is_cancelled) $row_classes[] = 'cancelled-order';
                $row_class = implode(' ', $row_classes);
            ?>
                <tr class="<?php echo esc_attr($row_class); ?>">
                    <td class="column-checkbox">
                        <input type="checkbox" class="order-checkbox" value="<?php echo esc_attr($order->get_id()); ?>" />
                    </td>
                    <td><?php echo esc_html($order->get_id()); ?></td>
                    <td><?php echo esc_html($order->get_formatted_billing_full_name() ?: 'Guest'); ?></td>
                    <td><?php echo esc_html($order->get_billing_email()); ?></td>
                    <td>
                        <?php
                        $address_parts = array_filter([
                            esc_html($order->get_billing_address_1()),
                            esc_html($order->get_billing_address_2()),
                            esc_html($order->get_billing_city()),
                            esc_html(WC()->countries->get_states($order->get_billing_country())[$order->get_billing_state()] ?? ''),
                            esc_html($order->get_billing_postcode()),
                            esc_html(WC()->countries->countries[$order->get_billing_country()] ?? '')
                        ]);
                        echo implode('<br>', $address_parts);
                        ?>
                    </td>
                    <td>
                        <?php
                        echo esc_html($order_time_format == 'ago' && function_exists('oltew_ago_woo_list_table') 
                            ? oltew_ago_woo_list_table($order_date) 
                            : date('d/M/y h:i A', strtotime($order_date))
                        );
                        ?>
                    </td>
                    <td>
                        <?php
                        foreach ($order->get_items() as $item) {
                            echo esc_html($item->get_name()) . ' x ' . esc_html($item->get_quantity()) . '<br>';
                        }
                        ?>
                    </td>
                    <td style="direction: ltr;"><?php echo esc_html($order->get_billing_phone()); ?></td>
                    <td><?php echo esc_html($order->get_meta('billing_2nd_phone')); ?></td>
                    <td><?php echo esc_html($order->get_customer_note()); ?></td>
                    <td><?php echo wc_price($order->get_shipping_total()); ?></td>
                    <td>
                        <?php echo wc_price($order->get_discount_total()); ?>
                        <?php foreach ($order->get_fees() as $fee) : ?>
                            <div><?php echo esc_html($fee->get_name()); ?>: <?php echo wc_price($fee->get_total()); ?></div>
                        <?php endforeach; ?>
                    </td>
                    <td><?php echo wc_price($order->get_total()); ?></td>
                    <td>
                        <?php echo esc_html($origin ?: 'N/A'); ?>
                    </td>
                    <td><?php echo esc_html(ucfirst($order->get_status())); ?></td>
                    <td>
                        <button class="print-invoice-btn" data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                            Print Invoice
                        </button>
                    </td>
                </tr>
            <?php
            }
            wp_reset_postdata();
            ?>
        </tbody>
    </table>
</div>

<div class="oltew-pagination">
    <?php
    echo paginate_links([
        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999) ?: '/')),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $orders->max_num_pages,
    ]);
    ?>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    const rows = document.querySelectorAll('#dataTable tbody tr');

    checkboxes.forEach((checkbox, index) => {
        checkbox.checked = this.checked;
        const currentClasses = rows[index].className;
        rows[index].className = this.checked 
            ? `${currentClasses} is-selected`.trim()
            : currentClasses.replace('is-selected', '').trim();
    });
});

const orderCheckboxes = document.querySelectorAll('.order-checkbox');
orderCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener('change', function() {
        const row = this.closest('tr');
        const currentClasses = row.className;
        if (this.checked) {
            row.className = `${currentClasses} is-selected`.trim();
        } else {
            row.className = currentClasses.replace('is-selected', '').trim();
        }

        const allChecked = [...orderCheckboxes].every(box => box.checked);
        document.getElementById('select-all').checked = allChecked;
    });
});

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('print-invoice-btn')) {
        const orderId = event.target.getAttribute('data-order-id');
        window.open(`/wp-admin/admin.php?page=wc_order_invoice&order_id=${orderId}`, '_blank');
    }
});
</script>