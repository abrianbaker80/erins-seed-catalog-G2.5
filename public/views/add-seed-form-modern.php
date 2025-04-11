<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Make sure modern form CSS is loaded first
wp_enqueue_style('esc-modern-form', ESC_PLUGIN_URL . 'public/css/esc-modern-form.css', [], ESC_VERSION);

// Enqueue enhanced AI results CSS and JS
wp_enqueue_style('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/css/esc-ai-results-enhanced.css', ['esc-modern-form'], ESC_VERSION);
wp_enqueue_script('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/js/esc-ai-results-enhanced.js', ['jquery'], ESC_VERSION, true);

// Enqueue fixes JS
wp_enqueue_style('esc-ai-results-fixes', ESC_PLUGIN_URL . 'public/css/esc-ai-results-fixes.css', ['esc-modern-form'], ESC_VERSION);
wp_enqueue_script('esc-ai-results-fixes', ESC_PLUGIN_URL . 'public/js/esc-ai-results-fixes.js', ['jquery'], ESC_VERSION, true);

// Prepare AJAX object data
$ajax_object = [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('esc_ajax_nonce'),
    'loading_text' => __('Saving...', 'erins-seed-catalog'),
    'error_text' => __('An error occurred.', 'erins-seed-catalog'),
    'form_submit_success' => __('Seed added successfully!', 'erins-seed-catalog'),
    'form_submit_error' => __('Error adding seed.', 'erins-seed-catalog'),
    'catalog_url' => get_permalink(get_option('esc_catalog_page_id')) ?: home_url('/seed-catalog/'),
    'add_another_text' => __('Add Another Seed', 'erins-seed-catalog'),
    'view_catalog_text' => __('View Catalog', 'erins-seed-catalog'),
];

// Localize script for AJAX calls - for both scripts
wp_localize_script('esc-ai-results-fixes', 'esc_ajax_object', $ajax_object);
wp_localize_script('esc-ai-results-enhanced', 'esc_ajax_object', $ajax_object);

// Add direct form submission handler
wp_add_inline_script('esc-ai-results-enhanced', '
document.addEventListener("DOMContentLoaded", function() {
    console.log("Direct form handler added");
    var form = document.getElementById("esc-add-seed-form");
    var submitBtn = document.getElementById("esc-submit-seed");

    if (form && submitBtn) {
        submitBtn.addEventListener("click", function(e) {
            e.preventDefault();
            console.log("Submit button clicked directly");

            // Get the form data
            var formData = new FormData(form);
            formData.append("action", "esc_add_seed");
            formData.append("nonce", esc_ajax_object.nonce);

            // Create AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open("POST", esc_ajax_object.ajax_url, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

            // Set up response handler
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 400) {
                    var response = JSON.parse(xhr.responseText);
                    console.log("AJAX response:", response);

                    if (response.success) {
                        alert("Seed added successfully!");
                        form.reset();
                    } else {
                        alert("Error: " + (response.data.message || "Unknown error"));
                    }
                } else {
                    alert("Error: Server returned status " + xhr.status);
                }
            };

            // Handle errors
            xhr.onerror = function() {
                alert("Error: Could not send request");
            };

            // Send the request
            xhr.send(formData);
        });
    }
});
');
?>

<div id="esc-add-seed-form-container" class="esc-container esc-modern-form">
    <h2><?php esc_html_e( 'Add New Seed to Catalog', 'erins-seed-catalog' ); ?></h2>

    <form id="esc-add-seed-form" class="esc-form" method="post">
        <!-- Message area for form feedback -->
        <div id="esc-form-messages" class="esc-message" style="display: none;"></div>

        <!-- Hidden fields for actual form submission -->
        <input type="hidden" id="esc_seed_name_hidden" name="seed_name" value="">
        <input type="hidden" id="esc_variety_name_hidden" name="variety_name" value="">
        <!-- Phase 1: AI Input -->
        <div class="esc-phase esc-phase-ai active" id="esc-phase-ai-input">
            <div class="esc-form-card esc-ai-card">
                <div class="esc-card-header">
                    <h3><?php esc_html_e('Add Seed with AI Assistance', 'erins-seed-catalog'); ?></h3>
                </div>
                <div class="esc-card-content">
                    <p class="esc-card-description"><?php esc_html_e('Enter the seed type and variety to automatically retrieve detailed information.', 'erins-seed-catalog'); ?></p>

                    <div class="esc-seed-variety-row">
                        <div class="esc-seed-field">
                            <div class="esc-floating-label">
                                <input type="text" id="esc_seed_name" placeholder=" " required autocomplete="off" data-target="esc_seed_name_hidden">
                                <label for="esc_seed_name"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="required">*</span></label>
                                <p class="description"><?php esc_html_e('Enter seed name.', 'erins-seed-catalog'); ?></p>
                            </div>
                        </div>

                        <div class="esc-variety-field">
                            <div class="esc-floating-label">
                                <input type="text" id="esc_variety_name" placeholder=" " autocomplete="off" data-target="esc_variety_name_hidden">
                                <label for="esc_variety_name"><?php esc_html_e('Variety (Optional)', 'erins-seed-catalog'); ?></label>
                                <p class="description"><?php esc_html_e('Select or enter variety name.', 'erins-seed-catalog'); ?></p>
                            </div>
                            <div id="esc-variety-dropdown" class="esc-variety-dropdown"></div>
                            <div class="esc-variety-loading" style="display: none;">
                                <span class="dashicons dashicons-update-alt esc-spin"></span> <?php esc_html_e('Loading varieties...', 'erins-seed-catalog'); ?>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="esc-ai-fetch-trigger" class="esc-button esc-button-primary esc-button-large">
                        <span class="dashicons dashicons-superhero"></span>
                        <span><?php esc_html_e('Generate Seed Details with AI', 'erins-seed-catalog'); ?></span>
                    </button>

                    <div class="esc-ai-status-container">
                        <!-- Initial State -->
                        <div class="esc-ai-initial">
                            <p><?php esc_html_e('AI will search for detailed information about your seeds.', 'erins-seed-catalog'); ?></p>
                        </div>

                        <!-- Loading State with Stages -->
                        <div class="esc-ai-loading" style="display: none;">
                            <div class="esc-loading-animation">
                                <div class="esc-loading-pulse"></div>
                            </div>
                            <div class="esc-loading-stages">
                                <div class="esc-loading-stage active" data-stage="1"><?php esc_html_e('Searching seed databases...', 'erins-seed-catalog'); ?></div>
                                <div class="esc-loading-stage" data-stage="2"><?php esc_html_e('Gathering growing information...', 'erins-seed-catalog'); ?></div>
                                <div class="esc-loading-stage" data-stage="3"><?php esc_html_e('Compiling seed details...', 'erins-seed-catalog'); ?></div>
                            </div>
                        </div>

                        <!-- Success State - Hidden, using the review phase header instead -->
                        <div class="esc-ai-success" style="display: none;">
                            <!-- Success message completely removed to avoid redundancy -->
                        </div>

                        <!-- Error State -->
                        <div class="esc-ai-error" style="display: none;">
                            <div class="esc-error-icon">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="esc-error-message">
                                <h4><?php esc_html_e('Couldn\'t Find Complete Information', 'erins-seed-catalog'); ?></h4>
                                <p><?php esc_html_e('Some details couldn\'t be found. You can:', 'erins-seed-catalog'); ?></p>
                                <ul>
                                    <li><?php esc_html_e('Try a different variety name', 'erins-seed-catalog'); ?></li>
                                    <li><?php esc_html_e('Check your spelling', 'erins-seed-catalog'); ?></li>
                                    <li><?php esc_html_e('Fill in the missing fields manually', 'erins-seed-catalog'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="esc-manual-toggle">
                        <a href="#" id="esc-toggle-manual-entry"><?php esc_html_e('Prefer to enter details manually?', 'erins-seed-catalog'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase 2: Review & Edit AI Results -->
        <div class="esc-phase esc-phase-review" id="esc-phase-review-edit" style="display: none;">
            <div class="esc-ai-result-summary">
                <div class="esc-ai-result-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="esc-ai-result-text">
                    <h3><?php esc_html_e('AI Found Information for', 'erins-seed-catalog'); ?> <span id="esc-seed-display-name"></span></h3>
                    <p><?php esc_html_e('Review the details below and make any necessary adjustments.', 'erins-seed-catalog'); ?></p>
                </div>
            </div>

            <div class="esc-ai-changes-summary">
                <button type="button" class="esc-toggle-changes">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php esc_html_e('Show AI Changes', 'erins-seed-catalog'); ?>
                </button>

                <div class="esc-changes-detail" style="display: none;">
                    <h4><?php esc_html_e('Fields Populated by AI', 'erins-seed-catalog'); ?></h4>
                    <ul class="esc-changes-list">
                        <!-- Dynamically populated -->
                    </ul>
                </div>
            </div>

            <!-- Review mode toggle removed -->

            <div class="esc-review-modes">
                <!-- Detailed Edit Mode -->
                    <!-- Basic Information Card -->
                    <div class="esc-form-card" data-ai-status="needs-review">
                        <div class="esc-card-header">
                            <h3><?php esc_html_e('Basic Information', 'erins-seed-catalog'); ?></h3>
                            <div class="esc-ai-status-badge">
                                <span class="dashicons dashicons-yes"></span>
                                <span class="esc-badge-text"><?php esc_html_e('AI Complete', 'erins-seed-catalog'); ?></span>
                            </div>
                        </div>

                        <div class="esc-card-content">
                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <div class="esc-floating-label">
                                        <div class="esc-input-with-confidence">
                                            <input type="text" id="esc_seed_name_review" placeholder=" " required data-target="esc_seed_name_hidden">
                                            <label for="esc_seed_name_review"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="required">*</span></label>
                                            <div class="esc-confidence-indicator" data-confidence="high">
                                                <span class="dashicons dashicons-shield"></span>
                                                <span class="esc-confidence-tooltip"><?php esc_html_e('High confidence: This information comes from verified sources.', 'erins-seed-catalog'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="esc-form-field">
                                    <div class="esc-floating-label">
                                        <div class="esc-input-with-confidence">
                                            <input type="text" id="esc_variety_name_review" placeholder=" " data-target="esc_variety_name_hidden">
                                            <label for="esc_variety_name_review"><?php esc_html_e('Variety', 'erins-seed-catalog'); ?></label>
                                            <div class="esc-confidence-indicator" data-confidence="high">
                                                <span class="dashicons dashicons-shield"></span>
                                                <span class="esc-confidence-tooltip"><?php esc_html_e('High confidence: This information comes from verified sources.', 'erins-seed-catalog'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <div class="esc-floating-label">
                                        <textarea id="esc_description" name="description" placeholder=" "></textarea>
                                        <label for="esc_description"><?php esc_html_e('Description', 'erins-seed-catalog'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <?php
                                    // Use the new image uploader component
                                    ESC_Image_Uploader::render('image_url', 'esc_image_url', '', __('Image', 'erins-seed-catalog'));
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plant Characteristics Card -->
                    <div class="esc-form-card" data-ai-status="partially-populated">
                        <div class="esc-card-header">
                            <h3><?php esc_html_e('Plant Characteristics', 'erins-seed-catalog'); ?></h3>
                            <div class="esc-ai-status-badge">
                                <span class="dashicons dashicons-marker"></span>
                                <span class="esc-badge-text"><?php esc_html_e('Needs Review', 'erins-seed-catalog'); ?></span>
                            </div>
                        </div>

                        <div class="esc-card-content">
                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_plant_type"><?php esc_html_e('Plant Type', 'erins-seed-catalog'); ?></label>
                                    <div class="esc-input-with-confidence">
                                        <input type="text" id="esc_plant_type" name="plant_type" placeholder="e.g., Determinate Tomato, Annual Flower">
                                        <div class="esc-confidence-indicator" data-confidence="medium">
                                            <span class="dashicons dashicons-shield"></span>
                                            <span class="esc-confidence-tooltip"><?php esc_html_e('Medium confidence: This information is likely correct but may need verification.', 'erins-seed-catalog'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_growth_habit"><?php esc_html_e('Growth Habit', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_growth_habit" name="growth_habit" placeholder="e.g., Bush, Vining, Upright">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_days_to_maturity"><?php esc_html_e('Days to Maturity', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_days_to_maturity" name="days_to_maturity" placeholder="e.g., 60-90">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_plant_size"><?php esc_html_e('Plant Size (H x W)', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_plant_size" name="plant_size" placeholder="e.g., 4-6 ft x 2-3 ft">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_fruit_info"><?php esc_html_e('Fruit/Flower Info', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_fruit_info" name="fruit_info" placeholder="e.g., 6-8 oz red globe tomato">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_flavor_profile"><?php esc_html_e('Flavor Profile', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_flavor_profile" name="flavor_profile" placeholder="e.g., Sweet and tangy">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_scent"><?php esc_html_e('Scent', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_scent" name="scent" placeholder="e.g., Strong lavender scent">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_bloom_time"><?php esc_html_e('Bloom Time', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_bloom_time" name="bloom_time" placeholder="e.g., Early Summer to Fall">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <label for="esc_special_characteristics"><?php esc_html_e('Special Characteristics', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_special_characteristics" name="special_characteristics" placeholder="e.g., Disease resistant (VFN), Heat tolerant, Heirloom"></textarea>
                                </div>
                            </div>


                        </div>
                    </div>

                    <!-- Growing Instructions Card -->
                    <div class="esc-form-card" data-ai-status="fully-populated">
                        <div class="esc-card-header">
                            <h3><?php esc_html_e('Growing Instructions', 'erins-seed-catalog'); ?></h3>
                            <div class="esc-ai-status-badge">
                                <span class="dashicons dashicons-yes"></span>
                                <span class="esc-badge-text"><?php esc_html_e('AI Complete', 'erins-seed-catalog'); ?></span>
                            </div>
                        </div>

                        <div class="esc-card-content">
                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_sowing_method"><?php esc_html_e('Sowing Method', 'erins-seed-catalog'); ?></label>
                                    <select id="esc_sowing_method" name="sowing_method" class="esc-select">
                                        <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                                        <option value="Direct Sow"><?php esc_html_e('Direct Sow', 'erins-seed-catalog'); ?></option>
                                        <option value="Start Indoors"><?php esc_html_e('Start Indoors', 'erins-seed-catalog'); ?></option>
                                        <option value="Both"><?php esc_html_e('Both', 'erins-seed-catalog'); ?></option>
                                    </select>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_sowing_depth"><?php esc_html_e('Sowing Depth', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_sowing_depth" name="sowing_depth" placeholder="e.g., 1/4 inch">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_sowing_spacing"><?php esc_html_e('Sowing Spacing', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_sowing_spacing" name="sowing_spacing" placeholder="e.g., 18-24 inches apart">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_germination_temp"><?php esc_html_e('Germination Temperature', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_germination_temp" name="germination_temp" placeholder="e.g., 70-85°F (21-29°C)">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_sun_requirements"><?php esc_html_e('Sun Requirements', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_sun_requirements" name="sun_requirements" placeholder="e.g., Full Sun">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_watering"><?php esc_html_e('Watering Needs', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_watering" name="watering" placeholder="e.g., Keep consistently moist"></textarea>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_fertilizer"><?php esc_html_e('Fertilizer', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_fertilizer" name="fertilizer" placeholder="e.g., Balanced fertilizer at planting"></textarea>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_pest_disease_info"><?php esc_html_e('Pest & Disease Info', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_pest_disease_info" name="pest_disease_info" placeholder="e.g., Susceptible to aphids, monitor regularly"></textarea>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_harvesting_tips"><?php esc_html_e('Harvesting Tips', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_harvesting_tips" name="harvesting_tips" placeholder="e.g., Harvest when fruits are fully colored"></textarea>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_storage_recommendations"><?php esc_html_e('Storage Recommendations', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_storage_recommendations" name="storage_recommendations" placeholder="e.g., Store in cool, dry conditions"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Card -->
                    <div class="esc-form-card" data-ai-status="not-populated">
                        <div class="esc-card-header">
                            <h3><?php esc_html_e('Additional Information', 'erins-seed-catalog'); ?></h3>
                            <div class="esc-ai-status-badge">
                                <span class="dashicons dashicons-warning"></span>
                                <span class="esc-badge-text"><?php esc_html_e('Not Found', 'erins-seed-catalog'); ?></span>
                            </div>
                        </div>

                        <div class="esc-card-content">
                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_usda_zones"><?php esc_html_e('USDA Hardiness Zones', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_usda_zones" name="usda_zones" placeholder="e.g., 3-9">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_pollinator_info"><?php esc_html_e('Pollinator Information', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_pollinator_info" name="pollinator_info" placeholder="e.g., Attracts bees and butterflies"></textarea>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_container_suitability"><?php esc_html_e('Container Suitability', 'erins-seed-catalog'); ?></label>
                                    <select id="esc_container_suitability" name="container_suitability" class="esc-select">
                                        <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                                        <option value="1"><?php esc_html_e('Yes', 'erins-seed-catalog'); ?></option>
                                        <option value="0"><?php esc_html_e('No', 'erins-seed-catalog'); ?></option>
                                    </select>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_cut_flower_potential"><?php esc_html_e('Cut Flower Potential', 'erins-seed-catalog'); ?></label>
                                    <select id="esc_cut_flower_potential" name="cut_flower_potential" class="esc-select">
                                        <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                                        <option value="1"><?php esc_html_e('Yes', 'erins-seed-catalog'); ?></option>
                                        <option value="0"><?php esc_html_e('No', 'erins-seed-catalog'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_edible_parts"><?php esc_html_e('Edible Parts', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_edible_parts" name="edible_parts" placeholder="e.g., Leaves and stems; Fruit; Root">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_historical_background"><?php esc_html_e('Historical Background', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_historical_background" name="historical_background" placeholder="e.g., Brief origin or history of the variety"></textarea>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_companion_plants"><?php esc_html_e('Companion Plants', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_companion_plants" name="companion_plants" placeholder="e.g., List of suggested companion plants"></textarea>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_regional_tips"><?php esc_html_e('Regional Tips', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_regional_tips" name="regional_tips" placeholder="e.g., Growing tips for specific regions"></textarea>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <label for="esc_seed_saving_info"><?php esc_html_e('Seed Saving Info', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_seed_saving_info" name="seed_saving_info" placeholder="e.g., Isolation distance, how to collect/dry seeds"></textarea>
                                </div>
                            </div>

                            <div class="esc-retry-ai">
                                <button type="button" class="esc-button esc-button-secondary" data-section="additional">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e('Try AI Again for This Section', 'erins-seed-catalog'); ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Categories & Notes Card -->
                    <div class="esc-form-card">
                        <div class="esc-card-header">
                            <h3><?php esc_html_e('Categories & Notes', 'erins-seed-catalog'); ?></h3>
                        </div>

                        <div class="esc-card-content">
                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <label for="esc_seed_category"><?php esc_html_e('Seed Category', 'erins-seed-catalog'); ?></label>
                                    <select id="esc_seed_category" name="esc_seed_category[]" multiple="multiple" class="esc-select-multiple">
                                        <?php echo ESC_Taxonomy::get_category_dropdown_options(); // Get hierarchical options ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('Select one or more relevant categories.', 'erins-seed-catalog'); ?></p>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <label for="esc_notes"><?php esc_html_e('Personal Notes', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_notes" name="notes" placeholder=" "></textarea>
                                    <p class="description"><?php esc_html_e('Your own observations, planting dates, results, etc.', 'erins-seed-catalog'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="esc-form-actions">
                <button type="button" id="esc-back-to-ai" class="esc-button esc-button-secondary">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php esc_html_e('Back to AI Search', 'erins-seed-catalog'); ?>
                </button>

                <button type="button" class="esc-button esc-button-primary" id="esc-submit-seed">
                    <span class="dashicons dashicons-saved"></span>
                    <?php esc_html_e('Submit Seed', 'erins-seed-catalog'); ?>
                </button>
            </div>
        </div>

        <!-- Manual Entry Mode -->
        <div class="esc-phase esc-phase-manual" id="esc-phase-manual-entry" style="display: none;">
            <div class="esc-manual-header">
                <h3><?php esc_html_e('Manual Seed Entry', 'erins-seed-catalog'); ?></h3>
                <p><?php esc_html_e('Fill in the details manually or', 'erins-seed-catalog'); ?> <a href="#" id="esc-back-to-ai-search"><?php esc_html_e('switch back to AI search', 'erins-seed-catalog'); ?></a>.</p>
            </div>

            <!-- Include the original form fields here -->
            <div id="esc-manual-form-fields">
                <?php include ESC_PLUGIN_DIR . 'public/views/add-seed-form-fields.php'; ?>
            </div>
        </div>
    </form>

<!-- Success screen will be dynamically added by esc-ai-results-enhanced.js -->


</div>
