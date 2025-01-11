<?php

namespace Supreme\ConditionalDiscounts;

use Supreme\ConditionalDiscounts\Loader;
use Supreme\ConditionalDiscounts\Admin\Admin;
use Supreme\ConditionalDiscounts\Discounts\DiscountHandler;


class Plugin {

    private $loader;

    private static ?Plugin $instance = null;

    public function __construct() {
        $this->loader = new Loader();
        
        $this->define_admin_filter_hooks(); 
        $this->define_admin_action_hooks();  
        $this->define_public_hooks();  
        $this->initialize_services();
    }

    public static function instance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function initialize_services() {      
        (new DiscountHandler())->register();
    }

    private function define_admin_filter_hooks() {

        $this->loader->add_filter('woocommerce_get_settings_pages', function($settings){
            $settings[] = include __DIR__ . '/Admin/SettingsPage.php';
            return $settings;        
        });

        $this->loader->add_filter('plugin_action_links_conditional-discounts-for-woocommerce/cdwc.php', [new Admin(), 'cdwc_add_settings_link']);
    }
   

    private function define_admin_action_hooks() {
        $this->loader->add_action('admin_enqueue_scripts', [new Admin(), 'cdwc_enqueue_admin_scripts']);
    }    
    


    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
    } 

    public function run() {
        $this->loader->run();
    }

}
