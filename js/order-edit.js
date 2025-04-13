jQuery(document).ready(function($) {
    // Auto-update totals when quantities change
    $('.order-edit-section input[name^="item_qty"]').on('change', function() {
        var row = $(this).closest('tr');
        var price = parseFloat(row.find('td:eq(2)').text().replace(/[^0-9.-]+/g, ''));
        var qty = parseFloat($(this).val());
        var total = price * qty;
        row.find('td:eq(3)').text(formatMoney(total));
    });

    // Helper function to format money values
    function formatMoney(amount) {
        return amount.toLocaleString('ar-EG', {
            style: 'currency',
            currency: 'EGP'
        });
    }

    // Form submission handling
    $('#edit-order-form').on('submit', function() {
        // You could add validation here
        return confirm('Are you sure you want to save these changes?');
    });
});