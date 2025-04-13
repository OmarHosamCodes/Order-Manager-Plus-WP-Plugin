/**
 * Order Manager Plus - Invoice JS
 */
(($) => {
    /**
     * Handle print button click
     */
    function handlePrintButton() {
        $(".omp-print-button").on("click", (e) => {
            e.preventDefault();
            window.print();
        });
    }

    /**
     * Handle PDF download button click
     */
    function handlePdfButton() {
        // Only enable if PDF functionality is available
        if (!ompInvoiceData.pdfEnabled) {
            return;
        }

        $(".omp-pdf-button").on("click", function (e) {
            e.preventDefault();

            const $button = $(this);
            const orderId = $button.data("order-id");

            if (!orderId) {
                return;
            }

            // Show loading state
            const originalText = $button.text();
            $button.text(ompInvoiceData.downloadText).prop("disabled", true);

            // Make AJAX request
            $.ajax({
                url: ompInvoiceData.ajaxUrl,
                type: "POST",
                data: {
                    action: "omp_download_invoice_pdf",
                    nonce: ompInvoiceData.nonce,
                    order_id: orderId,
                },
                success: (response) => {
                    if (response.success && response.data && response.data.pdf_data) {
                        // Create blob and download
                        const binary = atob(response.data.pdf_data);
                        const len = binary.length;
                        const buffer = new ArrayBuffer(len);
                        const view = new Uint8Array(buffer);

                        for (let i = 0; i < len; i++) {
                            view[i] = binary.charCodeAt(i);
                        }

                        const blob = new Blob([view], { type: "application/pdf" });
                        const url = URL.createObjectURL(blob);

                        // Create temporary download link
                        const a = document.createElement("a");
                        a.href = url;
                        a.download = response.data.filename;
                        document.body.appendChild(a);
                        a.click();

                        // Clean up
                        setTimeout(() => {
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        }, 100);
                    } else {
                        alert(
                            response.data?.message
                                ? response.data.message
                                : ompInvoiceData.downloadErrorText,
                        );
                    }
                },
                error: () => {
                    alert(ompInvoiceData.downloadErrorText);
                },
                complete: () => {
                    // Reset button
                    $button.text(originalText).prop("disabled", false);
                },
            });
        });
    }

    /**
     * Initialize invoice functionality
     */
    function init() {
        handlePrintButton();
        handlePdfButton();
    }

    // Initialize when document is ready
    $(document).ready(init);
})(jQuery);
