<?php

namespace Supreme\ConditionalDiscounts;

use Supreme\ConditionalDiscounts\Loader;
use Supreme\ConditionalDiscounts\Admin\Admin;
use Supreme\ConditionalDiscounts\Discounts\DiscountHandler;


class Plugin {

    private $loader;
    private $admin;

    private static ?Plugin $instance = null;

    public function __construct() {
        $this->loader = new Loader();
        $this->admin = new Admin();
        $this->define_admin_filter_hooks(); 
        $this->define_admin_action_hooks();  
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
            $settings[] = include CDWC_PLUGIN_DIR . 'includes/Admin/SettingsPage.php';
            return $settings;        
        });
        
        $this->loader->add_filter('plugin_action_links_cdwc/cdwc.php', [$this->admin, 'cdwc_add_settings_link']);
    }
   

    private function define_admin_action_hooks() {
        $this->loader->add_action('admin_enqueue_scripts', [$this->admin, 'cdwc_enqueue_admin_scripts']);
    }    

    public function run() {
        $this->loader->run();
    }

}
