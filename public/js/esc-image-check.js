/**
 * Image Check Script for Erin's Seed Catalog
 *
 * This script helps diagnose image loading issues by checking if images exist in the WordPress media library.
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Image Check Script loaded');

        // Check for seed cards
        const $seedCards = $('.esc-seed-card');
        if ($seedCards.length) {
            console.log(`Found ${$seedCards.length} seed cards`);

            // Create a container for the debug info
            const $debugContainer = $('<div id="esc-image-debug" style="position: fixed; top: 10px; right: 10px; background: white; border: 1px solid #ccc; padding: 10px; max-width: 400px; max-height: 80vh; overflow: auto; z-index: 9999;"></div>');
            $debugContainer.append('<h3>Image Debug Info</h3>');
            $debugContainer.append('<button id="esc-close-debug" style="position: absolute; top: 5px; right: 5px;">X</button>');
            $debugContainer.append('<div id="esc-image-stats"></div>');
            $debugContainer.append('<ul id="esc-image-list" style="padding-left: 20px;"></ul>');

            // Add to body
            $('body').append($debugContainer);

            // Close button
            $('#esc-close-debug').on('click', function() {
                $('#esc-image-debug').hide();
            });

            // Stats
            let totalImages = 0;
            let loadedImages = 0;
            let failedImages = 0;
            let wpMediaImages = 0;
            let externalImages = 0;

            // Analyze each seed card's image
            $seedCards.each(function(index) {
                const $card = $(this);
                const seedId = $card.data('seed-id');

                // Find image
                let $image;
                let imageUrl = '';

                // Check for enhanced card
                if ($card.hasClass('esc-enhanced-card')) {
                    $image = $card.find('.esc-seed-image-container img.esc-seed-image');
                } else {
                    $image = $card.find('img.esc-seed-image');
                }

                if ($image.length) {
                    totalImages++;
                    imageUrl = $image.attr('src');

                    // Add to list
                    const $listItem = $('<li></li>');
                    $listItem.html(`Seed #${seedId}: <span class="esc-image-status">Checking...</span><br><small>${imageUrl}</small>`);
                    $('#esc-image-list').append($listItem);

                    // Check if it's a WordPress media library image
                    const isWpMedia = imageUrl.indexOf('/wp-content/uploads/') !== -1;
                    const hasLocalIp = imageUrl.indexOf('192.168.1.128') !== -1;

                    if (isWpMedia) {
                        wpMediaImages++;

                        // Add info about local IP
                        if (hasLocalIp) {
                            $listItem.append('<br><small style="color: blue;">Has local network IP</small>');
                        } else {
                            $listItem.append('<br><small style="color: orange;">Missing local network IP</small>');
                        }
                    } else {
                        externalImages++;
                    }

                    // Check if image loads
                    $image.on('load', function() {
                        loadedImages++;
                        $listItem.find('.esc-image-status').text('Loaded').css('color', 'green');
                        updateStats();
                    }).on('error', function() {
                        failedImages++;
                        $listItem.find('.esc-image-status').text('Failed').css('color', 'red');
                        updateStats();

                        // Try to diagnose the issue
                        let errorReason = '';

                        if (!imageUrl) {
                            errorReason = 'Empty URL';
                        } else if (imageUrl.indexOf('http') !== 0) {
                            errorReason = 'Missing protocol';
                        } else if (isWpMedia) {
                            // Check if the file exists on the server
                            errorReason = 'WordPress media file may not exist';
                        } else {
                            errorReason = 'External image may be inaccessible';
                        }

                        $listItem.append(`<br><small style="color: red;">Error: ${errorReason}</small>`);
                    });

                    // Force a reload of the image to trigger load/error events
                    const currentSrc = $image.attr('src');
                    $image.attr('src', '');
                    setTimeout(() => {
                        $image.attr('src', currentSrc);
                    }, 10);
                }
            });

            function updateStats() {
                $('#esc-image-stats').html(`
                    <p>Total Images: ${totalImages}</p>
                    <p>Loaded: ${loadedImages} (${Math.round(loadedImages/totalImages*100)}%)</p>
                    <p>Failed: ${failedImages} (${Math.round(failedImages/totalImages*100)}%)</p>
                    <p>WordPress Media: ${wpMediaImages} (${Math.round(wpMediaImages/totalImages*100)}%)</p>
                    <p>External: ${externalImages} (${Math.round(externalImages/totalImages*100)}%)</p>
                `);
            }

            // Initial stats update
            updateStats();
        }
    });

})(jQuery);
