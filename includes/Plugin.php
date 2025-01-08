<?php

namespace Supreme\ConditionalDiscounts;

use Supreme\ConditionalDiscounts\Admin\SettingsPage;
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
        $this->loader->add_filter('woocommerce_get_settings_pages', [$this, 'add_settings_page']);
    }

    private function define_admin_action_hooks() {
        $this->loader->add_action('admin_enqueue_scripts', function() {
            if (isset($_GET['page'], $_GET['tab'], $_GET['section']) &&
            $_GET['page'] === 'wc-settings' &&
            $_GET['tab'] === 'conditional_discounts' &&
            $_GET['section'] === '') {
                
            wp_enqueue_script(
                'cdwc-conditional-discounts-admin',
                CDWC_PLUGIN_URL . 'assets/js/admin-general-discounts.js',
                ['jquery'],  
                '1.0.0',     
                true         
            );
        }
        });


        $this->loader->add_action('admin_enqueue_scripts', function() {
            if (isset($_GET['page'], $_GET['tab'], $_GET['section']) &&
            $_GET['page'] === 'wc-settings' &&
            $_GET['tab'] === 'conditional_discounts' &&
            $_GET['section'] === 'cart_discounts') {
                
            wp_enqueue_script(
                'cdwc-cart-discounts-admin',
                CDWC_PLUGIN_URL . 'assets/js/admin-cart-discounts.js',
                ['jquery'],  
                '1.0.0',     
                true         
            );
        }
        });

        $this->loader->add_action('admin_enqueue_scripts', function() {
            if (isset($_GET['page'], $_GET['tab'], $_GET['section']) &&
            $_GET['page'] === 'wc-settings' &&
            $_GET['tab'] === 'conditional_discounts' &&
            $_GET['section'] === 'product_discounts') {
                
            wp_enqueue_script(
                'cdwc-product-discounts-admin',
                CDWC_PLUGIN_URL . 'assets/js/admin-product-discounts.js',
                ['jquery'],  
                '1.0.0',     
                true         
            );
        }
        });    

    }    
    
    public function add_settings_page($settings) {
        $settings[] = new SettingsPage();
        return $settings;
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
