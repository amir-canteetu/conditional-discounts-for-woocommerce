<?php

namespace Supreme\ConditionalDiscounts\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}




class Admin {


    /**
     * Add a "Settings" link to the plugin actions.
     *
     * @param array $links The existing array of action links for the plugin.
     * @return array The modified array of action links.
     */
    public function cdwc_add_settings_link($links) {
        // Dynamically generate the admin URL for the settings page.
        $settings_link = '<a href="' . esc_url(add_query_arg([
            'page' => 'wc-settings',
            'tab'  => 'conditional_discounts',
            'section' => '',
        ], admin_url('admin.php'))) . '">' . esc_html__('Settings', 'cdwc') . '</a>';

        // Add the settings link to the beginning of the links array.
        array_unshift($links, $settings_link);

        return $links;
    }

    public function cdwc_enqueue_admin_scripts() {
        // Ensure required constants are defined
        if (!defined('CDWC_PLUGIN_URL') || !defined('CDWC_PLUGIN_VERSION')) {
            return; // Exit if critical constants are not defined
        }
    
        // Sanitize GET parameters
        $page    = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        $tab     = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
        $section = isset($_GET['section']) ? sanitize_text_field(wp_unslash($_GET['section'])) : '';
    
        // Define valid sections and their corresponding scripts
        $sections = [
            '' => 'admin-general-discounts.js',          // General discounts
            'cart_discounts' => 'admin-cart-discounts.js',  // Cart discounts
            'product_discounts' => 'admin-product-discounts.js', // Product discounts
        ];
    
        // Check if we are on the correct admin page and tab
        if ($page === 'wc-settings' && $tab === 'conditional_discounts' && array_key_exists($section, $sections)) {
            // Enqueue the relevant script for the section
            wp_enqueue_script(
                'cdwc-' . ($section ?: 'general') . '-admin', // Dynamic handle based on section
                CDWC_PLUGIN_URL . 'assets/js/' . $sections[$section], // script path
                ['jquery'], // Dependencies
                CDWC_PLUGIN_VERSION, // Version
                true // Load in the footer
            );
        }
    }    

}
















