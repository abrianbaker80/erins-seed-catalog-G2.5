<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// This form is primarily controlled by public JS for AJAX updates.
// Values might be pre-filled by JS or initial page load context if needed.
?>
<div id="esc-search-container">
    <form id="esc-search-form" class="esc-form" method="get" action="<?php /* echo esc_url(home_url('/')); */ // Action URL not strictly needed for AJAX ?>" aria-label="<?php esc_attr_e('Search seed catalog', 'erins-seed-catalog'); ?>" role="search">
         <div class="esc-search-input-wrapper">
             <label for="esc-search-input" class="screen-reader-text"><?php esc_html_e( 'Search Seeds:', 'erins-seed-catalog' ); ?></label>
             <input type="search" id="esc-search-input" name="s_seed" value="<?php echo esc_attr( get_query_var('s_seed', '') ); ?>" placeholder="<?php esc_attr_e( 'Search name, variety...', 'erins-seed-catalog' ); ?>">
             <!-- Clear button will be added by JS -->
         </div>

        <label for="esc-filter-category" class="screen-reader-text"><?php esc_html_e( 'Filter by Category:', 'erins-seed-catalog' ); ?></label>
        <select id="esc-filter-category" name="seed_category_filter">
            <option value=""><?php esc_html_e( 'All Categories', 'erins-seed-catalog' ); ?></option>
             <?php
                // Use WP's walker for dropdown or our custom function if needed
                /* wp_dropdown_categories( array(
                    'show_option_all' => __( 'All Categories', 'erins-seed-catalog' ),
                    'taxonomy'        => ESC_Taxonomy::TAXONOMY_NAME,
                    'name'            => 'seed_category_filter',
                    'orderby'         => 'name',
                    'hierarchical'    => true,
                    'hide_empty'      => false,
                    'value_field'     => 'term_id',
                    'id'              => 'esc-filter-category',
                    // 'selected'        => $initial_category_id ?? 0, // Pre-select if needed from context
                ) ); */
                 // Use our custom function to get hierarchical options
                 echo ESC_Taxonomy::get_category_dropdown_options( get_query_var('cat', 0) ); // Pass currently selected if available
             ?>
        </select>

        <button type="submit" class="esc-button"><?php esc_html_e( 'Search', 'erins-seed-catalog' ); ?></button>
        <button type="reset" class="esc-button esc-button-secondary esc-reset-button"><?php esc_html_e( 'Reset', 'erins-seed-catalog' ); ?></button>
    </form>
</div>