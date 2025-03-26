jQuery(document).ready(function($) {
    'use strict';

    // --- AI Information Fetch ---
    $('#esc-ai-fetch-trigger').on('click', function() {
        var $button = $(this);
        var $statusDiv = $('#esc-ai-status');
        var $resultDiv = $('#esc-ai-result-display');
        var $form = $button.closest('form');

        var seedName = $form.find('#esc_seed_name').val();
        var variety = $form.find('#esc_variety_name').val();
        var brand = $form.find('#esc_brand').val();
        var skuUpc = $form.find('#esc_sku_upc').val();

        if (!seedName) {
            $statusDiv.text('Please enter a Seed Name first.').css('color', 'red');
            return;
        }

        // Disable button and show loading state
        $button.prop('disabled', true).text(esc_ajax_object.loading_text || 'Fetching...');
        $statusDiv.text('Contacting AI assistant...').css('color', '#555');
        $resultDiv.hide().empty(); // Clear previous results

        $.ajax({
            url: esc_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'esc_gemini_search',
                nonce: esc_ajax_object.nonce,
                seed_name: seedName,
                variety: variety,
                brand: brand,
                sku_upc: skuUpc
            },
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.success) {
                    $statusDiv.text('AI information received. Review below and fill form.').css('color', 'green');
                    displayAiResults(response.data, $form, $resultDiv);
                } else {
                    // Display specific error from Gemini or generic one
                    let errorMsg = esc_ajax_object.gemini_error_text || 'Error fetching AI info:';
                    if (response.data && response.data.message) {
                        errorMsg += ' ' + response.data.message;
                        if(response.data.data) { // Add extra details if available
                             errorMsg += ' (' + response.data.data + ')';
                        }
                    } else {
                        errorMsg += ' Unknown error.';
                    }
                    $statusDiv.text(errorMsg).css('color', 'red');
                    console.error("Gemini Error:", response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $statusDiv.text(esc_ajax_object.error_text || 'An error occurred during the request.').css('color', 'red');
                console.error("AJAX Error:", textStatus, errorThrown);
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text('Fetch AI Info');
            }
        });
    });

    // Function to display AI results and pre-fill form
    function displayAiResults(data, $form, $resultDiv) {
        $resultDiv.empty().append('<h4>Suggested Information (Review & Edit)</h4>');
        let foundData = false;

        if (!data || typeof data !== 'object') {
            $resultDiv.append('<p>Invalid data received from AI.</p>');
            return;
        }

        // Skip these fields in display
        const skipFields = ['action', 'nonce', 'suggested_term_ids'];

        // Display all non-empty values from the data object
        Object.entries(data).forEach(([key, value]) => {
            if (value !== null && value !== '' && !skipFields.includes(key)) {
                let label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                let displayValue = '';

                // Format the display value based on type
                if (typeof value === 'boolean') {
                    displayValue = value ? 'Yes' : 'No';
                } else {
                    displayValue = escapeHtml(String(value));
                }

                $resultDiv.append(
                    '<div class="esc-ai-result-item">' +
                    '<strong>' + label + ':</strong> ' +
                    '<span class="ai-value">' + displayValue + '</span>' +
                    '</div>'
                );
                foundData = true;

                // Pre-fill form field if it exists and is empty
                let $field = $form.find('#esc_' + key);
                if ($field.length > 0) {
                    let currentValue = $field.val();
                    if (!currentValue) {
                        if ($field.is(':checkbox')) {
                            $field.prop('checked', !!value);
                        } else if ($field.is('select')) {
                            if ($field.find('option[value="' + value + '"]').length > 0) {
                                $field.val(value);
                            } else {
                                $field.find('option').filter(function() {
                                    return $(this).text().toLowerCase() === String(value).toLowerCase();
                                }).prop('selected', true);
                            }
                        } else {
                            $field.val(value);
                        }
                    }
                }
            }
        });

        // Handle category suggestions separately
        if (data.esc_seed_category_suggestion) {
            $resultDiv.append(
                '<div class="esc-ai-result-item">' +
                '<strong>Suggested Categories:</strong> ' +
                '<span class="ai-value">' + escapeHtml(data.esc_seed_category_suggestion) + '</span>' +
                '</div>'
            );
            foundData = true;

            // Try to select the suggested categories in the dropdown
            if (data.suggested_term_ids && Array.isArray(data.suggested_term_ids) && data.suggested_term_ids.length > 0) {
                let $categorySelect = $form.find('#esc_seed_category');
                if ($categorySelect.length > 0 && $categorySelect.is('select[multiple]')) {
                    let currentSelection = $categorySelect.val();
                    if (!currentSelection || !currentSelection.length) {
                        $categorySelect.val(data.suggested_term_ids);
                    }
                }
            }
        }

        if (!foundData) {
            $resultDiv.append('<p>No specific details found by AI.</p>');
        }

        $resultDiv.show();
    }

    // Helper to escape HTML for display
    function escapeHtml(text) {
        if (typeof text !== 'string') return String(text);
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // --- Add New Seed Form Submission ---
    $('#esc-add-seed-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        var $messageDiv = $('#esc-form-messages');

        // Clear previous messages and show loading state
        $messageDiv.empty().removeClass('success error').addClass('loading').text(esc_ajax_object.loading_text || 'Saving...').show();
        $submitButton.prop('disabled', true);

        // Serialize form data
        var formData = $form.serialize();

        // Add AJAX action and nonce
        formData += '&action=esc_add_seed&nonce=' + esc_ajax_object.nonce;

        $.ajax({
            url: esc_ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $messageDiv.removeClass('loading').addClass('success').text(response.data.message || esc_ajax_object.form_submit_success || 'Seed added successfully!');
                    // Optionally clear the form or redirect
                    $form[0].reset(); // Reset native form elements
                     $('#esc-ai-result-display').hide().empty(); // Hide AI results
                     $('#esc-ai-status').empty();
                     $('select#esc_seed_category').val(null).trigger('change'); // Clear multi-select if using library like Select2, otherwise just .val(null) might work. Use .val([]) for standard multi-select.


                    // Scroll to top of form to see message?
                    $('html, body').animate({ scrollTop: $form.offset().top - 50 }, 500);

                } else {
                     let errorMsg = response.data.message || esc_ajax_object.form_submit_error || 'Error adding seed.';
                    $messageDiv.removeClass('loading').addClass('error').text(errorMsg);
                     console.error("Add Seed Error:", response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                 $messageDiv.removeClass('loading').addClass('error').text(esc_ajax_object.error_text || 'An error occurred.');
                 console.error("AJAX Error:", textStatus, errorThrown);
            },
            complete: function() {
                $submitButton.prop('disabled', false);
                // Keep message displayed
            }
        });
    });


    // --- Catalog View Filtering/Searching/Pagination ---
    var $catalogContainer = $('#esc-catalog-view-container');
    if ($catalogContainer.length > 0) {
        var $resultsContainer = $('#esc-catalog-results');
        var $searchForm = $('#esc-search-form'); // Could be separate or part of the view container

        // Function to load seeds via AJAX
        function loadSeeds(page = 1, searchData = null) {
            $resultsContainer.html('<div class="esc-loading">' + (esc_ajax_object.loading_text || 'Loading...') + '</div>');

            let ajaxData = {
                action: 'esc_filter_seeds',
                nonce: esc_ajax_object.nonce,
                paged: page
            };

            // Get search/filter values if form exists
            if ($searchForm.length > 0) {
                 ajaxData.search = $searchForm.find('#esc-search-input').val() || '';
                 ajaxData.category = $searchForm.find('#esc-filter-category').val() || '';
                 // Add other filters like sorting here if implemented
            } else if (searchData) {
                // Use passed data if no form (e.g., initial load from shortcode atts)
                ajaxData.search = searchData.search || '';
                ajaxData.category = searchData.category || '';
            }


            $.ajax({
                url: esc_ajax_object.ajax_url,
                type: 'POST',
                data: ajaxData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $resultsContainer.html(response.data.html);
                        // Update browser history? (More advanced)
                        // updateURL(page, ajaxData.search, ajaxData.category);
                    } else {
                        $resultsContainer.html('<p class="esc-no-results error">' + (response.data.message || 'Error loading seeds.') + '</p>');
                        console.error("Filter Seeds Error:", response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $resultsContainer.html('<p class="esc-no-results error">' + (esc_ajax_object.error_text || 'An error occurred.') + '</p>');
                     console.error("AJAX Error:", textStatus, errorThrown);
                }
            });
        }

        // Handle search form submission
        if ($searchForm.length > 0) {
            $searchForm.on('submit', function(e) {
                e.preventDefault();
                loadSeeds(1); // Load first page with new filters
            });
             // Optional: Handle category dropdown change immediately
             $searchForm.find('#esc-filter-category').on('change', function() {
                 loadSeeds(1);
             });
        }


        // Handle AJAX pagination clicks
        // Need event delegation as pagination links are loaded dynamically
        $resultsContainer.on('click', '.esc-pagination a.page-numbers', function(e) {
            e.preventDefault();

            var $link = $(this);
            var pageUrl = $link.attr('href');

            // Extract page number from URL (robustness depends on paginate_links format)
            // This simple regex works if format is '?paged=X' or '#X'
             var pageNumMatch = pageUrl.match(/paged=(\d+)|#(\d+)/);
             var pageNum = 1;
             if (pageNumMatch) {
                 // Get the first non-null captured group
                 pageNum = pageNumMatch[1] || pageNumMatch[2] || 1;
             }


            loadSeeds(parseInt(pageNum, 10));
        });

        // Initial load if the results div is present
        // loadSeeds(); // Might already be loaded server-side via shortcode, or trigger if needed
    }

    // --- Category List Filtering ---
    // If clicking a category link should filter the main catalog view via AJAX:
    var $categoryList = $('#esc-categories-container');
    if ($categoryList.length > 0 && $catalogContainer.length > 0) { // Check if both exist on the same page
        $categoryList.on('click', 'a', function(e){
             e.preventDefault();
             var $link = $(this);
             var categoryUrl = $link.attr('href');
             // Extract term ID or slug from URL (depends on permalink structure)
             // Example: Assuming URL is like /seed-category/vegetables/ or ?esc_seed_category=vegetables or term_id
             // This needs refinement based on actual URL structure.
             var termSlugOrId = categoryUrl.split('/').filter(Boolean).pop(); // Basic guess

             // Find term ID if we only have slug (requires an extra AJAX call or preloaded data)
             // For simplicity, assume the filter dropdown uses term_id and update it.
             var termId = $link.data('term-id'); // Add data-term-id to links in the PHP view

             if(termId && $searchForm.length > 0) {
                 $searchForm.find('#esc-filter-category').val(termId);
                 loadSeeds(1); // Trigger filter

                 // Scroll to catalog view?
                  $('html, body').animate({ scrollTop: $catalogContainer.offset().top - 50 }, 500);
             } else {
                // Fallback or handle differently if term ID isn't available or search form doesn't exist
                 window.location.href = categoryUrl; // Default browser navigation
             }
        });
    }


}); // End jQuery ready