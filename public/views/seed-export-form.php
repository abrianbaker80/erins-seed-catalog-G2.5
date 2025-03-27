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

// Format field names for display
$field_display_names = [];
foreach ($all_fields as $field => $type) {
    // Convert snake_case to Title Case
    $display_name = ucwords(str_replace('_', ' ', $field));
    $field_display_names[$field] = $display_name;
}

// Default selected fields (commonly used fields)
$default_selected = [
    'seed_name', 'variety_name', 'plant_type', 'growth_habit', 
    'days_to_maturity', 'sunlight', 'categories'
];
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
                            <?php esc_html_e('Select All', 'erins-seed-catalog'); ?>
                        </button>
                        <button type="button" id="esc-select-none-columns" class="esc-button esc-button-secondary">
                            <?php esc_html_e('Select None', 'erins-seed-catalog'); ?>
                        </button>
                        <button type="button" id="esc-select-common-columns" class="esc-button esc-button-secondary">
                            <?php esc_html_e('Common Fields', 'erins-seed-catalog'); ?>
                        </button>
                    </div>
                    
                    <div class="esc-columns-grid">
                        <?php foreach ($field_display_names as $field => $display_name) : ?>
                            <div class="esc-column-option">
                                <label>
                                    <input type="checkbox" name="export_columns[]" value="<?php echo esc_attr($field); ?>" 
                                        <?php checked(in_array($field, $default_selected)); ?>>
                                    <?php echo esc_html($display_name); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="esc-export-format">
                    <h3><?php esc_html_e('Export Format', 'erins-seed-catalog'); ?></h3>
                    <div class="esc-format-options">
                        <label>
                            <input type="radio" name="export_format" value="csv" checked>
                            <?php esc_html_e('CSV (Excel)', 'erins-seed-catalog'); ?>
                        </label>
                    </div>
                </div>
                
                <div class="esc-export-filters">
                    <h3><?php esc_html_e('Filters (Optional)', 'erins-seed-catalog'); ?></h3>
                    
                    <div class="esc-form-row">
                        <div class="esc-form-field">
                            <label for="esc-filter-category"><?php esc_html_e('Category', 'erins-seed-catalog'); ?></label>
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
            </div>
        </form>
    </div>
</div>
