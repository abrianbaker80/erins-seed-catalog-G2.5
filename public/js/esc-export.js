/**
 * Seed Catalog Export Functionality
 */
(function($) {
    'use strict';

    // Initialize export functionality
    function initExport() {
        // Handle column selection actions
        $('#esc-select-all-columns').on('click', function(e) {
            e.preventDefault();
            $('.esc-column-option input[type="checkbox"]').prop('checked', true);
        });

        $('#esc-select-none-columns').on('click', function(e) {
            e.preventDefault();
            $('.esc-column-option input[type="checkbox"]').prop('checked', false);
        });

        $('#esc-select-common-columns').on('click', function(e) {
            e.preventDefault();
            // First uncheck all
            $('.esc-column-option input[type="checkbox"]').prop('checked', false);
            
            // Then check common fields
            const commonFields = [
                'seed_name', 'variety_name', 'plant_type', 'growth_habit', 
                'days_to_maturity', 'sunlight', 'categories'
            ];
            
            commonFields.forEach(function(field) {
                $('.esc-column-option input[value="' + field + '"]').prop('checked', true);
            });
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

    // Initialize when document is ready
    $(document).ready(function() {
        initExport();
    });

})(jQuery);
