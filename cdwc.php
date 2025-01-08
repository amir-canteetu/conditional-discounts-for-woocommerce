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
    'CDWC_PLUGIN_VERSION' => '1.0.0',
];

foreach ($constants as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

require_once __DIR__ . '/vendor/autoload.php';

function activate_cdwc() {
    Supreme\ConditionalDiscounts\Activator::activate();
}

function deactivate_cdwc() {
	Supreme\ConditionalDiscounts\DeActivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cdwc' );
register_deactivation_hook( __FILE__, 'deactivate_cdwc' );

function cdwc_load_textdomain() {
    load_plugin_textdomain('cdwc', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'cdwc_load_textdomain');

function cdwc_initialize_plugin() {
    Supreme\ConditionalDiscounts\Plugin::instance()->run();
}
add_action('plugins_loaded', 'cdwc_initialize_plugin');
