/**
 * Image URL Test Script for Erin's Seed Catalog
 * 
 * This script helps diagnose image URL issues by displaying the actual URLs stored in the database.
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('Image URL Test Script loaded');
        
        // Create a button to trigger the test
        const $button = $('<button id="esc-test-image-urls" style="position: fixed; top: 100px; right: 20px; z-index: 9999; padding: 10px; background: #0073aa; color: white; border: none; border-radius: 4px;">Test Image URLs</button>');
        $('body').append($button);
        
        $button.on('click', function() {
            testImageUrls();
        });
        
        function testImageUrls() {
            // Create a container for the results
            let $container = $('#esc-image-url-results');
            if ($container.length === 0) {
                $container = $('<div id="esc-image-url-results" style="position: fixed; top: 150px; right: 20px; z-index: 9999; width: 400px; max-height: 70vh; overflow: auto; background: white; border: 1px solid #ccc; padding: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.2);"></div>');
                $container.append('<h3>Image URL Test Results</h3>');
                $container.append('<button id="esc-close-results" style="position: absolute; top: 10px; right: 10px; background: none; border: none;">X</button>');
                $container.append('<div id="esc-url-results-content"></div>');
                $('body').append($container);
                
                $('#esc-close-results').on('click', function() {
                    $container.hide();
                });
            } else {
                $container.show();
                $('#esc-url-results-content').empty();
            }
            
            // Show loading message
            $('#esc-url-results-content').html('<p>Loading seed data...</p>');
            
            // Make AJAX request to get seed data
            $.ajax({
                url: esc_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'esc_test_image_urls',
                    nonce: esc_ajax_object.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayResults(response.data);
                    } else {
                        $('#esc-url-results-content').html('<p>Error: ' + (response.data.message || 'Unknown error') + '</p>');
                    }
                },
                error: function() {
                    $('#esc-url-results-content').html('<p>Error communicating with the server.</p>');
                }
            });
        }
        
        function displayResults(data) {
            const $content = $('#esc-url-results-content');
            $content.empty();
            
            if (data.seeds.length === 0) {
                $content.html('<p>No seeds found with images.</p>');
                return;
            }
            
            // Add summary
            $content.append('<p><strong>Total seeds with images:</strong> ' + data.seeds.length + '</p>');
            
            // Create table for results
            const $table = $('<table style="width: 100%; border-collapse: collapse;"></table>');
            $table.append('<thead><tr><th style="text-align: left; padding: 5px; border-bottom: 1px solid #ccc;">ID</th><th style="text-align: left; padding: 5px; border-bottom: 1px solid #ccc;">Name</th><th style="text-align: left; padding: 5px; border-bottom: 1px solid #ccc;">Image URL</th></tr></thead>');
            const $tbody = $('<tbody></tbody>');
            
            data.seeds.forEach(function(seed) {
                const $row = $('<tr></tr>');
                $row.append('<td style="padding: 5px; border-bottom: 1px solid #eee;">' + seed.id + '</td>');
                $row.append('<td style="padding: 5px; border-bottom: 1px solid #eee;">' + seed.name + '</td>');
                
                // Create a cell with the URL and a test button
                const $urlCell = $('<td style="padding: 5px; border-bottom: 1px solid #eee;"></td>');
                $urlCell.append('<div style="word-break: break-all; margin-bottom: 5px;">' + seed.image_url + '</div>');
                
                // Add a button to test the image
                const $testButton = $('<button class="esc-test-image-button" style="font-size: 12px; padding: 2px 5px;">Test Image</button>');
                $testButton.data('url', seed.image_url);
                $urlCell.append($testButton);
                
                $row.append($urlCell);
                $tbody.append($row);
            });
            
            $table.append($tbody);
            $content.append($table);
            
            // Add event listener for test buttons
            $('.esc-test-image-button').on('click', function() {
                const url = $(this).data('url');
                testImage(url, $(this));
            });
        }
        
        function testImage(url, $button) {
            // Create a test image
            const img = new Image();
            
            // Change button to loading state
            $button.text('Loading...').prop('disabled', true);
            
            // Set up event handlers
            img.onload = function() {
                $button.text('Success!').css('background-color', '#4CAF50').css('color', 'white');
                
                // Show the image in a popup
                showImagePopup(url);
            };
            
            img.onerror = function() {
                $button.text('Failed!').css('background-color', '#F44336').css('color', 'white');
                
                // Try with modified URL
                tryModifiedUrl(url, $button);
            };
            
            // Set the source to start loading
            img.src = url;
        }
        
        function tryModifiedUrl(url, $button) {
            // Try with the local network URL
            let modifiedUrl = url;
            
            // If it's a WordPress media URL without the domain
            if (url.indexOf('/wp-content/uploads/') === 0) {
                modifiedUrl = 'http://192.168.1.128' + url;
                
                // Create a test image
                const img = new Image();
                
                // Set up event handlers
                img.onload = function() {
                    $button.after('<div style="color: #4CAF50; margin-top: 5px;">Fixed URL works: ' + modifiedUrl + '</div>');
                    
                    // Show the image in a popup
                    showImagePopup(modifiedUrl);
                };
                
                img.onerror = function() {
                    $button.after('<div style="color: #F44336; margin-top: 5px;">Fixed URL also failed: ' + modifiedUrl + '</div>');
                };
                
                // Set the source to start loading
                img.src = modifiedUrl;
            }
        }
        
        function showImagePopup(url) {
            // Remove any existing popup
            $('#esc-image-popup').remove();
            
            // Create popup
            const $popup = $('<div id="esc-image-popup" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 10000; display: flex; align-items: center; justify-content: center;"></div>');
            
            // Add image container
            const $imageContainer = $('<div style="position: relative; max-width: 90%; max-height: 90%;"></div>');
            
            // Add close button
            const $closeButton = $('<button style="position: absolute; top: -30px; right: 0; background: none; border: none; color: white; font-size: 24px;">×</button>');
            $closeButton.on('click', function() {
                $popup.remove();
            });
            
            // Add image
            const $image = $('<img src="' + url + '" style="max-width: 100%; max-height: 80vh; display: block;">');
            
            // Add URL text
            const $urlText = $('<div style="color: white; margin-top: 10px; word-break: break-all;">' + url + '</div>');
            
            // Assemble popup
            $imageContainer.append($closeButton);
            $imageContainer.append($image);
            $imageContainer.append($urlText);
            $popup.append($imageContainer);
            
            // Add to body
            $('body').append($popup);
            
            // Close on click outside image
            $popup.on('click', function(e) {
                if (e.target === this) {
                    $popup.remove();
                }
            });
        }
    });

})(jQuery);
