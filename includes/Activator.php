<?php

namespace Supreme\ConditionalDiscounts;

use Supreme\ConditionalDiscounts\Db;

class Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
		$this->checkForWC();
                Db::create_table_multisite();
	}
        
	public function checkForWC() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			// Deactivate the plugin
			deactivate_plugins( plugin_basename( CDWC_PLUGIN_FILE ) );
	
			// Display an admin notice
			wp_die(
				'<div style="font-family: Arial, sans-serif; padding: 20px; max-width: 600px;">
					<h1>Plugin Activation Error</h1>
					<p><strong>Conditional Discounts for WooCommerce</strong> requires WooCommerce to be installed and active.</p>
					<p><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">Return to Plugins</a></p>
				</div>'
			);
		}		

	} 

}
