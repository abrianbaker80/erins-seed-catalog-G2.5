<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get all available fields for column selection
$available_fields = ESC_DB::get_allowed_fields();

// Add additional fields
$additional_fields = [
    'id' => 'ID',
    'date_added' => 'Date Added',
    'last_updated' => 'Last Updated',
    'categories' => 'Categories'
];

// Merge all fields
$all_fields = array_merge($available_fields, $additional_fields);

// Group fields by category for better organization
$field_categories = [
    'basic' => [
        'title' => 'Basic Information',
        'fields' => ['seed_name', 'variety_name', 'plant_type', 'description']
    ],
    'growing' => [
        'title' => 'Growing Information',
        'fields' => ['growth_habit', 'days_to_maturity', 'sunlight', 'watering', 'sowing_method', 'sowing_depth', 'sowing_spacing', 'germination_temp', 'germination_rate']
    ],
    'harvest' => [
        'title' => 'Harvest & Use',
        'fields' => ['bloom_time', 'fruit_info', 'flavor_profile', 'scent', 'harvesting_tips', 'cut_flower_potential']
    ],
    'management' => [
        'title' => 'Management',
        'fields' => ['pest_disease_info', 'companion_plants', 'fertilizer', 'pollinator_info']
    ],
    'storage' => [
        'title' => 'Storage & Inventory',
        'fields' => ['seed_quantity', 'seed_treatment', 'storage_recommendations', 'seed_saving_info']
    ],
    'meta' => [
        'title' => 'Metadata',
        'fields' => ['id', 'categories', 'date_added', 'last_updated', 'usda_zones', 'price', 'availability', 'brand', 'sku_upc', 'company_info']
    ],
    'other' => [
        'title' => 'Other',
        'fields' => []
    ]
];

// Format field names for display
$field_display_names = [];
foreach ($all_fields as $field => $type) {
    // Convert snake_case to Title Case
    $display_name = ucwords(str_replace('_', ' ', $field));
    $field_display_names[$field] = $display_name;

    // Add fields not explicitly categorized to 'other'
    $found = false;
    foreach ($field_categories as $cat_key => $category) {
        if (in_array($field, $category['fields'])) {
            $found = true;
            break;
        }
    }

    if (!$found) {
        $field_categories['other']['fields'][] = $field;
    }
}

// Default selected fields (commonly used fields)
$default_selected = [
    'seed_name', 'variety_name', 'plant_type', 'growth_habit',
    'days_to_maturity', 'sunlight', 'categories'
];

// Count total fields
$total_fields = count($field_display_names);
?>

<div id="esc-export-form-container" class="esc-container">
    <h2><?php esc_html_e('Export Seed Catalog', 'erins-seed-catalog'); ?></h2>

    <div class="esc-export-form-wrapper">
        <form id="esc-export-form" class="esc-form">
            <div class="esc-export-options">
                <h3><?php esc_html_e('Select Columns to Export', 'erins-seed-catalog'); ?></h3>

                <div class="esc-column-selection">
                    <div class="esc-column-actions">
                        <button type="button" id="esc-select-all-columns" class="esc-button esc-button-secondary">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e('Select All', 'erins-seed-catalog'); ?>
                        </button>
                        <button type="button" id="esc-select-none-columns" class="esc-button esc-button-secondary">
                            <span class="dashicons dashicons-no-alt"></span>
                            <?php esc_html_e('Select None', 'erins-seed-catalog'); ?>
                        </button>
                        <button type="button" id="esc-select-common-columns" class="esc-button esc-button-secondary">
                            <span class="dashicons dashicons-star-filled"></span>
                            <?php esc_html_e('Common Fields', 'erins-seed-catalog'); ?>
                        </button>
                    </div>

                    <div class="esc-column-categories">
                        <div class="esc-column-category active" data-category="all"><?php esc_html_e('All Fields', 'erins-seed-catalog'); ?> (<?php echo $total_fields; ?>)</div>
                        <?php foreach ($field_categories as $cat_key => $category) : ?>
                            <?php if (!empty($category['fields'])) : ?>
                                <div class="esc-column-category" data-category="<?php echo esc_attr($cat_key); ?>">
                                    <?php echo esc_html($category['title']); ?> (<?php echo count($category['fields']); ?>)
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="esc-columns-grid">
                        <?php foreach ($field_categories as $cat_key => $category) : ?>
                            <?php foreach ($category['fields'] as $field) : ?>
                                <?php if (isset($field_display_names[$field])) : ?>
                                    <div class="esc-column-option" data-category="<?php echo esc_attr($cat_key); ?>">
                                        <label>
                                            <input type="checkbox" name="export_columns[]" value="<?php echo esc_attr($field); ?>"
                                                <?php checked(in_array($field, $default_selected)); ?>>
                                            <span><?php echo esc_html($field_display_names[$field]); ?></span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="esc-selected-count">
                        <span id="esc-selected-count">0</span> <?php esc_html_e('of', 'erins-seed-catalog'); ?> <?php echo $total_fields; ?> <?php esc_html_e('fields selected', 'erins-seed-catalog'); ?>
                    </div>
                </div>

                <div class="esc-export-format">
                    <h3><?php esc_html_e('Export Format', 'erins-seed-catalog'); ?></h3>
                    <div class="esc-format-options">
                        <label>
                            <input type="radio" name="export_format" value="csv" checked>
                            <span>
                                <span class="dashicons dashicons-media-spreadsheet"></span>
                                <?php esc_html_e('CSV (Excel)', 'erins-seed-catalog'); ?>
                            </span>
                        </label>
                    </div>
                    <div class="esc-format-description">
                        <p><?php esc_html_e('CSV files can be opened with Microsoft Excel, Google Sheets, or any spreadsheet application.', 'erins-seed-catalog'); ?></p>
                    </div>
                </div>

                <div class="esc-export-filters">
                    <h3><?php esc_html_e('Filters (Optional)', 'erins-seed-catalog'); ?></h3>

                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc-filter-category">
                                <span class="dashicons dashicons-category"></span>
                                <?php esc_html_e('Category', 'erins-seed-catalog'); ?>
                            </label>
                            <select id="esc-filter-category" name="category_filter">
                                <option value=""><?php esc_html_e('All Categories', 'erins-seed-catalog'); ?></option>
                                <?php
                                // Get all categories
                                $terms = get_terms([
                                    'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
                                    'hide_empty' => false,
                                ]);

                                if (!empty($terms) && !is_wp_error($terms)) {
                                    foreach ($terms as $term) {
                                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <div class="esc-field-description">
                                <?php esc_html_e('Filter seeds by category to export only specific types of seeds.', 'erins-seed-catalog'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="esc-export-actions">
                    <button type="submit" id="esc-export-submit" class="esc-button esc-button-primary">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Export Catalog', 'erins-seed-catalog'); ?>
                    </button>
                    <div id="esc-export-status" class="esc-export-status" style="display: none;"></div>
                </div>

                <div class="esc-export-help">
                    <p>
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e('The export will include all seeds in your catalog with the selected columns. The file will download automatically when ready.', 'erins-seed-catalog'); ?>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
