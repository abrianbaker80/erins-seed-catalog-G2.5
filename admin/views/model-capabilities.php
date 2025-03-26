<?php
/**
 * Admin model capabilities template
 *
 * @package    Erins_Seed_Catalog
 * @subpackage Admin/Views
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get the current model
$current_model = get_option(ESC_GEMINI_MODEL_OPTION, 'gemini-2.0-flash-lite');

// Get the API key
$api_key = get_option(ESC_API_KEY_OPTION, '');

// Get model capabilities if API key is set
$capabilities = [];
if (!empty($api_key)) {
    $capabilities = get_transient('esc_model_capabilities_' . $current_model);
    
    // If capabilities are not cached, fetch them
    if (false === $capabilities) {
        $capabilities = ESC_Ajax::get_model_capabilities($current_model, $api_key);
        
        // Cache the capabilities for 24 hours
        set_transient('esc_model_capabilities_' . $current_model, $capabilities, DAY_IN_SECONDS);
    }
}
?>

<div class="esc-model-capabilities-wrapper">
    <h2><?php _e('Model Capabilities', 'erins-seed-catalog'); ?></h2>
    
    <?php if (empty($api_key)) : ?>
        <div class="notice notice-warning inline">
            <p><?php _e('Please enter your API key to view model capabilities.', 'erins-seed-catalog'); ?></p>
        </div>
    <?php elseif (empty($capabilities) || empty($capabilities['supportedGenerationMethods'])) : ?>
        <div class="notice notice-info inline">
            <p><?php _e('Model capabilities information is not available. Try testing the model connection.', 'erins-seed-catalog'); ?></p>
        </div>
    <?php else : ?>
        <div class="esc-capabilities-grid">
            <!-- Model Information -->
            <div class="esc-capability-card">
                <h3><?php _e('Model Information', 'erins-seed-catalog'); ?></h3>
                <table class="widefat">
                    <tr>
                        <th><?php _e('Model ID', 'erins-seed-catalog'); ?></th>
                        <td><code><?php echo esc_html($current_model); ?></code></td>
                    </tr>
                    <?php if (!empty($capabilities['displayName'])) : ?>
                    <tr>
                        <th><?php _e('Display Name', 'erins-seed-catalog'); ?></th>
                        <td><?php echo esc_html($capabilities['displayName']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($capabilities['description'])) : ?>
                    <tr>
                        <th><?php _e('Description', 'erins-seed-catalog'); ?></th>
                        <td><?php echo esc_html($capabilities['description']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($capabilities['version'])) : ?>
                    <tr>
                        <th><?php _e('Version', 'erins-seed-catalog'); ?></th>
                        <td><?php echo esc_html($capabilities['version']); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Token Limits -->
            <div class="esc-capability-card">
                <h3><?php _e('Token Limits', 'erins-seed-catalog'); ?></h3>
                <table class="widefat">
                    <tr>
                        <th><?php _e('Input Limit', 'erins-seed-catalog'); ?></th>
                        <td>
                            <?php 
                            if (!empty($capabilities['inputTokenLimit'])) {
                                echo number_format($capabilities['inputTokenLimit']);
                            } else {
                                echo esc_html__('Unknown', 'erins-seed-catalog');
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Output Limit', 'erins-seed-catalog'); ?></th>
                        <td>
                            <?php 
                            if (!empty($capabilities['outputTokenLimit'])) {
                                echo number_format($capabilities['outputTokenLimit']);
                            } else {
                                echo esc_html__('Unknown', 'erins-seed-catalog');
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Total Limit', 'erins-seed-catalog'); ?></th>
                        <td>
                            <?php 
                            if (!empty($capabilities['tokenLimit'])) {
                                echo esc_html($capabilities['tokenLimit']);
                            } else {
                                echo esc_html__('Unknown', 'erins-seed-catalog');
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Temperature Range -->
            <div class="esc-capability-card">
                <h3><?php _e('Temperature Range', 'erins-seed-catalog'); ?></h3>
                <?php if (!empty($capabilities['temperatureRange'])) : ?>
                <table class="widefat">
                    <tr>
                        <th><?php _e('Minimum', 'erins-seed-catalog'); ?></th>
                        <td><?php echo esc_html($capabilities['temperatureRange']['min']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Maximum', 'erins-seed-catalog'); ?></th>
                        <td><?php echo esc_html($capabilities['temperatureRange']['max']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Default', 'erins-seed-catalog'); ?></th>
                        <td><?php echo esc_html($capabilities['temperatureRange']['default'] ?? '0.7'); ?></td>
                    </tr>
                </table>
                <p class="description">
                    <?php _e('Temperature controls randomness. Lower values are more deterministic, higher values are more creative.', 'erins-seed-catalog'); ?>
                </p>
                <?php else : ?>
                <p><?php _e('Temperature range information not available.', 'erins-seed-catalog'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Supported Generation Methods -->
            <div class="esc-capability-card">
                <h3><?php _e('Supported Generation Methods', 'erins-seed-catalog'); ?></h3>
                <?php if (!empty($capabilities['supportedGenerationMethods'])) : ?>
                <ul class="esc-methods-list">
                    <?php foreach ($capabilities['supportedGenerationMethods'] as $method) : ?>
                    <li><code><?php echo esc_html($method); ?></code></li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                <p><?php _e('No generation methods information available.', 'erins-seed-catalog'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <p class="description">
            <?php _e('These capabilities determine how the model can be used and what parameters are available when making API requests.', 'erins-seed-catalog'); ?>
        </p>
        
        <p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=esc-usage-stats')); ?>" class="button">
                <?php _e('View Usage Statistics', 'erins-seed-catalog'); ?>
            </a>
        </p>
    <?php endif; ?>
</div>
