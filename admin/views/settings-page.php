<?php
/**
 * Admin settings page template
 *
 * @package    Erins_Seed_Catalog
 * @subpackage Admin/Views
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <form method="post" action="options.php">
        <?php
        // Output nonce, action, and option_page fields for the group.
        settings_fields( ESC_SETTINGS_OPTION_GROUP ); // Use the constant defined in the main plugin file

        // Output the settings sections and their fields for this page.
        do_settings_sections( 'erins-seed-catalog' ); // Use the page slug where sections/fields were added

        // Output save settings button
        submit_button();
        ?>
    </form>

    <hr>

    <h2><?php _e( 'Getting Started', 'erins-seed-catalog' ); ?></h2>
    <div class="esc-getting-started">
        <h3><?php _e( 'Quick Start Guide', 'erins-seed-catalog' ); ?></h3>
        <ol>
            <li><?php _e( 'Configure your preferred display settings above', 'erins-seed-catalog' ); ?></li>
            <li><?php _e( 'Add your Gemini API key if you want AI-powered planting suggestions', 'erins-seed-catalog' ); ?></li>
            <li><?php _e( 'Start adding seeds to your catalog', 'erins-seed-catalog' ); ?></li>
            <li><?php _e( 'Use shortcodes to display your catalog on any page', 'erins-seed-catalog' ); ?></li>
        </ol>

        <h3><?php _e( 'Available Shortcodes', 'erins-seed-catalog' ); ?></h3>
        <table class="widefat" style="max-width: 800px;">
            <thead>
                <tr>
                    <th><?php _e( 'Shortcode', 'erins-seed-catalog' ); ?></th>
                    <th><?php _e( 'Description', 'erins-seed-catalog' ); ?></th>
                    <th><?php _e( 'Example', 'erins-seed-catalog' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>[seed_catalog]</code></td>
                    <td><?php _e( 'Display your complete seed catalog', 'erins-seed-catalog' ); ?></td>
                    <td><code>[seed_catalog limit="12" category="vegetables"]</code></td>
                </tr>
                <tr>
                    <td><code>[seed_search]</code></td>
                    <td><?php _e( 'Add a search form for your seeds', 'erins-seed-catalog' ); ?></td>
                    <td><code>[seed_search placeholder="Find seeds..."]</code></td>
                </tr>
                <tr>
                    <td><code>[seed_categories]</code></td>
                    <td><?php _e( 'Display a list of seed categories', 'erins-seed-catalog' ); ?></td>
                    <td><code>[seed_categories show_count="yes"]</code></td>
                </tr>
                <tr>
                    <td><code>[add_seed_form]</code></td>
                    <td><?php _e( 'Display a form for adding new seeds (admin only)', 'erins-seed-catalog' ); ?></td>
                    <td><code>[add_seed_form title="Add New Seed"]</code></td>
                </tr>
            </tbody>
        </table>

        <h3><?php _e( 'Need Help?', 'erins-seed-catalog' ); ?></h3>
        <p>
            <?php _e( 'For more information:', 'erins-seed-catalog' ); ?>
            <ul>
                <li><a href="https://github.com/your-repo/erins-seed-catalog" target="_blank"><?php _e( 'Documentation', 'erins-seed-catalog' ); ?></a></li>
                <li><a href="https://github.com/your-repo/erins-seed-catalog/issues" target="_blank"><?php _e( 'Support', 'erins-seed-catalog' ); ?></a></li>
            </ul>
        </p>
    </div>
</div>
