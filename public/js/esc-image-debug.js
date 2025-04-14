/**
 * Image Debug Script for Erin's Seed Catalog
 *
 * This script helps diagnose image loading issues in the seed catalog.
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Image Debug Script loaded');

        // Check for seed cards
        const $seedCards = $('.esc-seed-card');
        if ($seedCards.length) {
            console.log(`Found ${$seedCards.length} seed cards`);

            // Analyze each seed card's image
            $seedCards.each(function(index) {
                const $card = $(this);
                const seedId = $card.data('seed-id');
                console.log(`\n--- Analyzing Seed Card #${index + 1} (ID: ${seedId}) ---`);

                // Check for image container in enhanced cards
                const $imageContainer = $card.find('.esc-seed-image-container');
                if ($imageContainer.length) {
                    console.log('Image container found');

                    // Check for image
                    const $image = $imageContainer.find('img.esc-seed-image');
                    if ($image.length) {
                        const src = $image.attr('src');
                        console.log(`Image found with src: ${src}`);

                        // Check if image is a WordPress media library URL
                        if (src.indexOf('/wp-content/uploads/') !== -1) {
                            console.log('This is a WordPress media library image');

                            // Check if it has the local network IP
                            if (src.indexOf('192.168.1.128') !== -1) {
                                console.log('Image URL includes local network IP');
                            } else {
                                console.log('Image URL is missing local network IP');
                            }
                        } else {
                            console.log('This is an external image');
                        }

                        // Monitor image loading
                        $image.on('load', function() {
                            console.log(`Image for seed ID ${seedId} loaded successfully`);
                            $imageContainer.addClass('esc-image-loaded');
                        }).on('error', function() {
                            console.error(`Image for seed ID ${seedId} failed to load: ${src}`);
                            $imageContainer.addClass('esc-image-error');

                            // Try to diagnose the issue
                            if (src.indexOf('http') !== 0) {
                                console.error('Image URL does not start with http or https');
                            }

                            if (src.indexOf('//') === 0) {
                                console.error('Image URL is protocol-relative (starts with //)');
                            }

                            // Check if image exists using fetch API
                            fetch(src, { method: 'HEAD' })
                                .then(response => {
                                    if (!response.ok) {
                                        console.error(`Image returned HTTP status: ${response.status}`);
                                    } else {
                                        console.log(`Image exists on server (HTTP ${response.status})`);
                                        console.error('Image may be blocked by CORS policy or other security restriction');
                                    }
                                })
                                .catch(error => {
                                    console.error(`Network error checking image: ${error.message}`);
                                });
                        });

                        // Force a reload of the image to trigger load/error events
                        const currentSrc = $image.attr('src');
                        $image.attr('src', '');
                        setTimeout(() => {
                            $image.attr('src', currentSrc);
                        }, 10);
                    } else {
                        console.log('No image found in container');

                        // Check for no-image placeholder
                        const $noImage = $imageContainer.find('.esc-no-image');
                        if ($noImage.length) {
                            console.log('Using no-image placeholder');
                        } else {
                            console.error('No image and no placeholder found');
                        }
                    }
                } else {
                    // Check for direct image in standard cards
                    const $directImage = $card.find('img.esc-seed-image');
                    if ($directImage.length) {
                        const src = $directImage.attr('src');
                        console.log(`Direct image found with src: ${src}`);

                        // Monitor image loading
                        $directImage.on('load', function() {
                            console.log(`Image for seed ID ${seedId} loaded successfully`);
                        }).on('error', function() {
                            console.error(`Image for seed ID ${seedId} failed to load: ${src}`);

                            // Try to diagnose the issue
                            if (src.indexOf('http') !== 0) {
                                console.error('Image URL does not start with http or https');
                            }

                            if (src.indexOf('//') === 0) {
                                console.error('Image URL is protocol-relative (starts with //)');
                            }
                        });

                        // Force a reload of the image to trigger load/error events
                        const currentSrc = $directImage.attr('src');
                        $directImage.attr('src', '');
                        setTimeout(() => {
                            $directImage.attr('src', currentSrc);
                        }, 10);
                    } else {
                        console.log('No image found in card');
                    }
                }
            });
        } else {
            console.log('No seed cards found on page');
        }
    });

})(jQuery);
