/**
 * Order Manager Plus - Order Export JS
 */
(($) => {
    /**
     * Handle CSV export functionality
     */
    function handleExport() {
        $("#omp-export-btn").on("click", function () {
            const $button = $(this);
            const orderIds = [];

            // Get selected order IDs
            $(".omp-order-checkbox:checked").each(function () {
                orderIds.push($(this).val());
            });

            // Get filter values
            const status = $("#omp-export-status").val();
            const fromDate = $("#omp-export-from").val();
            const toDate = $("#omp-export-to").val();

            // If no orders selected and no filters set, show message
            if (orderIds.length === 0 && status === "any" && !fromDate && !toDate) {
                alert(
                    ompData.i18n.selectOrders ||
                    "Please select at least one order or set some filters.",
                );
                return;
            }

            // Show loading state
            $button.addClass("omp-loading").prop("disabled", true);
            $button.text(ompData.i18n.exporting || "Exporting...");

            // Make AJAX request
            $.ajax({
                url: ompData.ajaxUrl,
                type: "POST",
                data: {
                    action: "omp_export_orders",
                    nonce: ompData.nonce,
                    order_ids: orderIds,
                    status: status,
                    from_date: fromDate,
                    to_date: toDate,
                },
                success: (response) => {
                    if (response.success) {
                        // Create download link
                        const blob = new Blob([response.data.csv], { type: "text/csv" });
                        const link = document.createElement("a");
                        link.href = window.URL.createObjectURL(blob);
                        link.download = response.data.filename;
                        link.style.display = "none";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert(
                            response.data.message ||
                            ompData.i18n.exportError ||
                            "Error exporting orders.",
                        );
                    }
                },
                error: () => {
                    alert(ompData.i18n.exportError || "Error exporting orders.");
                },
                complete: () => {
                    // Reset button state
                    $button.removeClass("omp-loading").prop("disabled", false);
                    $button.text(ompData.i18n.exportSelected || "Export Selected");
                },
            });
        });
    }

    /**
     * Initialize datepickers
     */
    function initDatepickers() {
        if ($.fn.datepicker) {
            $(".omp-datepicker").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
            });
        }
    }

    /**
     * Handle select all functionality
     */
    function handleSelectAll() {
        $("#omp-select-all").on("change", function () {
            const isChecked = $(this).prop("checked");
            $(".omp-order-checkbox").prop("checked", isChecked);

            if (isChecked) {
                $(".omp-order-checkbox").closest("tr").addClass("omp-selected");
            } else {
                $(".omp-order-checkbox").closest("tr").removeClass("omp-selected");
            }
        });

        $(".omp-order-checkbox").on("change", function () {
            const $row = $(this).closest("tr");

            if ($(this).prop("checked")) {
                $row.addClass("omp-selected");
            } else {
                $row.removeClass("omp-selected");
                $("#omp-select-all").prop("checked", false);
            }

            // Update select all checkbox if all are checked
            const allChecked =
                $(".omp-order-checkbox:checked").length ===
                $(".omp-order-checkbox").length;
            $("#omp-select-all").prop("checked", allChecked);
        });
    }

    /**
     * Initialize the export functionality
     */
    function init() {
        handleExport();
        initDatepickers();
        handleSelectAll();
    }

    // Initialize when document is ready
    $(document).ready(init);
})(jQuery);
