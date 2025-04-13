/**
 * Order Manager Plus - Block Editor
 */

((blocks, editor, components, i18n, element) => {
    const el = element.createElement;
    const __ = i18n.__;
    const InspectorControls = editor.InspectorControls;
    const PanelBody = components.PanelBody;
    const RangeControl = components.RangeControl;
    const SelectControl = components.SelectControl;
    const ToggleControl = components.ToggleControl;
    const CheckboxControl = components.CheckboxControl;

    // Get block data from localized script
    const statusOptions = ompBlockData.status_options || [];
    const defaultSettings = ompBlockData.default_settings || {};
    const pluginUrl = ompBlockData.plugin_url || "";

    /**
     * Register the block
     */
    blocks.registerBlockType("order-manager-plus/order-table", {
        title: __("Order Manager Table", "order-manager-plus"),
        icon: "list-view",
        category: "widgets",
        keywords: [
            __("orders", "order-manager-plus"),
            __("woocommerce", "order-manager-plus"),
            __("table", "order-manager-plus"),
        ],

        attributes: {
            list_per_page: {
                type: "number",
                default: defaultSettings.list_per_page || 10,
            },
            select_status: {
                type: "array",
                default: defaultSettings.select_status || [
                    "wc-completed",
                    "wc-processing",
                    "wc-on-hold",
                ],
            },
            order_time_format: {
                type: "string",
                default: defaultSettings.order_time_format || "ago",
            },
            order_by: {
                type: "string",
                default: defaultSettings.order_by || "desc",
            },
        },

        /**
         * Edit function
         */
        edit: (props) => {
            const attributes = props.attributes;

            // Update function for order statuses
            function updateOrderStatuses(status, checked) {
                let newStatuses = attributes.select_status.slice();

                if (checked) {
                    // Add status if not already present
                    if (newStatuses.indexOf(status) === -1) {
                        newStatuses.push(status);
                    }
                } else {
                    // Remove status
                    newStatuses = newStatuses.filter((item) => item !== status);
                }

                props.setAttributes({ select_status: newStatuses });
            }

            return [
                // Inspector controls (sidebar)
                el(
                    InspectorControls,
                    { key: "inspector" },
                    el(
                        PanelBody,
                        {
                            title: __("Table Settings", "order-manager-plus"),
                            initialOpen: true,
                        },
                        el(RangeControl, {
                            label: __("Orders Per Page", "order-manager-plus"),
                            value: attributes.list_per_page,
                            min: 1,
                            max: 100,
                            onChange: (value) => {
                                props.setAttributes({ list_per_page: value });
                            },
                        }),

                        el(SelectControl, {
                            label: __("Date Format", "order-manager-plus"),
                            value: attributes.order_time_format,
                            options: [
                                {
                                    label: __(
                                        'Time Ago (e.g., "2 days ago")',
                                        "order-manager-plus",
                                    ),
                                    value: "ago",
                                },
                                {
                                    label: __(
                                        'Short Date (e.g., "12/Jan/22")',
                                        "order-manager-plus",
                                    ),
                                    value: "short",
                                },
                                {
                                    label: __("WordPress Date Format", "order-manager-plus"),
                                    value: "human",
                                },
                            ],
                            onChange: (value) => {
                                props.setAttributes({ order_time_format: value });
                            },
                        }),

                        el(SelectControl, {
                            label: __("Order Sort", "order-manager-plus"),
                            value: attributes.order_by,
                            options: [
                                {
                                    label: __("Newest First", "order-manager-plus"),
                                    value: "desc",
                                },
                                {
                                    label: __("Oldest First", "order-manager-plus"),
                                    value: "asc",
                                },
                            ],
                            onChange: (value) => {
                                props.setAttributes({ order_by: value });
                            },
                        }),
                    ),

                    el(
                        PanelBody,
                        {
                            title: __("Order Statuses", "order-manager-plus"),
                            initialOpen: true,
                        },
                        statusOptions.map((option) =>
                            el(CheckboxControl, {
                                label: option.label,
                                checked: attributes.select_status.indexOf(option.value) !== -1,
                                onChange: (checked) => {
                                    updateOrderStatuses(option.value, checked);
                                },
                            }),
                        ),
                    ),
                ),

                // Block preview
                el(
                    "div",
                    { className: props.className },
                    el(
                        "div",
                        { className: "omp-block-preview" },
                        el(
                            "div",
                            { className: "omp-block-icon" },
                            el("img", {
                                src: `${pluginUrl}assets/images/table-icon.svg`,
                                alt: __("Order Table", "order-manager-plus"),
                            }),
                        ),
                        el(
                            "div",
                            { className: "omp-block-title" },
                            el("h3", {}, __("Order Manager Table", "order-manager-plus")),
                        ),
                        el(
                            "div",
                            { className: "omp-block-description" },
                            el(
                                "p",
                                {},
                                __(
                                    "Displays a table of WooCommerce orders based on the settings.",
                                    "order-manager-plus",
                                ),
                            ),
                        ),
                        el(
                            "div",
                            { className: "omp-block-settings" },
                            el(
                                "ul",
                                {},
                                el(
                                    "li",
                                    {},
                                    `${__("Orders Per Page:", "order-manager-plus")} ${attributes.list_per_page}`,
                                ),
                                el(
                                    "li",
                                    {},
                                    `${__("Order Sort:", "order-manager-plus")} ${attributes.order_by === "desc"
                                        ? __("Newest First", "order-manager-plus")
                                        : __("Oldest First", "order-manager-plus")}`,
                                ),
                                el(
                                    "li",
                                    {},
                                    `${__("Date Format:", "order-manager-plus")} ${attributes.order_time_format}`,
                                ),
                                el(
                                    "li",
                                    {},
                                    `${__("Order Statuses:", "order-manager-plus")} ${attributes.select_status.length}`,
                                ),
                            ),
                        ),
                    ),
                ),
            ];
        },

        /**
         * Save function (empty for dynamic blocks)
         */
        save: () => {
            return null; // Dynamic block, rendered by PHP
        },
    });
})(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n,
    window.wp.element,
);
