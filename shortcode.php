<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function oltew_order_list_table_shortcode($atts) {
    $atts = shortcode_atts(array(
        'select_status' => array('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-failed', 'wc-cancelled'),
        'list_per_page' => 10,
        'order_time_format' => 'ago',
        'order_by' => 'desc',
    ), $atts);

    ob_start();
    $settings = $atts;
    include 'woo-table-render.php';
    return ob_get_clean();
}
add_shortcode('woo_order_list_table', 'oltew_order_list_table_shortcode');
