/**
 * Fix for floating labels with pre-filled fields
 */
(function($) {
    'use strict';

    // Run when document is ready
    $(document).ready(function() {
        initFloatingLabels();
    });

    /**
     * Initialize floating labels and fix pre-filled fields
     */
    function initFloatingLabels() {
        // Fix for pre-filled fields
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').each(function() {
            var $input = $(this);
            
            // Check if the field has a value
            if ($input.val() && $input.val().trim() !== '') {
                $input.addClass('has-value');
                
                // Move the label up
                var $label = $input.siblings('label');
                if ($label.length) {
                    $label.addClass('active');
                }
            }
        });

        // Handle input events
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').on('input change focus blur', function() {
            var $input = $(this);
            
            if ($input.val() && $input.val().trim() !== '') {
                $input.addClass('has-value');
            } else {
                $input.removeClass('has-value');
            }
        });
    }

})(jQuery);
