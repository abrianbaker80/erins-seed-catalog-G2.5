<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Note: All CSS and JS files are enqueued in the shortcode handler
// This template only contains the HTML structure
?>

<div id="esc-add-seed-form-container" class="esc-container">
    <h2 class="esc-text-2xl esc-font-semibold esc-mb-6"><?php esc_html_e('Add New Seed to Catalog', 'erins-seed-catalog'); ?></h2>

    <form id="esc-add-seed-form" class="esc-form" method="post">
        <!-- Message area for form feedback -->
        <div id="esc-form-messages" class="esc-alert" style="display: none;"></div>

        <!-- Hidden fields for actual form submission -->
        <input type="hidden" id="esc_seed_name_hidden" name="seed_name" value="">
        <input type="hidden" id="esc_variety_name_hidden" name="variety_name" value="">

        <!-- Phase 1: AI Input -->
        <div class="esc-phase active" id="esc-phase-ai-input">
            <div class="esc-card">
                <div class="esc-card__header">
                    <h3 class="esc-card__title"><?php esc_html_e('Add Seed with AI Assistance', 'erins-seed-catalog'); ?></h3>
                </div>
                <div class="esc-card__content">
                    <p class="esc-mb-4"><?php esc_html_e('Enter the seed type and variety to automatically retrieve detailed information.', 'erins-seed-catalog'); ?></p>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <div class="esc-floating-label">
                                <input type="text" id="esc_seed_name" class="esc-form-input esc-floating-label__input" placeholder=" " required autocomplete="off" data-target="esc_seed_name_hidden">
                                <label for="esc_seed_name" class="esc-floating-label__label"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="esc-form-label__required">*</span></label>
                            </div>
                            <p class="esc-form-help"><?php esc_html_e('Enter seed name.', 'erins-seed-catalog'); ?></p>
                        </div>

                        <div class="esc-form-field">
                            <div class="esc-floating-label">
                                <input type="text" id="esc_variety_name" class="esc-form-input esc-floating-label__input" placeholder=" " autocomplete="off" data-target="esc_variety_name_hidden">
                                <label for="esc_variety_name" class="esc-floating-label__label"><?php esc_html_e('Variety (Optional)', 'erins-seed-catalog'); ?></label>
                            </div>
                            <p class="esc-form-help"><?php esc_html_e('Select or enter variety name.', 'erins-seed-catalog'); ?></p>
                            <div id="esc-variety-dropdown" class="esc-dropdown__menu" style="display: none;"></div>
                            <div class="esc-variety-loading" style="display: none;">
                                <span class="dashicons dashicons-update-alt esc-animate-pulse"></span> <?php esc_html_e('Loading varieties...', 'erins-seed-catalog'); ?>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="esc-ai-fetch-trigger" class="esc-button esc-button--primary esc-button--lg esc-mt-4">
                        <span class="esc-button__icon"><span class="dashicons dashicons-superhero"></span></span>
                        <span><?php esc_html_e('Generate Seed Details with AI', 'erins-seed-catalog'); ?></span>
                    </button>

                    <div class="esc-ai-status-container esc-mt-6">
                        <!-- Initial State -->
                        <div class="esc-ai-initial">
                            <p><?php esc_html_e('AI will search for detailed information about your seeds.', 'erins-seed-catalog'); ?></p>
                        </div>

                        <!-- Loading State with Stages -->
                        <div class="esc-ai-loading esc-loading" style="display: none;">
                            <div class="esc-loading__spinner"></div>
                            <p class="esc-loading__text"><?php esc_html_e('Searching for seed information...', 'erins-seed-catalog'); ?></p>
                            <div class="esc-loading__stages">
                                <div class="esc-loading__stage esc-loading__stage--active" data-stage="1"><?php esc_html_e('Searching seed databases...', 'erins-seed-catalog'); ?></div>
                                <div class="esc-loading__stage" data-stage="2"><?php esc_html_e('Gathering growing information...', 'erins-seed-catalog'); ?></div>
                                <div class="esc-loading__stage" data-stage="3"><?php esc_html_e('Compiling seed details...', 'erins-seed-catalog'); ?></div>
                            </div>
                        </div>

                        <!-- Error State -->
                        <div class="esc-ai-error esc-alert esc-alert--error" style="display: none;">
                            <div class="esc-alert__icon">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="esc-alert__content">
                                <h4 class="esc-alert__title"><?php esc_html_e('Couldn\'t Find Complete Information', 'erins-seed-catalog'); ?></h4>
                                <div class="esc-alert__message">
                                    <p><?php esc_html_e('Some details couldn\'t be found. You can:', 'erins-seed-catalog'); ?></p>
                                    <ul>
                                        <li><?php esc_html_e('Try a different variety name', 'erins-seed-catalog'); ?></li>
                                        <li><?php esc_html_e('Check your spelling', 'erins-seed-catalog'); ?></li>
                                        <li><?php esc_html_e('Fill in the missing fields manually', 'erins-seed-catalog'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="esc-manual-toggle esc-mt-4 esc-text-center">
                        <a href="#" id="esc-toggle-manual-entry" class="esc-text-primary"><?php esc_html_e('Prefer to enter details manually?', 'erins-seed-catalog'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase 2: Review & Edit AI Results -->
        <div class="esc-phase" id="esc-phase-review-edit" style="display: none;">
            <div class="esc-alert esc-alert--info esc-mb-6">
                <div class="esc-alert__icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="esc-alert__content">
                    <h4 class="esc-alert__title"><?php esc_html_e('AI Found Information for', 'erins-seed-catalog'); ?> <span id="esc-seed-display-name"></span></h4>
                    <p class="esc-alert__message"><?php esc_html_e('Review the details below and make any necessary adjustments.', 'erins-seed-catalog'); ?></p>
                </div>
            </div>

            <div class="esc-ai-changes-summary esc-mb-6">
                <button type="button" class="esc-button esc-button--secondary esc-toggle-changes">
                    <span class="esc-button__icon"><span class="dashicons dashicons-visibility"></span></span>
                    <?php esc_html_e('Show AI Changes', 'erins-seed-catalog'); ?>
                </button>

                <div class="esc-changes-detail esc-card esc-mt-3" style="display: none;">
                    <div class="esc-card__content">
                        <h4 class="esc-text-lg esc-font-semibold esc-mb-3"><?php esc_html_e('Fields Populated by AI', 'erins-seed-catalog'); ?></h4>
                        <ul class="esc-changes-list">
                            <!-- Dynamically populated -->
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Basic Information Card -->
            <div class="esc-card esc-card--primary esc-form-card" data-ai-status="fully-populated" data-section="basic">
                <div class="esc-card__header">
                    <h3 class="esc-card__title"><?php esc_html_e('Basic Information', 'erins-seed-catalog'); ?></h3>
                    <div class="esc-badge esc-badge--success esc-ai-status-badge">
                        <span class="esc-badge__icon"><span class="dashicons dashicons-yes"></span></span>
                        <span class="esc-badge-text"><?php esc_html_e('AI Complete', 'erins-seed-catalog'); ?></span>
                    </div>
                </div>

                <div class="esc-card__content">
                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <div class="esc-input-with-confidence">
                                <div class="esc-floating-label">
                                    <input type="text" id="esc_seed_name_review" class="esc-form-input esc-floating-label__input" placeholder=" " required data-target="esc_seed_name_hidden">
                                    <label for="esc_seed_name_review" class="esc-floating-label__label"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="esc-form-label__required">*</span></label>
                                </div>
                                <div class="esc-confidence esc-confidence--high esc-confidence-indicator" data-confidence="high">
                                    <span class="dashicons dashicons-shield"></span>
                                    <div class="esc-confidence__tooltip"><?php esc_html_e('High confidence: This information comes from verified sources.', 'erins-seed-catalog'); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="esc-form-field">
                            <div class="esc-input-with-confidence">
                                <div class="esc-floating-label">
                                    <input type="text" id="esc_variety_name_review" class="esc-form-input esc-floating-label__input" placeholder=" " data-target="esc_variety_name_hidden">
                                    <label for="esc_variety_name_review" class="esc-floating-label__label"><?php esc_html_e('Variety', 'erins-seed-catalog'); ?></label>
                                </div>
                                <div class="esc-confidence esc-confidence--high esc-confidence-indicator" data-confidence="high">
                                    <span class="dashicons dashicons-shield"></span>
                                    <div class="esc-confidence__tooltip"><?php esc_html_e('High confidence: This information comes from verified sources.', 'erins-seed-catalog'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field esc-form-field--full">
                            <label for="esc_description" class="esc-form-label"><?php esc_html_e('Description', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_description" name="description" class="esc-form-input esc-form-textarea" placeholder="<?php esc_attr_e('Enter a detailed description of the seed', 'erins-seed-catalog'); ?>"></textarea>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field esc-form-field--full">
                            <?php
                            // Use the image uploader component
                            ESC_Image_Uploader::render('image_url', 'esc_image_url', '', __('Image', 'erins-seed-catalog'));
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plant Characteristics Card -->
            <div class="esc-card esc-form-card" data-ai-status="partially-populated" data-section="characteristics">
                <div class="esc-card__header">
                    <h3 class="esc-card__title"><?php esc_html_e('Plant Characteristics', 'erins-seed-catalog'); ?></h3>
                    <div class="esc-badge esc-badge--warning esc-ai-status-badge">
                        <span class="esc-badge__icon"><span class="dashicons dashicons-marker"></span></span>
                        <span class="esc-badge-text"><?php esc_html_e('Needs Review', 'erins-seed-catalog'); ?></span>
                    </div>
                </div>

                <div class="esc-card__content">
                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_plant_type" class="esc-form-label"><?php esc_html_e('Plant Type', 'erins-seed-catalog'); ?></label>
                            <div class="esc-input-with-confidence">
                                <input type="text" id="esc_plant_type" name="plant_type" class="esc-form-input" placeholder="e.g., Determinate Tomato, Annual Flower">
                                <div class="esc-confidence esc-confidence--medium esc-confidence-indicator" data-confidence="medium">
                                    <span class="dashicons dashicons-shield"></span>
                                    <div class="esc-confidence__tooltip"><?php esc_html_e('Medium confidence: This information is likely correct but may need verification.', 'erins-seed-catalog'); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_growth_habit" class="esc-form-label"><?php esc_html_e('Growth Habit', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_growth_habit" name="growth_habit" class="esc-form-input" placeholder="e.g., Bush, Vining, Upright">
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_days_to_maturity" class="esc-form-label"><?php esc_html_e('Days to Maturity', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_days_to_maturity" name="days_to_maturity" class="esc-form-input" placeholder="e.g., 60-90">
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_plant_size" class="esc-form-label"><?php esc_html_e('Plant Size (H x W)', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_plant_size" name="plant_size" class="esc-form-input" placeholder="e.g., 4-6 ft x 2-3 ft">
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_fruit_info" class="esc-form-label"><?php esc_html_e('Fruit/Flower Info', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_fruit_info" name="fruit_info" class="esc-form-input" placeholder="e.g., 6-8 oz red globe tomato">
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_flavor_profile" class="esc-form-label"><?php esc_html_e('Flavor Profile', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_flavor_profile" name="flavor_profile" class="esc-form-input" placeholder="e.g., Sweet and tangy">
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_scent" class="esc-form-label"><?php esc_html_e('Scent', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_scent" name="scent" class="esc-form-input" placeholder="e.g., Strong lavender scent">
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_bloom_time" class="esc-form-label"><?php esc_html_e('Bloom Time', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_bloom_time" name="bloom_time" class="esc-form-input" placeholder="e.g., Early Summer to Fall">
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field esc-form-field--full">
                            <label for="esc_special_characteristics" class="esc-form-label"><?php esc_html_e('Special Characteristics', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_special_characteristics" name="special_characteristics" class="esc-form-textarea" placeholder="e.g., Disease resistant (VFN), Heat tolerant, Heirloom"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Growing Instructions Card -->
            <div class="esc-card esc-form-card" data-ai-status="fully-populated" data-section="growing">
                <div class="esc-card__header">
                    <h3 class="esc-card__title"><?php esc_html_e('Growing Instructions', 'erins-seed-catalog'); ?></h3>
                    <div class="esc-badge esc-badge--success esc-ai-status-badge">
                        <span class="esc-badge__icon"><span class="dashicons dashicons-yes"></span></span>
                        <span class="esc-badge-text"><?php esc_html_e('AI Complete', 'erins-seed-catalog'); ?></span>
                    </div>
                </div>

                <div class="esc-card__content">
                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_sowing_method" class="esc-form-label"><?php esc_html_e('Sowing Method', 'erins-seed-catalog'); ?></label>
                            <select id="esc_sowing_method" name="sowing_method" class="esc-form-select">
                                <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                                <option value="Direct Sow"><?php esc_html_e('Direct Sow', 'erins-seed-catalog'); ?></option>
                                <option value="Start Indoors"><?php esc_html_e('Start Indoors', 'erins-seed-catalog'); ?></option>
                                <option value="Both"><?php esc_html_e('Both', 'erins-seed-catalog'); ?></option>
                            </select>
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_sowing_depth" class="esc-form-label"><?php esc_html_e('Sowing Depth', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_sowing_depth" name="sowing_depth" class="esc-form-input" placeholder="e.g., 1/4 inch">
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_sowing_spacing" class="esc-form-label"><?php esc_html_e('Sowing Spacing', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_sowing_spacing" name="sowing_spacing" class="esc-form-input" placeholder="e.g., 18-24 inches apart">
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_germination_temp" class="esc-form-label"><?php esc_html_e('Germination Temperature', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_germination_temp" name="germination_temp" class="esc-form-input" placeholder="e.g., 70-85°F (21-29°C)">
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_sun_requirements" class="esc-form-label"><?php esc_html_e('Sun Requirements', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_sun_requirements" name="sun_requirements" class="esc-form-input" placeholder="e.g., Full Sun">
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_watering" class="esc-form-label"><?php esc_html_e('Watering Needs', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_watering" name="watering" class="esc-form-textarea" placeholder="e.g., Keep consistently moist"></textarea>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_fertilizer" class="esc-form-label"><?php esc_html_e('Fertilizer', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_fertilizer" name="fertilizer" class="esc-form-textarea" placeholder="e.g., Balanced fertilizer at planting"></textarea>
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_pest_disease_info" class="esc-form-label"><?php esc_html_e('Pest & Disease Info', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_pest_disease_info" name="pest_disease_info" class="esc-form-textarea" placeholder="e.g., Susceptible to aphids, monitor regularly"></textarea>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_harvesting_tips" class="esc-form-label"><?php esc_html_e('Harvesting Tips', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_harvesting_tips" name="harvesting_tips" class="esc-form-textarea" placeholder="e.g., Harvest when fruits are fully colored"></textarea>
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_storage_recommendations" class="esc-form-label"><?php esc_html_e('Storage Recommendations', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_storage_recommendations" name="storage_recommendations" class="esc-form-textarea" placeholder="e.g., Store in cool, dry conditions"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Card -->
            <div class="esc-card esc-form-card" data-ai-status="not-populated" data-section="additional">
                <div class="esc-card__header">
                    <h3 class="esc-card__title"><?php esc_html_e('Additional Information', 'erins-seed-catalog'); ?></h3>
                    <div class="esc-badge esc-badge--danger esc-ai-status-badge">
                        <span class="esc-badge__icon"><span class="dashicons dashicons-warning"></span></span>
                        <span class="esc-badge-text"><?php esc_html_e('Not Found', 'erins-seed-catalog'); ?></span>
                    </div>
                </div>

                <div class="esc-card__content">
                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_usda_zones" class="esc-form-label"><?php esc_html_e('USDA Hardiness Zones', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_usda_zones" name="usda_zones" class="esc-form-input" placeholder="e.g., 3-9">
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_pollinator_info" class="esc-form-label"><?php esc_html_e('Pollinator Information', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_pollinator_info" name="pollinator_info" class="esc-form-textarea" placeholder="e.g., Attracts bees and butterflies"></textarea>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_container_suitability" class="esc-form-label"><?php esc_html_e('Container Suitability', 'erins-seed-catalog'); ?></label>
                            <select id="esc_container_suitability" name="container_suitability" class="esc-form-select">
                                <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                                <option value="1"><?php esc_html_e('Yes', 'erins-seed-catalog'); ?></option>
                                <option value="0"><?php esc_html_e('No', 'erins-seed-catalog'); ?></option>
                            </select>
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_cut_flower_potential" class="esc-form-label"><?php esc_html_e('Cut Flower Potential', 'erins-seed-catalog'); ?></label>
                            <select id="esc_cut_flower_potential" name="cut_flower_potential" class="esc-form-select">
                                <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                                <option value="1"><?php esc_html_e('Yes', 'erins-seed-catalog'); ?></option>
                                <option value="0"><?php esc_html_e('No', 'erins-seed-catalog'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_edible_parts" class="esc-form-label"><?php esc_html_e('Edible Parts', 'erins-seed-catalog'); ?></label>
                            <input type="text" id="esc_edible_parts" name="edible_parts" class="esc-form-input" placeholder="e.g., Leaves and stems; Fruit; Root">
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_historical_background" class="esc-form-label"><?php esc_html_e('Historical Background', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_historical_background" name="historical_background" class="esc-form-textarea" placeholder="e.g., Brief origin or history of the variety"></textarea>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc_companion_plants" class="esc-form-label"><?php esc_html_e('Companion Plants', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_companion_plants" name="companion_plants" class="esc-form-textarea" placeholder="e.g., List of suggested companion plants"></textarea>
                        </div>

                        <div class="esc-form-field">
                            <label for="esc_regional_tips" class="esc-form-label"><?php esc_html_e('Regional Tips', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_regional_tips" name="regional_tips" class="esc-form-textarea" placeholder="e.g., Growing tips for specific regions"></textarea>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field esc-form-field--full">
                            <label for="esc_seed_saving_info" class="esc-form-label"><?php esc_html_e('Seed Saving Info', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_seed_saving_info" name="seed_saving_info" class="esc-form-textarea" placeholder="e.g., Isolation distance, how to collect/dry seeds"></textarea>
                        </div>
                    </div>

                    <div class="esc-retry-ai esc-mt-4">
                        <button type="button" class="esc-button esc-button--secondary" data-section="additional">
                            <span class="esc-button__icon"><span class="dashicons dashicons-update"></span></span>
                            <?php esc_html_e('Try AI Again for This Section', 'erins-seed-catalog'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Categories & Notes Card -->
            <div class="esc-card esc-form-card" data-section="categories">
                <div class="esc-card__header">
                    <h3 class="esc-card__title"><?php esc_html_e('Categories & Notes', 'erins-seed-catalog'); ?></h3>
                </div>

                <div class="esc-card__content">
                    <div class="esc-form-row">
                        <div class="esc-form-field esc-form-field--full">
                            <label for="esc_seed_category" class="esc-form-label"><?php esc_html_e('Seed Category', 'erins-seed-catalog'); ?></label>
                            <select id="esc_seed_category" name="esc_seed_category[]" multiple="multiple" class="esc-form-select esc-form-select--multiple">
                                <?php echo ESC_Taxonomy::get_category_dropdown_options(); // Get hierarchical options ?>
                            </select>
                            <p class="esc-form-help"><?php esc_html_e('Select one or more relevant categories.', 'erins-seed-catalog'); ?></p>
                        </div>
                    </div>

                    <div class="esc-form-row">
                        <div class="esc-form-field esc-form-field--full">
                            <label for="esc_notes" class="esc-form-label"><?php esc_html_e('Personal Notes', 'erins-seed-catalog'); ?></label>
                            <textarea id="esc_notes" name="notes" class="esc-form-textarea" placeholder=" "></textarea>
                            <p class="esc-form-help"><?php esc_html_e('Your own observations, planting dates, results, etc.', 'erins-seed-catalog'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="esc-form-actions esc-mt-6">
                <button type="button" id="esc-back-to-ai" class="esc-button esc-button--secondary">
                    <span class="esc-button__icon"><span class="dashicons dashicons-arrow-left-alt"></span></span>
                    <?php esc_html_e('Back to AI Search', 'erins-seed-catalog'); ?>
                </button>

                <button type="button" id="esc-submit-seed" class="esc-button esc-button--primary">
                    <span class="esc-button__icon"><span class="dashicons dashicons-saved"></span></span>
                    <?php esc_html_e('Submit Seed', 'erins-seed-catalog'); ?>
                </button>
            </div>
        </div>

        <!-- Manual Entry Mode -->
        <div class="esc-phase" id="esc-phase-manual-entry" style="display: none;">
            <div class="esc-card">
                <div class="esc-card__header">
                    <h3 class="esc-card__title"><?php esc_html_e('Manual Seed Entry', 'erins-seed-catalog'); ?></h3>
                </div>
                <div class="esc-card__content">
                    <p class="esc-mb-4"><?php esc_html_e('Fill in the details manually or', 'erins-seed-catalog'); ?> <a href="#" id="esc-back-to-ai-search" class="esc-text-primary"><?php esc_html_e('switch back to AI search', 'erins-seed-catalog'); ?></a>.</p>

                    <!-- Include the original form fields here -->
                    <div id="esc-manual-form-fields">
                        <?php include ESC_PLUGIN_DIR . 'public/views/add-seed-form-fields.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
