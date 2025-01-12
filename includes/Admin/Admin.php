<?php

namespace Supreme\ConditionalDiscounts\Admin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}




class Admin {


    /**
     * Add a "Settings" link to the plugin actions.
     *
     * @param array  
     * @return array  
     */
    public function cdwc_add_settings_link( $links ) {

        $settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=conditional_discounts&section' ) . '">' . __( 'Settings', 'cdwc' ) . '</a>';
        array_unshift( $links, $settings_link );

        return $links;
    } 

    public function cdwc_enqueue_admin_scripts() {

        if (isset($_GET['page'], $_GET['tab'], $_GET['section']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'conditional_discounts' && $_GET['section'] === '') {
                
            wp_enqueue_script(
                'cdwc-conditional-discounts-admin',
                CDWC_PLUGIN_URL . 'assets/js/admin-general-discounts.js',
                ['jquery'],  
                CDWC_PLUGIN_VERSION,     
                true         
            );
        }

        if (isset($_GET['page'], $_GET['tab'], $_GET['section']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'conditional_discounts' && $_GET['section'] === 'cart_discounts') {
                
            wp_enqueue_script(
                'cdwc-cart-discounts-admin',
                CDWC_PLUGIN_URL . 'assets/js/admin-cart-discounts.js',
                ['jquery'],  
                CDWC_PLUGIN_VERSION,     
                true         
            );

        }  
        
        
        if (isset($_GET['page'], $_GET['tab'], $_GET['section']) && $_GET['page'] === 'wc-settings' && $_GET['tab'] === 'conditional_discounts' && $_GET['section'] === 'product_discounts') {
                
            wp_enqueue_script(
                'cdwc-product-discounts-admin',
                CDWC_PLUGIN_URL . 'assets/js/admin-product-discounts.js',
                ['jquery'],  
                CDWC_PLUGIN_VERSION,     
                true         
            );

        }        


    }     

}
















