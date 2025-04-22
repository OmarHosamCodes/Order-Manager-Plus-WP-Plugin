<?php
/**
 * Admin Menu Class
 * 
 * Handles administration menu and pages
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Admin_Menu Class
 */
class OMP_Admin_Menu
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Register admin pages
        add_action('admin_menu', array($this, 'register_admin_pages'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Enqueue admin scripts and styles
     * 
     * @param string $hook Current admin page
     */
    public function enqueue_admin_assets($hook)
    {
        // Only enqueue on our plugin pages
        if (strpos($hook, 'order_manager_plus') === false) {
            return;
        }

        // Enqueue color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // Enqueue custom admin script
        wp_enqueue_script(
            'omp-admin-script',
            OMP_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            OMP_VERSION,
            true
        );
    }

    /**
     * Register the admin pages
     */
    public function register_admin_pages()
    {
        // Add main menu page
        add_menu_page(
            __('Order Manager Plus', 'order-manager-plus'),
            __('Order Manager', 'order-manager-plus'),
            'manage_woocommerce',
            'order_manager_plus',
            array($this, 'render_dashboard_page'),
            'dashicons-list-view',
            58 // Position after WooCommerce
        );

        // Add settings submenu
        add_submenu_page(
            'order_manager_plus',
            __('Settings', 'order-manager-plus'),
            __('Settings', 'order-manager-plus'),
            'manage_woocommerce',
            'order_manager_plus',
            array($this, 'render_dashboard_page')
        );

        add_submenu_page(
            'order_manager_plus',
            __('Order Invoices', 'order-manager-plus'),
            __('Invoices', 'order-manager-plus'),
            'manage_woocommerce',
            'omp_order_invoices',
            array($this, 'render_invoices_page')
        );

        // Keep a hidden page for individual invoice viewing/printing
        add_submenu_page(
            null, // No parent menu - hidden page
            __('View Invoice', 'order-manager-plus'),
            __('View Invoice', 'order-manager-plus'),
            'manage_woocommerce',
            'omp_order_invoice',
            array($this, 'render_invoice_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        // Register settings for the General tab
        register_setting(
            'omp_options',
            'omp_settings',
            array($this, 'sanitize_settings')
        );

        // Register settings for the Theme tab
        register_setting(
            'omp_theme_options',
            'omp_theme_settings',
            array($this, 'sanitize_theme_settings')
        );

        // Register settings for the Advanced tab
        register_setting(
            'omp_advanced_options',
            'omp_settings'
        );

        // Add sections
        add_settings_section(
            'omp_theme_section',
            __('Theme Customization', 'order-manager-plus'),
            array($this, 'render_theme_section'),
            'omp_theme_page'
        );

        // Add fields
        add_settings_field(
            'omp_primary_color',
            __('Primary Color', 'order-manager-plus'),
            array($this, 'render_color_field'),
            'omp_theme_page',
            'omp_theme_section',
            array(
                'id' => 'primary_color',
                'default' => '#2c3e50'
            )
        );

        add_settings_field(
            'omp_secondary_color',
            __('Secondary Color', 'order-manager-plus'),
            array($this, 'render_color_field'),
            'omp_theme_page',
            'omp_theme_section',
            array(
                'id' => 'secondary_color',
                'default' => '#3498db'
            )
        );

        add_settings_field(
            'omp_success_color',
            __('Success Color', 'order-manager-plus'),
            array($this, 'render_color_field'),
            'omp_theme_page',
            'omp_theme_section',
            array(
                'id' => 'success_color',
                'default' => '#27ae60'
            )
        );

        add_settings_field(
            'omp_danger_color',
            __('Danger Color', 'order-manager-plus'),
            array($this, 'render_color_field'),
            'omp_theme_page',
            'omp_theme_section',
            array(
                'id' => 'danger_color',
                'default' => '#e74c3c'
            )
        );

        add_settings_field(
            'omp_font_family',
            __('Font Family', 'order-manager-plus'),
            array($this, 'render_font_field'),
            'omp_theme_page',
            'omp_theme_section',
            array(
                'id' => 'font_family'
            )
        );

        add_settings_field(
            'omp_border_radius',
            __('Border Radius', 'order-manager-plus'),
            array($this, 'render_range_field'),
            'omp_theme_page',
            'omp_theme_section',
            array(
                'id' => 'border_radius',
                'min' => 0,
                'max' => 20,
                'step' => 1,
                'default' => 4
            )
        );

        add_settings_field(
            'omp_apply_to_frontend',
            __('Apply to Frontend', 'order-manager-plus'),
            array($this, 'render_checkbox_field'),
            'omp_theme_page',
            'omp_theme_section',
            array(
                'id' => 'apply_to_frontend',
                'description' => __('Apply theme settings to frontend tables and elements', 'order-manager-plus')
            )
        );
    }

    /**
     * Sanitize theme settings
     * 
     * @param array $input Settings input
     * @return array Sanitized settings
     */
    public function sanitize_theme_settings($input)
    {
        $sanitized = array();

        // Sanitize color values
        $color_fields = array('primary_color', 'secondary_color', 'success_color', 'danger_color');
        foreach ($color_fields as $field) {
            if (isset($input[$field])) {
                // Validate as hex color
                $sanitized[$field] = sanitize_hex_color($input[$field]);
            }
        }

        // Sanitize font family
        if (isset($input['font_family'])) {
            $sanitized['font_family'] = sanitize_text_field($input['font_family']);
        }

        // Sanitize border radius
        if (isset($input['border_radius'])) {
            $sanitized['border_radius'] = absint($input['border_radius']);
        }

        // Sanitize checkbox
        $sanitized['apply_to_frontend'] = isset($input['apply_to_frontend']) ? 1 : 0;

        return $sanitized;
    }

    /**
     * Render theme section
     */
    public function render_theme_section()
    {
        echo '<p>' . esc_html__('Customize the appearance of Order Manager Plus. These settings will be applied to the admin interface and optionally to the frontend elements.', 'order-manager-plus') . '</p>';
    }

    /**
     * Render color field
     * 
     * @param array $args Field arguments
     */
    public function render_color_field($args)
    {
        $options = get_option('omp_theme_settings', array());
        $id = $args['id'];
        $value = isset($options[$id]) ? $options[$id] : $args['default'];

        echo '<input type="text" class="omp-color-picker" id="omp_' . esc_attr($id) . '" name="omp_theme_settings[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" data-default-color="' . esc_attr($args['default']) . '" />';
    }

    /**
     * Render font field
     * 
     * @param array $args Field arguments
     */
    public function render_font_field($args)
    {
        $options = get_option('omp_theme_settings', array());
        $id = $args['id'];
        $value = isset($options[$id]) ? $options[$id] : '';

        $font_families = array(
            '' => __('Default', 'order-manager-plus'),
            'Arial, sans-serif' => 'Arial',
            'Helvetica, Arial, sans-serif' => 'Helvetica',
            'Georgia, serif' => 'Georgia',
            'Tahoma, Geneva, sans-serif' => 'Tahoma',
            'Trebuchet MS, sans-serif' => 'Trebuchet MS',
            'Verdana, Geneva, sans-serif' => 'Verdana',
            'Times New Roman, serif' => 'Times New Roman',
            'Courier New, monospace' => 'Courier New'
        );

        echo '<select id="omp_' . esc_attr($id) . '" name="omp_theme_settings[' . esc_attr($id) . ']">';
        foreach ($font_families as $font_value => $font_name) {
            echo '<option value="' . esc_attr($font_value) . '" ' . selected($value, $font_value, false) . '>' . esc_html($font_name) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Render range field
     * 
     * @param array $args Field arguments
     */
    public function render_range_field($args)
    {
        $options = get_option('omp_theme_settings', array());
        $id = $args['id'];
        $value = isset($options[$id]) ? $options[$id] : $args['default'];

        echo '<input type="range" id="omp_' . esc_attr($id) . '" name="omp_theme_settings[' . esc_attr($id) . ']" 
            value="' . esc_attr($value) . '" 
            min="' . esc_attr($args['min']) . '" 
            max="' . esc_attr($args['max']) . '" 
            step="' . esc_attr($args['step']) . '" 
            class="omp-range-slider" />';
        echo '<span class="omp-range-value">' . esc_html($value) . 'px</span>';
    }

    /**
     * Render checkbox field
     * 
     * @param array $args Field arguments
     */
    public function render_checkbox_field($args)
    {
        $options = get_option('omp_theme_settings', array());
        $id = $args['id'];
        $checked = isset($options[$id]) ? $options[$id] : 0;

        echo '<label for="omp_' . esc_attr($id) . '">';
        echo '<input type="checkbox" id="omp_' . esc_attr($id) . '" name="omp_theme_settings[' . esc_attr($id) . ']" value="1" ' . checked(1, $checked, false) . ' />';
        if (isset($args['description'])) {
            echo ' ' . esc_html($args['description']);
        }
        echo '</label>';
    }

    /**
     * Render the invoices listing page
     */
    public function render_invoices_page()
    {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            echo '<div class="notice notice-error"><p>' . esc_html__('WooCommerce is required for this feature.', 'order-manager-plus') . '</p></div>';
            return;
        }

        // Get recent orders
        $args = array(
            'limit' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'objects',
        );

        $orders = wc_get_orders($args);

        // Start output
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Order Invoices', 'order-manager-plus') . '</h1>';
        echo '<p>' . esc_html__('View and print invoices for recent orders.', 'order-manager-plus') . '</p>';

        if (!empty($orders)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . esc_html__('Order', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Date', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Customer', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Status', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Total', 'order-manager-plus') . '</th>';
            echo '<th>' . esc_html__('Actions', 'order-manager-plus') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($orders as $order) {
                echo '<tr>';
                echo '<td>' . esc_html($order->get_order_number()) . '</td>';
                echo '<td>' . esc_html($order->get_date_created()->date_i18n(get_option('date_format') . ' ' . get_option('time_format'))) . '</td>';
                echo '<td>' . esc_html($order->get_formatted_billing_full_name()) . '</td>';
                echo '<td><span class="omp-status-' . esc_attr($order->get_status()) . '">' . esc_html(wc_get_order_status_name($order->get_status())) . '</span></td>';
                echo '<td>' . wp_kses_post($order->get_formatted_order_total()) . '</td>';
                echo '<td>';
                echo '<a href="' . esc_url(admin_url('admin.php?page=omp_order_invoice&order_id=' . $order->get_id())) . '" class="button" target="_blank">' . esc_html__('View Invoice', 'order-manager-plus') . '</a>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="notice notice-info"><p>' . esc_html__('No orders found.', 'order-manager-plus') . '</p></div>';
        }

        echo '</div>';
    }

    /**
     * Render the dashboard page
     */
    public function render_dashboard_page()
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Order Manager Plus', 'order-manager-plus') . '</h1>';

        // Tab navigation
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

        echo '<h2 class="nav-tab-wrapper">';
        echo '<a href="?page=order_manager_plus&tab=general" class="nav-tab ' . ($current_tab == 'general' ? 'nav-tab-active' : '') . '">' . esc_html__('General', 'order-manager-plus') . '</a>';
        echo '<a href="?page=order_manager_plus&tab=theme" class="nav-tab ' . ($current_tab == 'theme' ? 'nav-tab-active' : '') . '">' . esc_html__('Theme', 'order-manager-plus') . '</a>';
        echo '<a href="?page=order_manager_plus&tab=advanced" class="nav-tab ' . ($current_tab == 'advanced' ? 'nav-tab-active' : '') . '">' . esc_html__('Advanced', 'order-manager-plus') . '</a>';
        echo '</h2>';

        // Tab content
        switch ($current_tab) {
            case 'theme':
                $this->render_theme_tab();
                break;

            case 'advanced':
                $this->render_advanced_tab();
                break;

            default:
                $this->render_general_tab();
                break;
        }

        echo '</div>';
    }

    /**
     * Render the general settings tab
     */
    /**
     * Sanitize general settings
     * 
     * @param array $input Settings input
     * @return array Sanitized settings
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        // Sanitize numeric values
        if (isset($input['table_per_page'])) {
            $sanitized['table_per_page'] = absint($input['table_per_page']);
        }

        // Sanitize array values
        if (isset($input['default_statuses']) && is_array($input['default_statuses'])) {
            $sanitized['default_statuses'] = array_map('sanitize_text_field', $input['default_statuses']);
        }

        // Sanitize string values
        if (isset($input['date_format'])) {
            $sanitized['date_format'] = sanitize_text_field($input['date_format']);
        }

        if (isset($input['order_sort'])) {
            $sanitized['order_sort'] = sanitize_text_field($input['order_sort']);
        }

        // Sanitize checkboxes
        $checkbox_fields = array('enable_invoice', 'enable_edit', 'enable_export', 'enable_debug');
        foreach ($checkbox_fields as $field) {
            $sanitized[$field] = isset($input[$field]) ? 1 : 0;
        }

        // Sanitize cache time
        if (isset($input['cache_time'])) {
            $sanitized['cache_time'] = absint($input['cache_time']);
        }

        // Sanitize custom CSS
        if (isset($input['custom_css'])) {
            $sanitized['custom_css'] = $input['custom_css']; // Not sanitizing to allow CSS
        }

        return $sanitized;
    }

    private function render_general_tab()
    {
        $options = get_option('omp_settings', array());

        echo '<form method="post" action="options.php">';
        settings_fields('omp_options');
        do_settings_sections('omp_options');

        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Default Rows Per Page', 'order-manager-plus') . '</th>';
        echo '<td>';
        echo '<input type="number" name="omp_settings[table_per_page]" value="' . esc_attr(isset($options['table_per_page']) ? $options['table_per_page'] : 10) . '" min="5" max="999" step="5" />';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Default Order Statuses', 'order-manager-plus') . '</th>';
        echo '<td>';

        $statuses = wc_get_order_statuses();
        $default_statuses = isset($options['default_statuses']) ? $options['default_statuses'] : array('wc-completed', 'wc-processing', 'wc-on-hold');

        foreach ($statuses as $status => $label) {
            echo '<label>';
            echo '<input type="checkbox" name="omp_settings[default_statuses][]" value="' . esc_attr($status) . '" ' . checked(in_array($status, $default_statuses), true, false) . ' />';
            echo ' ' . esc_html($label);
            echo '</label><br>';
        }

        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Date Format', 'order-manager-plus') . '</th>';
        echo '<td>';

        $date_format = isset($options['date_format']) ? $options['date_format'] : 'ago';

        echo '<label>';
        echo '<input type="radio" name="omp_settings[date_format]" value="ago" ' . checked('ago', $date_format, false) . ' />';
        echo ' ' . esc_html__('Time Ago (e.g., "2 days ago")', 'order-manager-plus');
        echo '</label><br>';

        echo '<label>';
        echo '<input type="radio" name="omp_settings[date_format]" value="short" ' . checked('short', $date_format, false) . ' />';
        echo ' ' . esc_html__('Short Date (e.g., "12/Jan/22")', 'order-manager-plus');
        echo '</label><br>';

        echo '<label>';
        echo '<input type="radio" name="omp_settings[date_format]" value="human" ' . checked('human', $date_format, false) . ' />';
        echo ' ' . esc_html__('WordPress Date Format', 'order-manager-plus');
        echo '</label>';

        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Default Order Sort', 'order-manager-plus') . '</th>';
        echo '<td>';

        $order_sort = isset($options['order_sort']) ? $options['order_sort'] : 'desc';

        echo '<label>';
        echo '<input type="radio" name="omp_settings[order_sort]" value="desc" ' . checked('desc', $order_sort, false) . ' />';
        echo ' ' . esc_html__('Newest First', 'order-manager-plus');
        echo '</label><br>';

        echo '<label>';
        echo '<input type="radio" name="omp_settings[order_sort]" value="asc" ' . checked('asc', $order_sort, false) . ' />';
        echo ' ' . esc_html__('Oldest First', 'order-manager-plus');
        echo '</label>';

        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Features', 'order-manager-plus') . '</th>';
        echo '<td>';

        $features = array(
            'enable_invoice' => __('Enable Invoice Generation', 'order-manager-plus'),
            'enable_edit' => __('Enable Order Editing', 'order-manager-plus'),
            'enable_export' => __('Enable CSV Export', 'order-manager-plus')
        );

        foreach ($features as $feature => $label) {
            $checked = isset($options[$feature]) ? $options[$feature] : true;

            echo '<label>';
            echo '<input type="checkbox" name="omp_settings[' . esc_attr($feature) . ']" value="1" ' . checked($checked, true, false) . ' />';
            echo ' ' . esc_html($label);
            echo '</label><br>';
        }

        echo '</td>';
        echo '</tr>';

        echo '</table>';

        submit_button();
        echo '</form>';
    }

    /**
     * Render the theme settings tab
     */
    private function render_theme_tab()
    {
        echo '<form method="post" action="options.php">';
        settings_fields('omp_theme_options');
        do_settings_sections('omp_theme_page');

        echo '<div class="omp-theme-preview">';
        echo '<h3>' . esc_html__('Theme Preview', 'order-manager-plus') . '</h3>';

        // Preview of elements with custom styles
        echo '<div class="omp-preview-container">';

        // Button preview
        echo '<div class="omp-preview-section">';
        echo '<h4>' . esc_html__('Buttons', 'order-manager-plus') . '</h4>';
        echo '<button class="omp-preview-button omp-primary-button">' . esc_html__('Primary Button', 'order-manager-plus') . '</button> ';
        echo '<button class="omp-preview-button omp-secondary-button">' . esc_html__('Secondary Button', 'order-manager-plus') . '</button> ';
        echo '<button class="omp-preview-button omp-success-button">' . esc_html__('Success Button', 'order-manager-plus') . '</button> ';
        echo '<button class="omp-preview-button omp-danger-button">' . esc_html__('Danger Button', 'order-manager-plus') . '</button>';
        echo '</div>';

        // Table preview
        echo '<div class="omp-preview-section">';
        echo '<h4>' . esc_html__('Table', 'order-manager-plus') . '</h4>';
        echo '<table class="omp-preview-table">';
        echo '<thead><tr><th>' . esc_html__('Order', 'order-manager-plus') . '</th><th>' . esc_html__('Date', 'order-manager-plus') . '</th><th>' . esc_html__('Status', 'order-manager-plus') . '</th><th>' . esc_html__('Total', 'order-manager-plus') . '</th></tr></thead>';
        echo '<tbody>';
        echo '<tr><td>1001</td><td>2025-04-10</td><td><span class="omp-preview-status omp-preview-completed">' . esc_html__('Completed', 'order-manager-plus') . '</span></td><td>$45.00</td></tr>';
        echo '<tr><td>1002</td><td>2025-04-11</td><td><span class="omp-preview-status omp-preview-processing">' . esc_html__('Processing', 'order-manager-plus') . '</span></td><td>$32.50</td></tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</div>';

        // Status pill preview
        echo '<div class="omp-preview-section">';
        echo '<h4>' . esc_html__('Status Indicators', 'order-manager-plus') . '</h4>';
        echo '<span class="omp-preview-status omp-preview-completed">' . esc_html__('Completed', 'order-manager-plus') . '</span> ';
        echo '<span class="omp-preview-status omp-preview-processing">' . esc_html__('Processing', 'order-manager-plus') . '</span> ';
        echo '<span class="omp-preview-status omp-preview-on-hold">' . esc_html__('On Hold', 'order-manager-plus') . '</span> ';
        echo '<span class="omp-preview-status omp-preview-cancelled">' . esc_html__('Cancelled', 'order-manager-plus') . '</span>';
        echo '</div>';

        echo '</div>'; // .omp-preview-container

        echo '</div>'; // .omp-theme-preview

        submit_button(__('Save Theme Settings', 'order-manager-plus'));
        echo '<button type="button" class="button button-secondary omp-reset-theme">' . esc_html__('Reset to Defaults', 'order-manager-plus') . '</button>';

        echo '</form>';
    }

    /**
     * Render the advanced settings tab
     */
    private function render_advanced_tab()
    {
        $options = get_option('omp_settings', array());

        echo '<form method="post" action="options.php">';
        settings_fields('omp_advanced_options');
        do_settings_sections('omp_advanced_options');

        echo '<table class="form-table">';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Custom CSS', 'order-manager-plus') . '</th>';
        echo '<td>';
        echo '<textarea name="omp_settings[custom_css]" rows="10" cols="50" class="large-text code">' . esc_textarea(isset($options['custom_css']) ? $options['custom_css'] : '') . '</textarea>';
        echo '<p class="description">' . esc_html__('Add custom CSS rules to further customize the plugin appearance.', 'order-manager-plus') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Debugging', 'order-manager-plus') . '</th>';
        echo '<td>';
        echo '<label>';
        echo '<input type="checkbox" name="omp_settings[enable_debug]" value="1" ' . checked(isset($options['enable_debug']) ? $options['enable_debug'] : false, true, false) . ' />';
        echo ' ' . esc_html__('Enable Debug Mode', 'order-manager-plus');
        echo '</label>';
        echo '<p class="description">' . esc_html__('Show additional debugging information to admins in order tables.', 'order-manager-plus') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Cache Expiration', 'order-manager-plus') . '</th>';
        echo '<td>';
        echo '<input type="number" name="omp_settings[cache_time]" value="' . esc_attr(isset($options['cache_time']) ? $options['cache_time'] : 3600) . '" min="0" step="300" />';
        echo '<p class="description">' . esc_html__('Time in seconds to cache order data. Set to 0 to disable caching.', 'order-manager-plus') . '</p>';
        echo '</td>';
        echo '</tr>';

        echo '<tr>';
        echo '<th scope="row">' . esc_html__('Tools', 'order-manager-plus') . '</th>';
        echo '<td>';
        echo '<button type="button" class="button omp-clear-cache">' . esc_html__('Clear Cache', 'order-manager-plus') . '</button> ';
        echo '<button type="button" class="button omp-reset-settings">' . esc_html__('Reset All Settings', 'order-manager-plus') . '</button>';
        echo '</td>';
        echo '</tr>';

        echo '</table>';

        submit_button(__('Save Advanced Settings', 'order-manager-plus'));
        echo '</form>';
    }

    /**
     * Render the invoice page
     */
    public function render_invoice_page()
    {
        // Include the invoice template
        $invoice_generator = new OMP_Invoice_Generator();
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        echo $invoice_generator->generate_invoice($order_id);
    }
}