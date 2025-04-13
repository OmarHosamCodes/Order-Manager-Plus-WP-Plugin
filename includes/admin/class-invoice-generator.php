<?php
/**
 * Invoice Generator Class
 * 
 * Handles generation of invoices for WooCommerce orders
 * 
 * @package OrderManagerPlus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OMP_Invoice_Generator Class
 */
class OMP_Invoice_Generator
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // Add print functionality
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add PDF download functionality if enabled
        if ($this->is_pdf_enabled()) {
            add_action('wp_ajax_omp_download_invoice_pdf', array($this, 'ajax_download_pdf'));
            add_action('wp_ajax_nopriv_omp_download_invoice_pdf', array($this, 'ajax_unauthorized'));
        }
    }

    /**
     * Check if PDF functionality is enabled
     * 
     * @return bool Whether PDF functionality is enabled
     */
    private function is_pdf_enabled()
    {
        // Get option from settings
        $options = get_option('omp_settings', array());
        $pdf_enabled = isset($options['enable_pdf']) ? $options['enable_pdf'] : false;

        // Allow filtering
        return apply_filters('omp_enable_pdf_invoices', $pdf_enabled);
    }

    /**
     * Enqueue scripts and styles for invoices
     * 
     * @param string $hook Current admin page
     */
    public function enqueue_scripts($hook)
    {
        // Only enqueue on our invoice page or public pages with our shortcode
        global $post;

        $is_invoice_page = ($hook == 'admin_page_omp_order_invoice');
        $has_shortcode = (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'order_manager_table'));

        if (!$is_invoice_page && !$has_shortcode) {
            return;
        }

        // Enqueue invoice styles
        wp_enqueue_style(
            'omp-invoice-styles',
            OMP_PLUGIN_URL . 'assets/css/invoice.css',
            array(),
            OMP_VERSION
        );

        // Enqueue print script
        wp_enqueue_script(
            'omp-invoice-script',
            OMP_PLUGIN_URL . 'assets/js/invoice.js',
            array('jquery'),
            OMP_VERSION,
            true
        );

        // Localize script with data
        wp_localize_script('omp-invoice-script', 'ompInvoiceData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('omp-invoice-nonce'),
            'pdfEnabled' => $this->is_pdf_enabled(),
            'downloadText' => __('Preparing PDF...', 'order-manager-plus'),
            'downloadErrorText' => __('Error generating PDF', 'order-manager-plus'),
        ));
    }

    /**
     * Handle unauthorized access
     */
    public function ajax_unauthorized()
    {
        wp_send_json_error(array(
            'message' => __('You do not have permission to download invoices.', 'order-manager-plus')
        ));
    }

    /**
     * AJAX handler for downloading PDF invoice
     */
    public function ajax_download_pdf()
    {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'omp-invoice-nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'order-manager-plus')
            ));
        }

        // Check if PDF functionality is enabled
        if (!$this->is_pdf_enabled()) {
            wp_send_json_error(array(
                'message' => __('PDF functionality is not enabled.', 'order-manager-plus')
            ));
        }

        // Get order ID
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

        if (!$order_id) {
            wp_send_json_error(array(
                'message' => __('No order ID provided.', 'order-manager-plus')
            ));
        }

        // Get order
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_send_json_error(array(
                'message' => __('Invalid order ID.', 'order-manager-plus')
            ));
        }

        // Check permissions - admin users or customer who owns the order
        if (!current_user_can('manage_woocommerce') && get_current_user_id() != $order->get_customer_id()) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to download this invoice.', 'order-manager-plus')
            ));
        }

        // Generate PDF
        $pdf_data = $this->generate_pdf($order);

        if (!$pdf_data) {
            wp_send_json_error(array(
                'message' => __('Error generating PDF.', 'order-manager-plus')
            ));
        }

        // Return PDF data
        wp_send_json_success(array(
            'pdf_data' => base64_encode($pdf_data),
            'filename' => 'invoice-' . $order->get_order_number() . '.pdf'
        ));
    }

    /**
     * Generate PDF invoice
     * 
     * @param WC_Order $order Order object
     * @return string|bool PDF content or false on failure
     */
    private function generate_pdf($order)
    {
        // Check if TCPDF library is available or try to load it
        if (!class_exists('TCPDF')) {
            // Try to load TCPDF from common locations
            $tcpdf_paths = array(
                WP_PLUGIN_DIR . '/tcpdf/tcpdf.php',
                WP_PLUGIN_DIR . '/woocommerce-pdf-invoices/lib/tcpdf/tcpdf.php',
                WP_PLUGIN_DIR . '/woocommerce-pdf-invoice/lib/tcpdf/tcpdf.php',
                ABSPATH . 'wp-includes/tcpdf/tcpdf.php',
                OMP_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php',
            );

            foreach ($tcpdf_paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }

            // If still not loaded, try to use alternative method
            if (!class_exists('TCPDF')) {
                // Use HTML to PDF conversion via browser (less reliable)
                return $this->generate_html_for_pdf($order);
            }
        }

        // Start output buffering to capture HTML
        ob_start();

        // Include invoice template
        include $this->get_invoice_template();

        // Get the HTML content
        $html = ob_get_clean();

        try {
            // Create new PDF document with sensible defaults
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator(get_bloginfo('name'));
            $pdf->SetAuthor(get_bloginfo('name'));
            $pdf->SetTitle(__('Invoice', 'order-manager-plus') . ' #' . $order->get_order_number());
            $pdf->SetSubject(__('Invoice', 'order-manager-plus') . ' #' . $order->get_order_number());

            // Remove header and footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->SetMargins(15, 15, 15);

            // Add a page
            $pdf->AddPage();

            // Write HTML content
            $pdf->writeHTML($html, true, false, true, false, '');

            // Close and return PDF document
            return $pdf->Output('', 'S');
        } catch (Exception $e) {
            // Log error
            error_log('Error generating PDF invoice: ' . $e->getMessage());

            // Return false to indicate failure
            return false;
        }
    }

    /**
     * Generate HTML for browser-based PDF generation
     * 
     * @param WC_Order $order Order object
     * @return string HTML with PDF conversion JS
     */
    private function generate_html_for_pdf($order)
    {
        // Start output buffering
        ob_start();

        // Include invoice template
        include $this->get_invoice_template();

        // Get the invoice HTML
        $invoice_html = ob_get_clean();

        // Add JS for converting to PDF in browser
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . esc_html__('Invoice', 'order-manager-plus') . ' #' . esc_html($order->get_order_number()) . '</title>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
            <script>
                window.onload = function() {
                    const element = document.getElementById("invoice");
                    const opt = {
                        margin: [15, 15, 15, 15],
                        filename: "invoice-' . esc_js($order->get_order_number()) . '.pdf",
                        image: { type: "jpeg", quality: 0.95 },
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: "mm", format: "a4", orientation: "portrait" }
                    };
                    
                    // Generate PDF
                    html2pdf().set(opt).from(element).save();
                }
            </script>
            <style>
                /* Add print styles */
                @media print {
                    body { margin: 0; padding: 0; }
                }
            </style>
        </head>
        <body>
            <div id="invoice">
                ' . $invoice_html . '
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Get the invoice template path
     * 
     * @return string Template path
     */
    private function get_invoice_template()
    {
        // Default template
        $default_template = OMP_PLUGIN_DIR . 'includes/templates/invoice-template.php';

        // Check for theme override
        $theme_template = locate_template('order-manager-plus/invoice-template.php');

        if ($theme_template) {
            $template = $theme_template;
        } else {
            $template = $default_template;
        }

        // Allow plugins to override template
        return apply_filters('omp_invoice_template', $template);
    }

    /**
     * Generate invoice for a specific order
     * 
     * @param int $order_id Order ID
     * @return string Invoice HTML
     */
    public function generate_invoice($order_id)
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            return '<div class="omp-error">' .
                __('Invalid order ID.', 'order-manager-plus') .
                '</div>';
        }

        // Start output buffering
        ob_start();

        // Include invoice template
        include $this->get_invoice_template();

        // Get the buffer and end buffering
        $output = ob_get_clean();

        return $output;
    }
}