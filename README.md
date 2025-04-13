# Order Manager Plus for WooCommerce

Order Manager Plus is a comprehensive WooCommerce extension that provides enhanced order management capabilities through a customizable order table, invoice generation, and order editing features.

## Features

- **Customizable Order Table**: Display orders in a clean, organized table with filtering and sorting options
- **Invoice Generation**: Create professional-looking invoices for any order with a print-friendly layout
- **Order Editing**: Modify order details, line items, and customer information directly
- **CSV Export**: Export orders to CSV format for reporting and analysis
- **Shortcode Support**: Easily add order tables to any page with configurable shortcodes
- **Gutenberg Block**: Use the included block editor integration for easy insertion
- **Optional Elementor Widget**: Display orders with the Elementor page builder (if installed)
- **Responsive Design**: Works on all device sizes
- **Lightweight & Optimized**: No unnecessary dependencies

## Installation

1. Upload the `order-manager-plus` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Order Manager Plus to configure settings

## Usage

### Shortcode

Use the following shortcode to display the order table on any page:

```
[order_manager_table]
```

You can customize the shortcode with various attributes:

```
[order_manager_table 
  list_per_page="20" 
  select_status="wc-completed,wc-processing" 
  order_time_format="short" 
  order_by="desc"
]
```

### Available Shortcode Attributes

| Attribute | Description | Default | Options |
|-----------|-------------|---------|---------|
| `list_per_page` | Number of orders per page | `10` | Any number (max 100) |
| `select_status` | Order statuses to display | `wc-completed,wc-processing,wc-on-hold,wc-failed,wc-cancelled` | Comma-separated list of statuses |
| `order_time_format` | Date format | `ago` | `ago`, `short`, `human` |
| `order_by` | Sort direction | `desc` | `desc`, `asc` |

### Gutenberg Block

1. Add the "Order Manager Table" block to any page
2. Configure block settings in the sidebar

### Elementor Widget (Optional)

If Elementor is installed:

1. Edit a page with Elementor
2. Search for "Order Manager" in the widgets panel
3. Drag the widget to your page
4. Configure settings in the widget controls

## Customization

The plugin can be customized in several ways:

### CSS Customization

Add custom CSS to your theme to override the plugin styles:

```css
/* Example: Change the table header colors */
#omp-order-table th {
    background-color: #4a6785;
    color: #ffffff;
}

/* Example: Change the button colors */
.omp-button {
    background-color: #3572b0;
}
```

### Filter Hooks

The plugin provides several filter hooks for customization:

| Filter | Description | Parameters |
|--------|-------------|------------|
| `omp_order_query_args` | Modify the query arguments for orders | `$args` (array), `$settings` (array) |
| `omp_export_csv_headers` | Customize CSV export headers | `$headers` (array) |
| `omp_export_csv_row` | Customize CSV export row data | `$row` (array), `$order` (WC_Order) |
| `omp_invoice_template` | Change the invoice template path | `$template` (string) |
| `omp_table_columns` | Modify the table columns | `$columns` (array) |

### Action Hooks

| Action | Description | Parameters |
|--------|-------------|------------|
| `omp_before_order_table` | Runs before rendering the order table | `$settings` (array) |
| `omp_after_order_table` | Runs after rendering the order table | `$settings` (array) |
| `omp_after_order_edit` | Runs after an order has been edited | `$order` (WC_Order), `$posted_data` (array) |

### Template Overrides

You can override the plugin's templates by copying them to your theme:

1. Create a folder named `order-manager-plus` in your theme
2. Copy template files from the plugin's `includes/templates` directory to your theme's `order-manager-plus` folder
3. Customize the templates as needed

### Constants

You can define the following constants in your `wp-config.php` file:

```php
// Disable certain features
define('OMP_DISABLE_INVOICE', true); // Disable invoice feature
define('OMP_DISABLE_EXPORT', true);  // Disable export feature
define('OMP_DISABLE_EDIT', true);    // Disable order editing

// Change default settings
define('OMP_DEFAULT_PER_PAGE', 25);  // Change default orders per page
```

## Frequently Asked Questions

**Q: Can I display orders from a specific customer?**
A: Yes, you can use the `customer_id` attribute in the shortcode:
```
[order_manager_table customer_id="123"]
```

**Q: Is this plugin compatible with custom order statuses?**
A: Yes, the plugin supports all custom order statuses registered with WooCommerce.

**Q: Can I change the columns displayed in the table?**
A: Yes, you can use the `omp_table_columns` filter to add, remove, or modify columns.

**Q: Will this plugin slow down my website?**
A: No, the plugin is designed to be lightweight and optimized. It only loads resources on pages where the order table is displayed.

## Requirements

- WordPress 5.6 or higher
- WooCommerce 5.0 or higher
- PHP 7.3 or higher

## Support

For support requests, please use the WordPress.org support forums or contact us directly.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Order Manager Plus is developed and maintained by [Your Name/Company].