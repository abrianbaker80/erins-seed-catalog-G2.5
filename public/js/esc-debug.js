/**
 * Debug script for Enhanced Seed Catalog
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Debug script loaded');

        // Check if enhanced catalog exists
        if ($('.esc-enhanced-catalog').length) {
            console.log('Enhanced catalog found on page');
        } else {
            console.log('Enhanced catalog NOT found on page');
        }

        // Check if seed cards exist
        if ($('.esc-seed-card').length) {
            console.log('Seed cards found: ' + $('.esc-seed-card').length);

            // Log seed IDs and image information
            $('.esc-seed-card').each(function(index) {
                const $card = $(this);
                const seedId = $card.data('seed-id');
                const $imageContainer = $card.find('.esc-seed-image-container');
                const $image = $imageContainer.find('img.esc-seed-image');

                console.log(`Seed #${index + 1} (ID: ${seedId}):`);

                if ($image.length) {
                    const src = $image.attr('src');
                    console.log(`- Has image: Yes`);
                    console.log(`- Image src: ${src}`);

                    // Check if image is loading correctly
                    $image.on('load', function() {
                        console.log(`- Image #${index + 1} loaded successfully`);
                    }).on('error', function() {
                        console.error(`- Image #${index + 1} failed to load`);

                        // Add error class to help identify in UI
                        $imageContainer.addClass('esc-image-error');
                    });
                } else {
                    console.log(`- Has image: No`);
                    const $noImage = $imageContainer.find('.esc-no-image');
                    if ($noImage.length) {
                        console.log(`- Using placeholder`);
                    } else {
                        console.error(`- No image and no placeholder found`);
                    }
                }
            });
        } else {
            console.log('No seed cards found');
        }

        // Check if modal exists
        if ($('#esc-seed-detail-modal').length) {
            console.log('Modal found');
        } else {
            console.log('Modal NOT found');
        }

        // Add test click handler
        $(document).on('click', '.esc-seed-card', function() {
            console.log('Card clicked: ' + $(this).data('seed-id'));
        });
    });

})(jQuery);
