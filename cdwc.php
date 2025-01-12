<?php
/**
 * Plugin Name: Conditional Discounts for WooCommerce
 * Description: A plugin to create cart-based conditional discounts.
 * Version: 1.0.0
 * Author: Amir Candido
 * Text Domain: cdwc
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

$constants = [
    'CDWC_PLUGIN_FILE'   => __FILE__,
    'CDWC_PLUGIN_DIR'    => plugin_dir_path(__FILE__),
    'CDWC_PLUGIN_URL'    => plugin_dir_url(__FILE__),
];

foreach ($constants as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

$plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], 'plugin');
define('CDWC_PLUGIN_VERSION', $plugin_data['Version']);

require_once __DIR__ . '/vendor/autoload.php';

function cdwc_activate_plugin() {
    Supreme\ConditionalDiscounts\Activator::activate();
}

function cdwc_deactivate_plugin() {
	Supreme\ConditionalDiscounts\DeActivator::deactivate();
}

register_activation_hook( __FILE__, 'cdwc_activate_plugin' );
register_deactivation_hook( __FILE__, 'cdwc_deactivate_plugin' );

function cdwc_load_textdomain() {
    load_plugin_textdomain('cdwc', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'cdwc_load_textdomain');

function cdwc_initialize_plugin() {
    Supreme\ConditionalDiscounts\Plugin::instance()->run(); //loader->run();
}
add_action('plugins_loaded', 'cdwc_initialize_plugin');
