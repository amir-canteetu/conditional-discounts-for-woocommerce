<?php
/**
 * Plugin Name: Conditional Discounts for WooCommerce
 * Plugin URI: https://github.com/amir-canteetu/conditional-discounts-for-woocommerce
 * Description: Advanced conditional discount rules for WooCommerce
 * Version: 1.0.0
 * Author: Amir Candido
 * License: GPL-2.0+
 * Text Domain: conditional-discounts
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 8.0" 
 */

defined('ABSPATH') || exit;

define('CDWC_VERSION', '1.0.0');
define('CDWC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CDWC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once __DIR__.'/vendor/autoload.php';

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

add_action('woocommerce_loaded', function() {

     (new Supreme\ConditionalDiscounts\Plugin())->initialize();
    
});
