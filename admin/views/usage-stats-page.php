<?php
/**
 * Admin usage statistics page template
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

// Get usage statistics
$usage_stats = get_option('esc_model_usage_stats', []);
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    
    <div class="esc-usage-stats-container">
        <?php if (empty($usage_stats)) : ?>
            <div class="notice notice-info">
                <p><?php _e('No usage statistics available yet. Test models or use the API to generate statistics.', 'erins-seed-catalog'); ?></p>
            </div>
        <?php else : ?>
            <div class="esc-usage-summary">
                <h2><?php _e('Usage Summary', 'erins-seed-catalog'); ?></h2>
                
                <div class="esc-usage-cards">
                    <?php
                    // Calculate totals
                    $total_calls = 0;
                    $total_tokens = 0;
                    $total_models = count($usage_stats);
                    
                    foreach ($usage_stats as $model => $stats) {
                        $total_calls += $stats['total_calls'];
                        $total_tokens += $stats['total_tokens'];
                    }
                    ?>
                    
                    <div class="esc-usage-card">
                        <h3><?php _e('Total API Calls', 'erins-seed-catalog'); ?></h3>
                        <div class="esc-usage-value"><?php echo number_format($total_calls); ?></div>
                    </div>
                    
                    <div class="esc-usage-card">
                        <h3><?php _e('Total Tokens Used', 'erins-seed-catalog'); ?></h3>
                        <div class="esc-usage-value"><?php echo number_format($total_tokens); ?></div>
                    </div>
                    
                    <div class="esc-usage-card">
                        <h3><?php _e('Models Used', 'erins-seed-catalog'); ?></h3>
                        <div class="esc-usage-value"><?php echo number_format($total_models); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="esc-usage-details">
                <h2><?php _e('Model Usage Details', 'erins-seed-catalog'); ?></h2>
                
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th><?php _e('Model', 'erins-seed-catalog'); ?></th>
                            <th><?php _e('API Calls', 'erins-seed-catalog'); ?></th>
                            <th><?php _e('Input Tokens', 'erins-seed-catalog'); ?></th>
                            <th><?php _e('Output Tokens', 'erins-seed-catalog'); ?></th>
                            <th><?php _e('Total Tokens', 'erins-seed-catalog'); ?></th>
                            <th><?php _e('Avg. Latency (ms)', 'erins-seed-catalog'); ?></th>
                            <th><?php _e('Last Used', 'erins-seed-catalog'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usage_stats as $model => $stats) : ?>
                            <tr>
                                <td><?php echo esc_html($model); ?></td>
                                <td><?php echo number_format($stats['total_calls']); ?></td>
                                <td><?php echo number_format($stats['total_input_tokens']); ?></td>
                                <td><?php echo number_format($stats['total_output_tokens']); ?></td>
                                <td><?php echo number_format($stats['total_tokens']); ?></td>
                                <td><?php echo round($stats['avg_latency']); ?></td>
                                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($stats['last_used'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="esc-usage-actions">
                <form method="post" action="">
                    <?php wp_nonce_field('esc_reset_usage_stats', 'esc_reset_usage_stats_nonce'); ?>
                    <input type="hidden" name="action" value="reset_usage_stats">
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset all usage statistics? This cannot be undone.', 'erins-seed-catalog'); ?>');">
                        <?php _e('Reset Statistics', 'erins-seed-catalog'); ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
