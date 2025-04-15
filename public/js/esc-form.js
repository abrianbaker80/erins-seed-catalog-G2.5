/**
 * Erin's Seed Catalog - Form Module
 *
 * Handles all form-related functionality including:
 * - Form validation
 * - Form submission
 * - Field synchronization
 * - Form state management
 */

// Add the Form module to our ESC namespace
ESC.Form = (function($) {
    'use strict';

    // Private variables
    let _initialized = false;
    let _formState = {
        isSubmitting: false,
        hasChanges: false,
        validationErrors: {}
    };

    // Private methods
    function _initFormHandlers() {
        ESC.log('Initializing form handlers');

        // Get the main form
        const $form = $('#esc-add-seed-form');

        if (!$form.length) {
            ESC.log('Form not found, skipping form initialization');
            return;
        }

        // Initialize field synchronization
        _initFieldSync();

        // Initialize form validation
        _initFormValidation();

        // Initialize form submission
        _initFormSubmission($form);

        // Track form changes
        _trackFormChanges($form);
    }

    function _initFieldSync() {
        ESC.log('Initializing field synchronization');

        // Track last values to prevent unnecessary updates
        const lastValues = {};

        // Find all input fields with data-target attribute
        $('input[data-target]').each(function() {
            const $input = $(this);
            const targetId = $input.data('target');

            // Skip if already initialized
            if ($input.hasClass('esc-field-sync--initialized')) {
                return;
            }

            // Initialize with current value
            const currentValue = $input.val();
            if (currentValue) {
                lastValues[targetId] = currentValue;
                $('#' + targetId).val(currentValue);
            }

            // Add input event handler
            $input.on('input', function() {
                const value = $(this).val();

                // Only update if the value has changed
                if (lastValues[targetId] !== value) {
                    lastValues[targetId] = value;

                    // Update the target field
                    $('#' + targetId).val(value);

                    // Also update any other fields with the same target
                    $('input[data-target="' + targetId + '"]').not(this).val(value);

                    // Trigger change event on the target field
                    $('#' + targetId).trigger('change');

                    ESC.log('Synced field value:', targetId, value);
                }
            });

            // Mark as initialized
            $input.addClass('esc-field-sync--initialized');
        });
    }

    function _initFormValidation() {
        ESC.log('Initializing form validation');

        // Get the main form
        const $form = $('#esc-add-seed-form');

        if (!$form.length) {
            return;
        }

        // Add validation rules
        const requiredFields = ['seed_name'];

        // Validate on submit
        $form.on('submit', function(e) {
            const isValid = _validateForm($form, requiredFields);

            if (!isValid) {
                e.preventDefault();
                return false;
            }
        });

        // Live validation on field change
        $form.find('input, select, textarea').on('change blur', function() {
            const $field = $(this);
            const fieldName = $field.attr('name');

            if (requiredFields.includes(fieldName)) {
                _validateField($field);
            }
        });
    }

    function _validateForm($form, requiredFields) {
        ESC.log('Validating form');

        let isValid = true;
        _formState.validationErrors = {};

        // Check required fields
        requiredFields.forEach(function(fieldName) {
            const $field = $form.find('[name="' + fieldName + '"]');

            if (!$field.length) {
                return;
            }

            const fieldValid = _validateField($field);
            isValid = isValid && fieldValid;
        });

        return isValid;
    }

    function _validateField($field) {
        const fieldName = $field.attr('name');
        const value = $field.val();
        const $formGroup = $field.closest('.esc-form-field');
        const $errorMessage = $formGroup.find('.esc-form-error');

        // Remove existing error
        $field.removeClass('esc-form-input--error');
        if ($errorMessage.length) {
            $errorMessage.remove();
        }

        // Check if empty
        if (!value || value.trim() === '') {
            const errorMessage = 'This field is required.';

            // Add error class
            $field.addClass('esc-form-input--error');

            // Add error message
            $formGroup.append('<div class="esc-form-error">' + errorMessage + '</div>');

            // Store error
            _formState.validationErrors[fieldName] = errorMessage;

            return false;
        }

        // Field is valid
        delete _formState.validationErrors[fieldName];
        return true;
    }

    function _initFormSubmission($form) {
        ESC.log('Initializing form submission');

        // Handle submit button click
        $('#esc-submit-seed').on('click', function(e) {
            e.preventDefault();

            // Prevent double submission
            if (_formState.isSubmitting) {
                ESC.log('Form already submitting, preventing duplicate submission');
                return;
            }

            _submitForm($form);
        });

        // Handle form submit event
        $form.on('submit', function(e) {
            e.preventDefault();

            // Prevent double submission
            if (_formState.isSubmitting) {
                ESC.log('Form already submitting, preventing duplicate submission');
                return;
            }

            _submitForm($form);
        });
    }

    function _submitForm($form) {
        ESC.log('Submitting form');

        // Validate form before submission
        const isValid = _validateForm($form, ['seed_name']);

        if (!isValid) {
            ESC.log('Form validation failed');
            return;
        }

        // Update submission state
        _formState.isSubmitting = true;

        // Get form data
        const formData = $form.serialize();

        // Ensure image URL is included in the form data
        const $imageUrl = $form.find('input[name="image_url"]');
        if ($imageUrl.length && $imageUrl.val()) {
            ESC.log('Found image URL in form:', $imageUrl.val());

            // Check if there's a hidden image URL field
            const $hiddenImageUrl = $form.find('input[name="image_url_hidden"]');
            if (!$hiddenImageUrl.length) {
                // Add a hidden field with the image URL
                ESC.log('Adding hidden image URL field to form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'image_url_hidden',
                    value: $imageUrl.val()
                }).appendTo($form);
            }
        }

        // Add AJAX action and nonce
        const data = formData + '&action=esc_add_seed&nonce=' + ESC.getConfig().nonce;

        // Show loading state
        const $submitButton = $('#esc-submit-seed');
        const $messageDiv = $('#esc-form-messages');

        $submitButton.prop('disabled', true);
        $messageDiv.removeClass('success error').addClass('loading').text(ESC.getConfig().loadingText).show();

        // Submit the form via AJAX
        $.ajax({
            url: ESC.getConfig().ajaxUrl,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                ESC.log('Form submission response:', response);

                if (response.success) {
                    // Hide message
                    $messageDiv.hide();

                    // Show success confirmation
                    _showSuccessConfirmation();
                } else {
                    // Show error message
                    const errorMsg = response.data && response.data.message
                        ? response.data.message
                        : ESC.getConfig().formSubmitError;

                    $messageDiv.removeClass('loading').addClass('error').text(errorMsg);
                    ESC.error('Form submission error:', response.data);
                }
            },
            error: function(xhr, status, error) {
                // Show error message
                ESC.error('AJAX error:', status, error);
                $messageDiv.removeClass('loading').addClass('error').text(ESC.getConfig().errorText);
            },
            complete: function() {
                // Reset submission state
                _formState.isSubmitting = false;
                $submitButton.prop('disabled', false);
            }
        });
    }

    function _showSuccessConfirmation() {
        ESC.log('Showing success confirmation');

        // Create confirmation modal if it doesn't exist
        if ($('.esc-confirmation').length === 0) {
            const confirmationHTML = `
                <div class="esc-modal esc-confirmation">
                    <div class="esc-modal__backdrop"></div>
                    <div class="esc-modal__container">
                        <div class="esc-modal__content esc-text-center">
                            <div class="esc-confirmation__icon">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </div>
                            <h2 class="esc-confirmation__title">${ESC.getConfig().formSubmitSuccess}</h2>
                            <p class="esc-confirmation__text">Your seed has been added to the catalog.</p>
                            <div class="esc-confirmation__actions">
                                <button class="esc-button esc-button--secondary esc-confirmation__view-catalog">
                                    ${ESC.getConfig().viewCatalogText}
                                </button>
                                <button class="esc-button esc-button--primary esc-confirmation__add-another">
                                    ${ESC.getConfig().addAnotherText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(confirmationHTML);

            // Add event handlers
            $('.esc-confirmation__add-another').on('click', function() {
                _resetForm();
                $('.esc-confirmation').removeClass('esc-modal--active');
            });

            $('.esc-confirmation__view-catalog').on('click', function() {
                const catalogUrl = ESC.getConfig().catalogUrl;
                ESC.log('Redirecting to catalog URL:', catalogUrl);
                window.location.href = catalogUrl;
            });

            $('.esc-modal__backdrop').on('click', function() {
                $('.esc-confirmation').removeClass('esc-modal--active');
            });
        }

        // Show the confirmation
        $('.esc-confirmation').addClass('esc-modal--active');
    }

    function _resetForm() {
        ESC.log('Resetting form');

        // Get the form
        const $form = $('#esc-add-seed-form');

        if (!$form.length) {
            return;
        }

        // Reset the form
        $form[0].reset();

        // Reset hidden fields
        $('input[type="hidden"]').val('');

        // Reset validation state
        $('.esc-form-input--error').removeClass('esc-form-input--error');
        $('.esc-form-error').remove();

        // Reset form state
        _formState.hasChanges = false;
        _formState.validationErrors = {};

        // Reset to initial phase
        if (typeof ESC.UI.showPhase === 'function') {
            ESC.UI.showPhase('esc-phase-ai-input');
        } else {
            $('.esc-phase').hide();
            $('#esc-phase-ai-input').show();
        }

        // Trigger form reset event
        $(document).trigger('esc:formReset');
    }

    function _trackFormChanges($form) {
        ESC.log('Tracking form changes');

        // Track changes to form fields
        $form.find('input, select, textarea').on('change input', function() {
            _formState.hasChanges = true;
        });
    }

    function _init() {
        if (_initialized) {
            return;
        }

        ESC.log('Initializing Form module');

        // Initialize form handlers
        _initFormHandlers();

        // Setup event listeners
        $(document).on('esc:reinitForm', function() {
            ESC.log('Reinitializing form handlers');
            _initFormHandlers();
        });

        _initialized = true;
    }

    // Public API
    return {
        init: _init,
        resetForm: _resetForm,
        getFormState: function() {
            return { ..._formState }; // Return a copy to prevent modification
        },
        validateForm: function($form, fields) {
            return _validateForm($form, fields);
        }
    };
})(jQuery);
