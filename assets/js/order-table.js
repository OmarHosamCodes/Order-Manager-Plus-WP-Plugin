/**
 * Order Manager Plus - Order Table JS
 */
(($) => {
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
     * Handle print invoice button clicks
     */
    function handlePrintInvoice() {
        $(".omp-invoice-button").on("click", function (e) {
            // No need to prevent default as we want the link to work
            // Just track for analytics if needed
            if (typeof ompData.trackEvent === "function") {
                ompData.trackEvent("invoice_view", $(this).data("order-id"));
            }
        });
    }

    /**
     * Initialize table filtering
     */
    function initTableFiltering() {
        // If we have a filter form
        if ($("#omp-filter-form").length) {
            // Handle status filter changes
            $("#omp-status-filter").on("change", () => {
                $("#omp-filter-form").submit();
            });

            // Handle date range filter
            $("#omp-apply-filter").on("click", (e) => {
                e.preventDefault();
                $("#omp-filter-form").submit();
            });

            // Handle reset filters
            $("#omp-reset-filter").on("click", (e) => {
                e.preventDefault();

                // Reset all form elements
                $("#omp-status-filter").val("");
                $("#omp-from-date, #omp-to-date").val("");

                // Submit the form
                $("#omp-filter-form").submit();
            });
        }
    }

    /**
     * Make certain table columns sortable
     */
    function initSortableColumns() {
        // If we have sortable headers
        $(".omp-sortable").on("click", function (e) {
            e.preventDefault();

            const column = $(this).data("sort");
            const currentOrder = $(this).data("order") || "desc";
            const newOrder = currentOrder === "desc" ? "asc" : "desc";

            // Update URL with sort parameters
            const url = new URL(window.location.href);
            url.searchParams.set("orderby", column);
            url.searchParams.set("order", newOrder);

            // Navigate to new URL
            window.location.href = url.toString();
        });
    }

    /**
     * Initialize the table functionality
     */
    function init() {
        initDatepickers();
        handleSelectAll();
        handlePrintInvoice();
        initTableFiltering();
        initSortableColumns();
    }

    // Initialize when document is ready
    $(document).ready(init);
})(jQuery);
