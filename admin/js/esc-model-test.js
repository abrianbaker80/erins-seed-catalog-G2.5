/**
 * JavaScript for testing Gemini models
 */
(function($) {
    'use strict';

    // Initialize the model test functionality
    function initModelTest() {
        // Add click handler for the test button
        $('#esc-test-model').on('click', function() {
            testSelectedModel();
        });
    }

    // Test the currently selected model
    function testSelectedModel() {
        // Get the selected model
        const selectedModel = $('#' + esc_model_test.model_option_name).val();
        
        // Get the API key
        const apiKey = $('#' + esc_model_test.api_key_option_name).val();
        
        // Check if API key is provided
        if (!apiKey) {
            showTestResults('error', esc_model_test.error_no_api_key, '');
            return;
        }
        
        // Show loading state
        showTestResults('loading', esc_model_test.loading_text, '');
        
        // Make the AJAX request
        $.ajax({
            url: esc_model_test.ajax_url,
            type: 'POST',
            data: {
                action: 'esc_test_model',
                nonce: esc_model_test.nonce,
                model: selectedModel,
                api_key: apiKey
            },
            success: function(response) {
                if (response.success) {
                    // Show success message with response data
                    showTestResults('success', esc_model_test.success_text, response.data);
                    
                    // Update usage statistics if available
                    if (response.data.usage) {
                        updateUsageStatistics(response.data.usage);
                    }
                    
                    // Update model capabilities if available
                    if (response.data.capabilities) {
                        updateModelCapabilities(response.data.capabilities);
                    }
                } else {
                    // Show error message
                    showTestResults('error', response.data.message || esc_model_test.error_text, response.data);
                }
            },
            error: function(xhr, status, error) {
                // Show error message
                showTestResults('error', esc_model_test.error_text + ' ' + error, xhr.responseText);
            }
        });
    }
    
    // Show test results in the results container
    function showTestResults(type, message, data) {
        const $resultsContainer = $('#esc-model-test-results');
        
        // Clear previous results
        $resultsContainer.empty();
        
        // Remove previous classes
        $resultsContainer.removeClass('success error loading');
        
        // Add appropriate class
        $resultsContainer.addClass(type);
        
        // Show the container
        $resultsContainer.show();
        
        // Add header based on type
        let header = '';
        if (type === 'success') {
            header = '<h4><span class="dashicons dashicons-yes-alt"></span> ' + esc_model_test.success_header + '</h4>';
        } else if (type === 'error') {
            header = '<h4><span class="dashicons dashicons-warning"></span> ' + esc_model_test.error_header + '</h4>';
        } else if (type === 'loading') {
            header = '<h4><span class="esc-test-spinner"></span> ' + esc_model_test.loading_header + '</h4>';
        }
        
        // Add content
        let content = '<p>' + message + '</p>';
        
        // Add data if available
        if (data && typeof data === 'object') {
            content += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        } else if (data && typeof data === 'string' && data.trim() !== '') {
            content += '<pre>' + data + '</pre>';
        }
        
        // Set the content
        $resultsContainer.html(header + content);
    }
    
    // Update usage statistics display
    function updateUsageStatistics(usage) {
        const $usageContainer = $('#esc-model-usage-stats');
        if ($usageContainer.length === 0) {
            return;
        }
        
        // Update the usage statistics
        $usageContainer.html(
            '<h4>' + esc_model_test.usage_header + '</h4>' +
            '<table class="widefat striped">' +
            '<tr><th>' + esc_model_test.usage_model + '</th><td>' + usage.model + '</td></tr>' +
            '<tr><th>' + esc_model_test.usage_tokens_in + '</th><td>' + usage.input_tokens + '</td></tr>' +
            '<tr><th>' + esc_model_test.usage_tokens_out + '</th><td>' + usage.output_tokens + '</td></tr>' +
            '<tr><th>' + esc_model_test.usage_total_tokens + '</th><td>' + usage.total_tokens + '</td></tr>' +
            '<tr><th>' + esc_model_test.usage_latency + '</th><td>' + usage.latency + 'ms</td></tr>' +
            '</table>'
        );
        
        // Show the container
        $usageContainer.show();
    }
    
    // Update model capabilities display
    function updateModelCapabilities(capabilities) {
        const $capabilitiesContainer = $('#esc-model-capabilities');
        if ($capabilitiesContainer.length === 0) {
            return;
        }
        
        // Create HTML for capabilities
        let capabilitiesHtml = '<h4>' + esc_model_test.capabilities_header + '</h4>';
        
        // Add supported generation methods
        if (capabilities.supportedGenerationMethods && capabilities.supportedGenerationMethods.length > 0) {
            capabilitiesHtml += '<h5>' + esc_model_test.capabilities_methods + '</h5>';
            capabilitiesHtml += '<ul>';
            capabilities.supportedGenerationMethods.forEach(function(method) {
                capabilitiesHtml += '<li>' + method + '</li>';
            });
            capabilitiesHtml += '</ul>';
        }
        
        // Add temperature range
        if (capabilities.temperatureRange) {
            capabilitiesHtml += '<h5>' + esc_model_test.capabilities_temperature + '</h5>';
            capabilitiesHtml += '<p>' + esc_model_test.capabilities_min + ': ' + capabilities.temperatureRange.min + '</p>';
            capabilitiesHtml += '<p>' + esc_model_test.capabilities_max + ': ' + capabilities.temperatureRange.max + '</p>';
        }
        
        // Add token limit
        if (capabilities.tokenLimit) {
            capabilitiesHtml += '<h5>' + esc_model_test.capabilities_token_limit + '</h5>';
            capabilitiesHtml += '<p>' + capabilities.tokenLimit + '</p>';
        }
        
        // Update the container
        $capabilitiesContainer.html(capabilitiesHtml);
        
        // Show the container
        $capabilitiesContainer.show();
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        initModelTest();
    });

})(jQuery);
