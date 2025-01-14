<?php
/**
 * Plugin Name: Conditional Discounts for WooCommerce
 * Description: A plugin to create cart-based conditional discounts.
 * Version: 1.0.0
 * Author: Amir Candido
 * Text Domain: conditional-discounts-for-woocommerce
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

// Define constants for plugin paths and URLs
$constants = [
    'CDWC_PLUGIN_FILE'   => __FILE__,                          // Path to the main plugin file
    'CDWC_PLUGIN_DIR'    => plugin_dir_path(__FILE__),         // Absolute directory path of the plugin
    'CDWC_PLUGIN_URL'    => plugin_dir_url(__FILE__),          // URL to the plugin directory
];
// Define constants only if they are not already defined
foreach ($constants as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

// Retrieve the plugin version dynamically from the plugin header
$plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], 'plugin');
define('CDWC_PLUGIN_VERSION', $plugin_data['Version']);

// Autoload classes using Composer's autoloader
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
    load_plugin_textdomain('conditional-discounts-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'cdwc_load_textdomain');

function cdwc_initialize_plugin() {
    Supreme\ConditionalDiscounts\Plugin::instance()->run(); //loader->run();
}
add_action('plugins_loaded', 'cdwc_initialize_plugin');
