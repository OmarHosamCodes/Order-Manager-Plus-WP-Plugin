<?php
/**
 * Block Editor Integration
 * 
 * Handles integration with the WordPress block editor (Gutenberg)
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Block_Editor Class
 */
class OMP_Block_Editor
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register block
        add_action('init', array($this, 'register_block'));
    }

    /**
     * Register the Gutenberg block
     */
    public function register_block()
    {
        // Register block script
        wp_register_script(
            'omp-order-table-block-editor',
            OMP_PLUGIN_URL . 'assets/js/block-editor.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            OMP_VERSION,
            true
        );

        // Register block style
        wp_register_style(
            'omp-order-table-block-editor-style',
            OMP_PLUGIN_URL . 'assets/css/block-editor.css',
            array(),
            OMP_VERSION
        );

        // Get available order statuses
        $order_statuses = wc_get_order_statuses();
        $status_options = array();

        foreach ($order_statuses as $status => $label) {
            $status_options[] = array(
                'value' => $status,
                'label' => $label,
            );
        }

        // Localize script with data for the block
        wp_localize_script('omp-order-table-block-editor', 'ompBlockData', array(
            'status_options' => $status_options,
            'default_settings' => array(
                'list_per_page' => 10,
                'select_status' => array('wc-completed', 'wc-processing', 'wc-on-hold'),
                'order_time_format' => 'ago',
                'order_by' => 'desc',
            ),
            'plugin_url' => OMP_PLUGIN_URL,
        ));

        // Register the block
        register_block_type('order-manager-plus/order-table', array(
            'editor_script' => 'omp-order-table-block-editor',
            'editor_style' => 'omp-order-table-block-editor-style',
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                'list_per_page' => array(
                    'type' => 'number',
                    'default' => 10,
                ),
                'select_status' => array(
                    'type' => 'array',
                    'default' => array('wc-completed', 'wc-processing', 'wc-on-hold'),
                    'items' => array(
                        'type' => 'string',
                    ),
                ),
                'order_time_format' => array(
                    'type' => 'string',
                    'default' => 'ago',
                ),
                'order_by' => array(
                    'type' => 'string',
                    'default' => 'desc',
                ),
                'className' => array(
                    'type' => 'string',
                ),
            ),
        ));
    }

    /**
     * Render the block
     * 
     * @param array $attributes Block attributes
     * @return string Block output
     */
    public function render_block($attributes)
    {
        // Ensure select_status is an array
        if (isset($attributes['select_status']) && !is_array($attributes['select_status'])) {
            $attributes['select_status'] = array($attributes['select_status']);
        }

        // Create an instance of the table with block attributes
        $table = new OMP_Order_Table($attributes);

        // Render the table
        $output = $table->render();

        // Add wrapper class if provided
        if (!empty($attributes['className'])) {
            $output = '<div class="' . esc_attr($attributes['className']) . '">' . $output . '</div>';
        }

        return $output;
    }
}