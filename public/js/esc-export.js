/**
 * Seed Catalog Export Functionality
 */
(function($) {
    'use strict';

    // Initialize export functionality
    function initExport() {
        // Update selected count on page load
        updateSelectedCount();

        // Handle column selection actions
        $('#esc-select-all-columns').on('click', function(e) {
            e.preventDefault();
            const visibleCheckboxes = getVisibleCheckboxes();
            visibleCheckboxes.prop('checked', true);
            updateSelectedCount();
        });

        $('#esc-select-none-columns').on('click', function(e) {
            e.preventDefault();
            const visibleCheckboxes = getVisibleCheckboxes();
            visibleCheckboxes.prop('checked', false);
            updateSelectedCount();
        });

        $('#esc-select-common-columns').on('click', function(e) {
            e.preventDefault();
            // First uncheck all visible checkboxes
            const visibleCheckboxes = getVisibleCheckboxes();
            visibleCheckboxes.prop('checked', false);

            // Then check common fields that are visible
            const commonFields = [
                'seed_name', 'variety_name', 'plant_type', 'growth_habit',
                'days_to_maturity', 'sunlight', 'categories'
            ];

            commonFields.forEach(function(field) {
                const checkbox = $('.esc-column-option input[value="' + field + '"]');
                if (checkbox.closest('.esc-column-option').is(':visible')) {
                    checkbox.prop('checked', true);
                }
            });

            updateSelectedCount();
        });

        // Handle category filtering
        $('.esc-column-category').on('click', function() {
            const category = $(this).data('category');

            // Update active state
            $('.esc-column-category').removeClass('active');
            $(this).addClass('active');

            // Show/hide columns based on category
            if (category === 'all') {
                $('.esc-column-option').show();
            } else {
                $('.esc-column-option').hide();
                $('.esc-column-option[data-category="' + category + '"]').show();
            }
        });

        // Handle checkbox changes
        $('.esc-column-option input[type="checkbox"]').on('change', function() {
            updateSelectedCount();
        });

        // Handle form submission
        $('#esc-export-form').on('submit', function(e) {
            e.preventDefault();

            // Validate that at least one column is selected
            if ($('.esc-column-option input[type="checkbox"]:checked').length === 0) {
                showExportStatus('error', esc_export_object.error_no_columns);
                return;
            }

            // Show loading status
            showExportStatus('loading', esc_export_object.loading_text);

            // Get form data
            const formData = $(this).serialize();

            // Create a form and submit it to trigger the download
            const $form = $('<form>', {
                'method': 'POST',
                'action': esc_export_object.export_url,
                'target': '_blank'
            }).appendTo('body');

            // Add nonce
            $('<input>').attr({
                'type': 'hidden',
                'name': 'nonce',
                'value': esc_export_object.nonce
            }).appendTo($form);

            // Add action
            $('<input>').attr({
                'type': 'hidden',
                'name': 'action',
                'value': 'esc_export_catalog'
            }).appendTo($form);

            // Add selected columns
            $('.esc-column-option input[type="checkbox"]:checked').each(function() {
                $('<input>').attr({
                    'type': 'hidden',
                    'name': 'export_columns[]',
                    'value': $(this).val()
                }).appendTo($form);
            });

            // Add category filter if selected
            const categoryFilter = $('#esc-filter-category').val();
            if (categoryFilter) {
                $('<input>').attr({
                    'type': 'hidden',
                    'name': 'category_filter',
                    'value': categoryFilter
                }).appendTo($form);
            }

            // Add export format
            const exportFormat = $('input[name="export_format"]:checked').val();
            $('<input>').attr({
                'type': 'hidden',
                'name': 'export_format',
                'value': exportFormat
            }).appendTo($form);

            // Submit the form to trigger download
            $form.submit();

            // Remove the form
            setTimeout(function() {
                $form.remove();
                showExportStatus('success', esc_export_object.success_text);

                // Hide status after a delay
                setTimeout(function() {
                    $('#esc-export-status').fadeOut();
                }, 3000);
            }, 1000);
        });
    }

    // Show export status message
    function showExportStatus(type, message) {
        const $status = $('#esc-export-status');

        // Clear previous classes and content
        $status.removeClass('esc-status-loading esc-status-error esc-status-success')
               .html('')
               .show();

        // Add appropriate class and message
        switch (type) {
            case 'loading':
                $status.addClass('esc-status-loading')
                       .html('<span class="dashicons dashicons-update esc-spin"></span> ' + message);
                break;
            case 'error':
                $status.addClass('esc-status-error')
                       .html('<span class="dashicons dashicons-warning"></span> ' + message);
                break;
            case 'success':
                $status.addClass('esc-status-success')
                       .html('<span class="dashicons dashicons-yes-alt"></span> ' + message);
                break;
        }
    }

    // Get visible checkboxes based on current category filter
    function getVisibleCheckboxes() {
        return $('.esc-column-option:visible input[type="checkbox"]');
    }

    // Update the selected count display
    function updateSelectedCount() {
        const totalChecked = $('.esc-column-option input[type="checkbox"]:checked').length;
        const totalFields = $('.esc-column-option input[type="checkbox"]').length;
        $('#esc-selected-count').text(totalChecked);

        // Highlight the count if no fields are selected
        if (totalChecked === 0) {
            $('#esc-selected-count').closest('.esc-selected-count').addClass('esc-warning');
        } else {
            $('#esc-selected-count').closest('.esc-selected-count').removeClass('esc-warning');
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initExport();
    });

})(jQuery);
