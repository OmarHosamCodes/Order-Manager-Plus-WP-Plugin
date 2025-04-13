/**
 * Order Manager Plus - Order Edit JS
 */
(($) => {
    /**
     * Format money value with the store's currency format
     *
     * @param {number} amount - The amount to format
     * @return {string} Formatted price
     */
    function formatMoney(amount) {
        // Gets WooCommerce currency settings from localized data
        const currencySymbol = ompEditData.currencySymbol || "$";
        const currencyPosition = ompEditData.currencyPosition || "left";
        const decimalSeparator = ompEditData.decimalSeparator || ".";
        const thousandSeparator = ompEditData.thousandSeparator || ",";
        const decimals = ompEditData.decimals || 2;

        // Format the number
        let formattedAmount = amount.toFixed(decimals);

        // Replace decimal separator
        formattedAmount = formattedAmount.replace(".", decimalSeparator);

        // Add thousand separators
        formattedAmount = formattedAmount.replace(
            /\B(?=(\d{3})+(?!\d))/g,
            thousandSeparator,
        );

        // Add currency symbol based on position
        if (currencyPosition === "left") {
            return currencySymbol + formattedAmount;
        } if (currencyPosition === "right") {
            return formattedAmount + currencySymbol;
        } if (currencyPosition === "left_space") {
            return `${currencySymbol} ${formattedAmount}`;
        } if (currencyPosition === "right_space") {
            return `${formattedAmount} ${currencySymbol}`;
        }

        return currencySymbol + formattedAmount;
    }

    /**
     * Update line item totals when quantity changes
     */
    function updateLineItems() {
        $(".omp-order-items tr").each(function () {
            const $row = $(this);
            const $qtyInput = $row.find(".omp-quantity-input");

            if ($qtyInput.length) {
                const qty = Number.parseInt($qtyInput.val(), 10);
                const price = Number.parseFloat($qtyInput.data("price"));
                const total = price * qty;

                $row.find(".item-total").text(formatMoney(total));

                // Highlight the row temporarily
                $row.addClass("omp-highlight");
                setTimeout(() => {
                    $row.removeClass("omp-highlight");
                }, 1500);
            }
        });
    }

    /**
     * Confirm before saving changes
     */
    function confirmBeforeSave() {
        $("#omp-edit-order-form").on("submit", () =>
            confirm(
                ompEditData.confirmMessage ||
                "Are you sure you want to save these changes?",
            ),
        );
    }

    /**
     * Initialize the order edit page
     */
    function init() {
        // Store original price data in data attribute
        $(".omp-order-items tr").each(function () {
            const $row = $(this);
            const $qtyInput = $row.find(".omp-quantity-input");

            if ($qtyInput.length) {
                const priceText = $row.find("td:nth-child(3)").text();
                const price = Number.parseFloat(
                    priceText.replace(/[^\d.,]/g, "").replace(",", "."),
                );

                $qtyInput.data("price", price);
            }
        });

        // Listen for quantity changes
        $(".omp-quantity-input").on("change", () => {
            updateLineItems();
        });

        // Confirm before saving
        confirmBeforeSave();
    }

    // Initialize when document is ready
    $(document).ready(init);
})(jQuery);
