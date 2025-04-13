/**
 * Erin's Seed Catalog - Core Module
 *
 * This is the main entry point for the JavaScript functionality.
 * It uses a modular pattern to organize code and prevent global namespace pollution.
 */

// Create a self-executing anonymous function to encapsulate our code
window.ESC = (function($) {
    'use strict';

    // Private variables
    let _config = {
        debug: false,
        ajaxUrl: '',
        nonce: '',
        loadingText: 'Loading...',
        errorText: 'An error occurred.',
        formSubmitSuccess: 'Seed added successfully!',
        formSubmitError: 'Error adding seed.',
        catalogUrl: '/',
        addAnotherText: 'Add Another Seed',
        viewCatalogText: 'View Catalog'
    };

    // Private methods
    function _log(...args) {
        if (_config.debug && args[0] !== undefined) {
            // Limit log depth to prevent circular references
            const safeArgs = args.map(arg => {
                if (typeof arg === 'object' && arg !== null) {
                    try {
                        // Create a shallow copy to avoid circular references
                        return Array.isArray(arg) ? [...arg] : {...arg};
                    } catch (e) {
                        return '[Complex Object]';
                    }
                }
                return arg;
            });
            console.log('[ESC]', ...safeArgs);
        }
    }

    function _error(...args) {
        if (_config.debug && args[0] !== undefined) {
            // Limit log depth to prevent circular references
            const safeArgs = args.map(arg => {
                if (typeof arg === 'object' && arg !== null) {
                    try {
                        // Create a shallow copy to avoid circular references
                        return Array.isArray(arg) ? [...arg] : {...arg};
                    } catch (e) {
                        return '[Complex Object]';
                    }
                }
                return arg;
            });
            console.error('[ESC]', ...safeArgs);
        }
    }

    function _init(options = {}) {
        // Merge options with defaults
        _config = { ..._config, ...options };

        _log('Initializing with config:', _config);

        // Initialize modules
        ESC.UI.init();
        ESC.Form.init();
        ESC.AI.init();

        // Initialize Variety module if it exists
        if (ESC.Variety && typeof ESC.Variety.init === 'function') {
            ESC.Variety.init();
        }

        _log('Initialization complete');
    }

    // Public API
    return {
        init: _init,
        log: _log,
        error: _error,
        getConfig: function() {
            return { ..._config }; // Return a copy to prevent modification
        }
    };
})(jQuery);

// Initialize when document is ready
jQuery(document).ready(function($) {
    // Get configuration from WordPress localized script
    const config = window.esc_ajax_object || {};

    // Debug check for form existence
    if ($('#esc-add-seed-form').length) {
        console.log('Form found, initializing ESC modules');
    } else {
        console.log('Form not found, check if the shortcode is properly rendered');
    }

    // Initialize the application
    ESC.init({
        debug: true, // Set to false in production
        ajaxUrl: config.ajax_url || '',
        nonce: config.nonce || '',
        loadingText: config.loading_text || 'Loading...',
        errorText: config.error_text || 'An error occurred.',
        formSubmitSuccess: config.form_submit_success || 'Seed added successfully!',
        formSubmitError: config.form_submit_error || 'Error adding seed.',
        catalogUrl: config.catalog_url || '/',
        addAnotherText: config.add_another_text || 'Add Another Seed',
        viewCatalogText: config.view_catalog_text || 'View Catalog'
    });

    // Ensure Variety module is initialized
    if (window.ESC && window.ESC.Variety && typeof window.ESC.Variety.init === 'function') {
        setTimeout(function() {
            window.ESC.Variety.init();
        }, 100);
    }
});
