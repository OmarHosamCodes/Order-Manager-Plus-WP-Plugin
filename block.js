wp.blocks.registerBlockType('oltew/woo-order-list-table', {
    title: 'WooCommerce Order List Table',
    icon: 'table-row-after',
    category: 'widgets',
    edit() {
        return wp.element.createElement('p', null, 'WooCommerce Order List Table Block');
    },
    save() {
        return null; // Rendering handled by PHP
    },
});
