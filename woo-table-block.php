<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function oltew_register_woo_table_block() {
    wp_register_script(
        'oltew-woo-table-block',
        plugins_url('block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'block.js')
    );

    register_block_type('oltew/woo-order-list-table', array(
        'editor_script' => 'oltew-woo-table-block',
        'render_callback' => 'oltew_render_order_list_table',
    ));
}

add_action('init', 'oltew_register_woo_table_block');

function oltew_render_order_list_table($attributes) {
    $attributes = shortcode_atts(array(
        'select_status' => array('wc-completed', 'wc-processing', 'wc-on-hold', 'wc-failed', 'wc-cancelled'),
        'list_per_page' => 99,
        'order_time_format' => 'ago',
        'order_by' => 'desc',
    ), $attributes);

    ob_start();
    $settings = $attributes;
    include 'woo-table-render.php';
    return ob_get_clean();
}
