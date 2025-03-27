<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Passed variables: $seeds (array), $action (string|null), $seed_to_edit (object|null)

// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) { // Adjust capability if needed
	return;
}

// Helper function to display category checkboxes recursively
function esc_display_category_checklist_item($term, $selected_cats, $level = 0) {
    $indent = str_repeat('&mdash; ', $level);
    $checked = in_array($term->term_id, $selected_cats) ? 'checked="checked"' : '';
    echo '<li>';
    echo '<label>';
    echo '<input type="checkbox" name="esc_seed_category[]" value="' . esc_attr($term->term_id) . '" ' . $checked . '> ';
    echo esc_html($indent . $term->name);
    echo '</label>';

    $children = get_terms([
        'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
        'parent' => $term->term_id,
        'hide_empty' => false,
        'orderby' => 'name',
    ]);

    if (!empty($children) && !is_wp_error($children)) {
        echo '<ul>';
        foreach ($children as $child) {
            esc_display_category_checklist_item($child, $selected_cats, $level + 1);
        }
        echo '</ul>';
    }
    echo '</li>';
}


?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php do_action('admin_notices'); // Display any notices added during processing ?>

    <?php if ( $action === 'edit' && $seed_to_edit ) : ?>
        <?php // --- EDIT SEED FORM ---
            // Get currently selected category term IDs for the seed being edited
            $selected_category_ids = [];
            if (isset($seed_to_edit->categories) && !empty($seed_to_edit->categories)) {
                 $selected_category_ids = wp_list_pluck($seed_to_edit->categories, 'term_id');
            }
        ?>
        <h2><?php printf( esc_html__( 'Edit Seed: %s', 'erins-seed-catalog' ), esc_html( $seed_to_edit->seed_name . ( $seed_to_edit->variety_name ? ' - ' . $seed_to_edit->variety_name : '' ) ) ); ?></h2>
        <p><a href="<?php echo esc_url( admin_url('admin.php?page=esc-manage-catalog') ); ?>">&laquo; <?php esc_html_e('Back to Catalog List', 'erins-seed-catalog'); ?></a></p>

        <form method="post" action="<?php echo esc_url( admin_url('admin.php?page=esc-manage-catalog') ); ?>" class="esc-admin-edit-form">
			<input type="hidden" name="seed_id" value="<?php echo esc_attr( $seed_to_edit->id ); ?>">
            <input type="hidden" name="action" value="update"> <?php // Or handle via submit button name ?>
            <?php wp_nonce_field( 'esc_edit_seed_action', 'esc_edit_seed_nonce' ); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <?php
                    // Loop through allowed fields and create form inputs
                    $allowed_fields = ESC_DB::get_allowed_fields();
                    foreach ($allowed_fields as $field => $type) :
                        $label = ucwords(str_replace('_', ' ', $field)); // Simple label generation
                        $value = $seed_to_edit->$field ?? ''; // Get current value

                        // Special handling for boolean (checkbox)
                        if ($type === 'bool') : ?>
                            <tr valign="top">
                                <th scope="row"><label for="esc_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
                                <td>
                                    <input type="checkbox" id="esc_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" value="1" <?php checked( (bool) $value ); ?>>
                                    <?php // Add description if needed ?>
                                </td>
                            </tr>
                        <?php // Handling for text areas
                        elseif ($type === 'text') : ?>
                            <tr valign="top">
                                <th scope="row"><label for="esc_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
                                <td>
                                    <textarea id="esc_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" rows="5" class="large-text"><?php echo esc_textarea($value); ?></textarea>
                                    <?php // Add description if needed ?>
                                </td>
                            </tr>
                         <?php // Handling for URL
                        elseif ($type === 'url') : ?>
                            <tr valign="top">
                                <th scope="row"><label for="esc_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
                                <td>
                                    <?php if ($field === 'image_url') : ?>
                                        <?php ESC_Image_Uploader::render($field, 'esc_' . $field, $value, $label); ?>
                                    <?php else : ?>
                                        <input type="url" id="esc_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_url($value); ?>" class="regular-text">
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php // Handling for date
                        elseif ($type === 'date') : ?>
                            <tr valign="top">
                                <th scope="row"><label for="esc_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
                                <td>
                                    <input type="date" id="esc_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text">
                                </td>
                            </tr>
                         <?php // Handling for integer
                        elseif ($type === 'int') : ?>
                            <tr valign="top">
                                <th scope="row"><label for="esc_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
                                <td>
                                    <input type="number" step="1" min="0" id="esc_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>" class="small-text">
                                </td>
                            </tr>
                        <?php // Default: text input
                        else : ?>
                            <tr valign="top">
                                <th scope="row"><label for="esc_<?php echo esc_attr($field); ?>"><?php echo esc_html($label); ?></label></th>
                                <td>
                                    <input type="text" id="esc_<?php echo esc_attr($field); ?>" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text">
                                    <?php // Add description if needed ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                     <?php // --- Category Selection --- ?>
                     <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Seed Categories', 'erins-seed-catalog' ); ?></th>
                        <td>
                            <div class="esc-category-checklist">
                                <ul>
                                    <?php
                                    $top_level_terms = get_terms([
                                        'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
                                        'parent' => 0,
                                        'hide_empty' => false,
                                        'orderby' => 'name',
                                    ]);

                                    if (!empty($top_level_terms) && !is_wp_error($top_level_terms)) {
                                        foreach ($top_level_terms as $term) {
                                            esc_display_category_checklist_item($term, $selected_category_ids);
                                        }
                                    } else {
                                        echo '<li>' . esc_html__('No categories found.', 'erins-seed-catalog') . '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <p class="description"><?php esc_html_e( 'Select one or more relevant categories.', 'erins-seed-catalog' ); ?> <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=' . ESC_Taxonomy::TAXONOMY_NAME)); ?>"><?php esc_html_e('Manage Categories', 'erins-seed-catalog'); ?></a></p>
                        </td>
                    </tr>

                </tbody>
            </table>

			<?php submit_button( __( 'Save Changes', 'erins-seed-catalog' ), 'primary', 'esc_submit_edit' ); ?>
		</form>


    <?php else : ?>
        <?php // --- SEED LIST TABLE --- ?>
         <h2><?php esc_html_e('Current Seed Catalog', 'erins-seed-catalog'); ?></h2>

         <div class="esc-export-button-container">
             <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=esc_export_seeds' ), 'esc_export_nonce' ) ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Export Catalog to CSV', 'erins-seed-catalog' ); ?>
            </a>
         </div>

         <?php if ( ! empty( $seeds ) ) : ?>
            <table class="wp-list-table widefat fixed striped esc-admin-table">
                <thead>
                    <tr>
                        <th scope="col" style="width:5%;"><?php esc_html_e( 'ID', 'erins-seed-catalog' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Seed Name', 'erins-seed-catalog' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Variety', 'erins-seed-catalog' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Brand', 'erins-seed-catalog' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Categories', 'erins-seed-catalog' ); ?></th>
                        <th scope="col" style="width:15%;"><?php esc_html_e( 'Date Added', 'erins-seed-catalog' ); ?></th>
                        <th scope="col" style="width:15%;"><?php esc_html_e( 'Actions', 'erins-seed-catalog' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $seeds as $seed ) : ?>
                        <tr>
                            <td><?php echo esc_html( $seed->id ); ?></td>
                            <td><?php echo esc_html( $seed->seed_name ); ?></td>
                            <td><?php echo esc_html( $seed->variety_name ?? 'N/A' ); ?></td>
                            <td><?php echo esc_html( $seed->brand ?? 'N/A' ); ?></td>
                             <td>
                                <?php
                                if (!empty($seed->categories)) {
                                    $cat_names = wp_list_pluck($seed->categories, 'name');
                                    echo esc_html(implode(', ', $cat_names));
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                             <td>
                                <?php
                                    $date_added_ts = strtotime($seed->date_added);
                                    echo esc_html( date_i18n( get_option('date_format') . ' ' . get_option('time_format'), $date_added_ts ) );
                                ?>
                            </td>
                            <td class="esc-actions">
                                <a href="<?php echo esc_url( add_query_arg( ['action' => 'edit', 'seed_id' => $seed->id], admin_url( 'admin.php?page=esc-manage-catalog' ) ) ); ?>" class="edit-link">
                                    <?php esc_html_e( 'Edit', 'erins-seed-catalog' ); ?>
                                </a>
                                |
                                <a href="#" class="delete-link" data-seed-id="<?php echo esc_attr( $seed->id ); ?>">
                                    <?php esc_html_e( 'Delete', 'erins-seed-catalog' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
             <?php // Add pagination here if implementing WP_List_Table or manual pagination ?>
         <?php else : ?>
             <p><?php esc_html_e( 'No seeds found in the catalog yet.', 'erins-seed-catalog' ); ?></p>
         <?php endif; ?>

    <?php endif; ?>

</div><!-- .wrap -->