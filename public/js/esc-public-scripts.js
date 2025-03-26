jQuery(document).ready(function($) {
    'use strict';

    // --- AI Information Fetch ---
    $('#esc-ai-fetch-trigger').on('click', function() {
        var $button = $(this);
        var $statusDiv = $('#esc-ai-status');
        var $extendedForm = $('#esc-extended-form');
        var $form = $button.closest('form');
        var seedName = $form.find('#esc_seed_name').val();
        var variety = $form.find('#esc_variety_name').val();

        if (!seedName) {
            $statusDiv.html('<div class="esc-error">Please enter a Seed Type first.</div>');
            return;
        }

        // Disable button and show loading state
        $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> ' + (esc_ajax_object.loading_text || 'Searching...'));
        $statusDiv.html('<div class="esc-loading">Searching for seed information...</div>');
        
        // Hide extended form while searching
        $extendedForm.slideUp();

        $.ajax({
            url: esc_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'esc_gemini_search',
                nonce: esc_ajax_object.nonce,
                seed_name: seedName,
                variety: variety
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $statusDiv.html('<div class="esc-success">Information found! Review and edit below.</div>');
                    displayAiResults(response.data);
                    // Show the extended form with a smooth animation
                    $extendedForm.slideDown();
                    // Scroll to the results after a short delay to allow animation
                    setTimeout(function() {
                        $('html, body').animate({
                            scrollTop: $statusDiv.offset().top - 50
                        }, 500);
                    }, 300);
                } else {
                    let errorMsg = response.data.message || esc_ajax_object.gemini_error_text || 'Error finding seed information.';
                    $statusDiv.html('<div class="esc-error">' + errorMsg + '</div>');
                    // Still show the form so user can enter manually
                    $extendedForm.slideDown();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $statusDiv.html('<div class="esc-error">' + (esc_ajax_object.error_text || 'An error occurred during the search.') + '</div>');
                console.error("AJAX Error:", textStatus, errorThrown);
                // Still show the form so user can enter manually
                $extendedForm.slideDown();
            },
            complete: function() {
                // Restore button state
                $button.prop('disabled', false).html('<span class="dashicons dashicons-search"></span> Search');
            }
        });
    });

    // Function to display AI results and pre-fill form
    function displayAiResults(data) {
        if (!data || typeof data !== 'object') {
            console.error('Invalid data received from AI');
            return;
        }

        console.log('Processing AI data:', data);

        // Clear any previous values
        $('#esc-extended-form input:not([type="hidden"]), #esc-extended-form textarea, #esc-extended-form select').each(function() {
            if ($(this).is(':checkbox')) {
                $(this).prop('checked', false);
            } else {
                $(this).val('');
            }
        });

        // Skip these fields when displaying/filling
        const skipFields = ['action', 'nonce', 'suggested_term_ids'];

        // Fill in the form fields with AI data
        Object.entries(data).forEach(([key, value]) => {
            // Special handling for sowing_depth to debug
            if (key === 'sowing_depth') {
                console.log('Found sowing_depth value:', value);
                const $sowingDepth = $('#esc_sowing_depth');
                console.log('Sowing depth field exists:', $sowingDepth.length > 0);
                if ($sowingDepth.length > 0) {
                    $sowingDepth.val(value);
                    console.log('Set sowing depth value to:', value);
                }
            }

            if (value !== null && value !== '' && !skipFields.includes(key)) {
                let $field = $('#esc_' + key);
                console.log(`Processing field ${key}:`, { value, fieldExists: $field.length > 0 });
                
                if ($field.length > 0) {
                    if ($field.is(':checkbox')) {
                        $field.prop('checked', !!value);
                    } else if ($field.is('select[multiple]')) {
                        // Handle multiple select (categories)
                        if (Array.isArray(value)) {
                            $field.val(value);
                        }
                    } else {
                        $field.val(value).trigger('change');
                    }
                }
            }
        });

        // Handle category suggestions
        if (data.suggested_term_ids && Array.isArray(data.suggested_term_ids)) {
            $('#esc_seed_category').val(data.suggested_term_ids);
        }

        // Verify all fields were populated correctly
        Object.entries(data).forEach(([key, value]) => {
            if (!skipFields.includes(key)) {
                const $field = $('#esc_' + key);
                if ($field.length > 0) {
                    const currentValue = $field.val();
                    console.log(`Verification - Field ${key}:`, { 
                        expected: value, 
                        actual: currentValue,
                        matches: currentValue === value 
                    });
                }
            }
        });
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