jQuery(document).ready(function($) {
    'use strict';

    // Confirm before deleting a seed
    $('.esc-admin-table .delete-link').on('click', function(e) {
        e.preventDefault(); // Prevent default link behavior

        var $link = $(this);
        var seedId = $link.data('seed-id');
        var row = $link.closest('tr'); // Get the table row

        if ( !seedId ) {
            console.error('Delete link missing data-seed-id attribute.');
            return;
        }

        // Use localized confirm message
        if (confirm(esc_admin_ajax_object.confirm_delete)) {
            // Add loading indicator?
            row.css('opacity', '0.5');

            // Perform AJAX delete
            $.ajax({
                url: esc_admin_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'esc_delete_seed', // Defined in ESC_Ajax
                    seed_id: seedId,
                    nonce: esc_admin_ajax_object.delete_nonce // Use the correct nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the row from the table
                        row.fadeOut(300, function() {
                            $(this).remove();
                            // Display success message? Maybe via admin notices?
                            // Note: admin notices typically require a page reload to show.
                            // Could add a temporary message above the table.
                        });
                    } else {
                        alert('Error deleting seed: ' + (response.data.message || 'Unknown error'));
                        row.css('opacity', '1'); // Restore opacity on failure
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('AJAX error: ' + textStatus + ' - ' + errorThrown);
                    row.css('opacity', '1'); // Restore opacity on failure
                }
            });
        }
    });

    // Add any other admin-specific JS interactions here
    // e.g., toggling sections, validating input on the manage page edit form, etc.

});