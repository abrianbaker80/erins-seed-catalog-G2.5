/**
 * Modern Drag & Drop Image Uploader for Erin's Seed Catalog
 */
(function($) {
    'use strict';

    // Initialize the image uploader
    function initImageUploader() {
        // Create uploader instances for each uploader container
        $('.esc-image-uploader').each(function() {
            const $container = $(this);
            const $dropzone = $container.find('.esc-dropzone');
            const $fileInput = $container.find('.esc-file-input');
            const $urlInput = $container.find('.esc-url-input');
            const $preview = $container.find('.esc-image-preview');
            const $previewImg = $container.find('.esc-preview-image');
            const $removeBtn = $container.find('.esc-remove-image');
            const $progress = $container.find('.esc-upload-progress');
            const $progressBar = $container.find('.esc-progress-bar');
            const $error = $container.find('.esc-upload-error');
            const $wpMediaBtn = $container.find('.esc-wp-media-btn');
            const $downloadBtn = $container.find('.esc-download-image-btn');

            // Initialize with existing image if URL is present
            if ($urlInput.val()) {
                showImagePreview($urlInput.val());
            }

            // Handle click on dropzone
            $dropzone.on('click', function(e) {
                if (!$(e.target).closest('.esc-wp-media-btn').length) {
                    $fileInput.trigger('click');
                }
            });

            // Handle file selection
            $fileInput.on('change', function() {
                const files = this.files;
                if (files.length > 0) {
                    handleFileUpload(files[0]);
                }
            });

            // Handle drag and drop events
            $dropzone.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $dropzone.addClass('esc-drag-over');
            });

            $dropzone.on('dragleave dragend drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $dropzone.removeClass('esc-drag-over');
            });

            $dropzone.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    handleFileUpload(files[0]);
                }
            });

            // Handle WordPress media library button
            $wpMediaBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openMediaLibrary();
            });

            // Handle download image button
            $downloadBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                downloadImage();
            });

            // Handle remove button
            $removeBtn.on('click', function(e) {
                e.preventDefault();
                removeImage();
            });

            // Function to handle file upload
            function handleFileUpload(file) {
                // Check if file is an image
                if (!file.type.match('image.*')) {
                    showError('Please select an image file (JPEG, PNG, GIF).');
                    return;
                }

                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showError('Image size should be less than 5MB.');
                    return;
                }

                // Reset error message
                hideError();

                // Show upload progress
                $progress.show();
                $progressBar.css('width', '0%');

                // Create FormData for AJAX upload
                const formData = new FormData();
                formData.append('action', 'esc_upload_image');
                formData.append('nonce', esc_ajax_object.nonce);
                formData.append('image', file);

                // Upload using AJAX
                $.ajax({
                    url: esc_ajax_object.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = (e.loaded / e.total) * 100;
                                $progressBar.css('width', percent + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $progress.hide();

                        if (response.success) {
                            // Set the URL in the input field
                            $urlInput.val(response.data.url);

                            // Show image preview
                            showImagePreview(response.data.url);
                        } else {
                            showError(response.data.message || 'Error uploading image.');
                        }
                    },
                    error: function() {
                        $progress.hide();
                        showError('Error uploading image. Please try again.');
                    }
                });
            }

            // Function to open WordPress media library
            function openMediaLibrary() {
                // If the media frame already exists, reopen it
                if (window.esc_media_frame) {
                    window.esc_media_frame.open();
                    return;
                }

                // Create a new media frame
                window.esc_media_frame = wp.media({
                    title: 'Select or Upload an Image',
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });

                // When an image is selected in the media frame...
                window.esc_media_frame.on('select', function() {
                    // Get media attachment details
                    const attachment = window.esc_media_frame.state().get('selection').first().toJSON();

                    // Set the URL in the input field
                    $urlInput.val(attachment.url);

                    // Show image preview
                    showImagePreview(attachment.url);
                });

                // Open the media frame
                window.esc_media_frame.open();
            }

            // Function to show image preview
            function showImagePreview(url) {
                $previewImg.attr('src', url);
                $preview.show();
                $dropzone.addClass('has-image');

                // Hide manual download container if it exists
                const $manualContainer = $container.find('.esc-manual-download-container');
                if ($manualContainer.length) {
                    $manualContainer.hide();
                }
            }

            // Function to remove image
            function removeImage() {
                $urlInput.val('');
                $preview.hide();
                $dropzone.removeClass('has-image');
                $fileInput.val('');

                // Show manual download container if it exists
                const $manualContainer = $container.find('.esc-manual-download-container');
                if ($manualContainer.length) {
                    $manualContainer.show();
                }
            }

            // Function to show error message
            function showError(message) {
                $error.text(message).show();
            }

            // Function to hide error message
            function hideError() {
                $error.hide();
            }

            // Function to download image from URL
            function downloadImage() {
                // Get the URL from the input field
                const imageUrl = $urlInput.val().trim();

                // Check if URL is provided
                if (!imageUrl) {
                    showError('Please enter an image URL to download.');
                    return;
                }

                // Validate URL format
                if (!isValidUrl(imageUrl)) {
                    showError('Please enter a valid URL.');
                    return;
                }

                // Reset error message
                hideError();

                // Show upload progress
                $progress.show();
                $progressBar.css('width', '0%');

                // Create FormData for AJAX request
                const formData = new FormData();
                formData.append('action', 'esc_download_image');
                formData.append('nonce', esc_ajax_object.nonce);
                formData.append('image_url', imageUrl);

                // Send AJAX request
                $.ajax({
                    url: esc_ajax_object.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percent = (e.loaded / e.total) * 100;
                                $progressBar.css('width', percent + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $progress.hide();

                        if (response.success) {
                            // Set the URL in the input field
                            $urlInput.val(response.data.url);

                            // Show image preview
                            showImagePreview(response.data.url);
                        } else {
                            showError(response.data.message || 'Error downloading image.');
                        }
                    },
                    error: function() {
                        $progress.hide();
                        showError('Error downloading image. Please try again.');
                    }
                });
            }

            // Function to validate URL
            function isValidUrl(url) {
                try {
                    new URL(url);
                    return true;
                } catch (e) {
                    return false;
                }
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        // Check if wp.media is available (only in admin)
        if (typeof wp !== 'undefined' && wp.media) {
            initImageUploader();
        } else {
            // In frontend, we need to check if we're in the admin bar
            // and wait for wp.media to be available
            if ($('#wpadminbar').length) {
                // Try to initialize after a delay to allow wp.media to load
                setTimeout(function() {
                    if (typeof wp !== 'undefined' && wp.media) {
                        initImageUploader();
                    } else {
                        console.warn('WordPress Media Library not available. Using basic file upload.');
                        initImageUploader();
                    }
                }, 1000);
            } else {
                // No admin bar, just initialize with basic functionality
                initImageUploader();
            }
        }
    });

})(jQuery);
