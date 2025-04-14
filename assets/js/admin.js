/**
 * Order Manager Plus - Admin JS
 */
(($) => {
    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        $(".omp-color-picker").wpColorPicker({
            change: (event, ui) => {
                // Update preview when color changes
                updateThemePreview();
            },
        });
    }

    /**
     * Initialize range sliders
     */
    function initRangeSliders() {
        $(".omp-range-slider").on("input", function () {
            // Update displayed value
            $(this)
                .next(".omp-range-value")
                .text(`${$(this).val()}px`);

            // Update preview
            updateThemePreview();
        });
    }

    /**
     * Initialize font selector
     */
    function initFontSelector() {
        $('select[id^="omp_font"]').on("change", () => {
            updateThemePreview();
        });
    }

    /**
     * Initialize checkbox changes
     */
    function initCheckboxes() {
        $('input[type="checkbox"][id^="omp_"]').on("change", () => {
            updateThemePreview();
        });
    }

    /**
     * Reset theme button
     */
    function initResetButton() {
        $(".omp-reset-theme").on("click", (e) => {
            e.preventDefault();

            if (confirm(ompAdminData.resetConfirmText)) {
                // Reset color pickers to defaults
                $(".omp-color-picker").each(function () {
                    const defaultColor = $(this).data("default-color");
                    $(this).wpColorPicker("color", defaultColor);
                });

                // Reset other fields
                $('select[id^="omp_font"]').val("");
                $("#omp_border_radius").val(4).trigger("input");
                $("#omp_apply_to_frontend").prop("checked", false);

                // Update preview
                updateThemePreview();
            }
        });
    }

    /**
     * Initialize tools buttons
     */
    function initToolButtons() {
        // Clear cache button
        $(".omp-clear-cache").on("click", function () {
            const $button = $(this);
            const originalText = $button.text();

            $button.text(ompAdminData.clearingCacheText).prop("disabled", true);

            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "omp_clear_cache",
                    nonce: ompAdminData.nonce,
                },
                success: (response) => {
                    if (response.success) {
                        alert(response.data.message);
                    } else {
                        alert(response.data.message || ompAdminData.errorText);
                    }
                },
                error: () => {
                    alert(ompAdminData.errorText);
                },
                complete: () => {
                    $button.text(originalText).prop("disabled", false);
                },
            });
        });

        // Reset settings button
        $(".omp-reset-settings").on("click", function () {
            if (confirm(ompAdminData.resetAllConfirmText)) {
                const $button = $(this);
                const originalText = $button.text();

                $button.text(ompAdminData.resettingText).prop("disabled", true);

                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "omp_reset_settings",
                        nonce: ompAdminData.nonce,
                    },
                    success: (response) => {
                        if (response.success) {
                            alert(response.data.message);
                            window.location.reload();
                        } else {
                            alert(response.data.message || ompAdminData.errorText);
                        }
                    },
                    error: () => {
                        alert(ompAdminData.errorText);
                    },
                    complete: () => {
                        $button.text(originalText).prop("disabled", false);
                    },
                });
            }
        });
    }

    /**
     * Update the theme preview with current settings
     */
    function updateThemePreview() {
        // Get current values
        const primaryColor = $("#omp_primary_color").val();
        const secondaryColor = $("#omp_secondary_color").val();
        const successColor = $("#omp_success_color").val();
        const dangerColor = $("#omp_danger_color").val();
        const fontFamily = $("#omp_font_family").val();
        const borderRadius = $("#omp_border_radius").val();

        // Apply to preview elements
        if (primaryColor) {
            $(".omp-preview-button.omp-primary-button").css(
                "background-color",
                primaryColor,
            );
            $(".omp-preview-table thead th").css("background-color", primaryColor);
        }

        if (secondaryColor) {
            $(".omp-preview-button.omp-secondary-button").css(
                "background-color",
                secondaryColor,
            );
            $(".omp-preview-status.omp-preview-processing").css(
                "background-color",
                secondaryColor,
            );
        }

        if (successColor) {
            $(".omp-preview-button.omp-success-button").css(
                "background-color",
                successColor,
            );
            $(".omp-preview-status.omp-preview-completed").css(
                "background-color",
                successColor,
            );
        }

        if (dangerColor) {
            $(".omp-preview-button.omp-danger-button").css(
                "background-color",
                dangerColor,
            );
            $(".omp-preview-status.omp-preview-cancelled").css(
                "background-color",
                dangerColor,
            );
        }

        if (fontFamily) {
            $(".omp-preview-container").css("font-family", fontFamily);
        } else {
            $(".omp-preview-container").css("font-family", "");
        }

        if (borderRadius) {
            $(".omp-preview-button, .omp-preview-status, .omp-preview-table").css(
                "border-radius",
                `${borderRadius}px`,
            );
        }
    }

    /**
     * Initialize all admin functionality
     */
    function init() {
        // Initialize UI components
        initColorPickers();
        initRangeSliders();
        initFontSelector();
        initCheckboxes();
        initResetButton();
        initToolButtons();

        // Initial preview update
        updateThemePreview();
    }

    // Initialize when document is ready
    $(document).ready(init);
})(jQuery);
