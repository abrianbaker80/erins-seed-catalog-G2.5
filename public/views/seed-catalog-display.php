<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Passed variables: $seeds (array), $paged (int), $total_pages (int), $initial_category_id (int)
// $seeds, $paged, $total_pages contain the *initial* server-side rendered data.
// AJAX will replace the content of #esc-catalog-results
?>

<div id="esc-catalog-view-container" class="esc-container">

    <?php // Include search form here if desired, or use separate shortcode ?>
    <?php // echo do_shortcode('[erins_seed_catalog_search]'); ?>
    <?php include( ESC_PLUGIN_DIR . 'public/views/seed-search-form.php' ); ?>


	<h2><?php esc_html_e( 'Seed Catalog', 'erins-seed-catalog' ); ?></h2>

	<div id="esc-catalog-results">
		<?php if ( ! empty( $seeds ) ) : ?>
			<div class="esc-seed-list">
				<?php foreach ( $seeds as $seed ) : ?>
					<?php include( ESC_PLUGIN_DIR . 'public/views/_seed-card.php' ); // Use the reusable card template ?>
				<?php endforeach; ?>
			</div>

			<?php if ($total_pages > 1) : ?>
                <div class="esc-pagination">
                    <?php
                    // Output pagination links for the initial load
                    echo paginate_links([
                        'base'      => get_permalink() . '%_%', // Use permalink structure
                        'format'    => 'page/%#%', // Or '?paged=%#%' based on permalink settings
                        'current'   => $paged,
                        'total'     => $total_pages,
                        'prev_text' => __('&laquo; Previous', 'erins-seed-catalog'),
                        'next_text' => __('Next &raquo;', 'erins-seed-catalog'),
                        // Use 'add_fragment' => '#esc-catalog-view-container' to jump to catalog on page load?
                    ]);
                    ?>
                </div>
            <?php endif; ?>

		<?php else : ?>
			<p class="esc-no-results"><?php esc_html_e( 'No seeds found in the catalog yet.', 'erins-seed-catalog' ); ?></p>
		<?php endif; ?>
	</div><!-- #esc-catalog-results -->

</div><!-- #esc-catalog-view-container -->

<!-- Seed Detail Modal -->
<div id="esc-seed-detail-modal" class="esc-modal" style="display: none;">
    <div class="esc-modal-content">
        <span class="esc-modal-close">&times;</span>
        <div id="esc-seed-detail-content"></div>
    </div>
</div>