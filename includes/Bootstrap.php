<?php

namespace Supreme\ConditionalDiscounts;

use Supreme\ConditionalDiscounts\Loader;
use Supreme\ConditionalDiscounts\Admin\Admin;
use Supreme\ConditionalDiscounts\Discounts\DiscountHandler;
use Supreme\ConditionalDiscounts\PostTypes\ShopDiscountType;
use Supreme\ConditionalDiscounts\Admin\DiscountListTable;
use Supreme\ConditionalDiscounts\Admin\RuleBuilder;


class Bootstrap {

    private $loader;
    private $admin;

    private static ?Bootstrap $instance = null;

    public function __construct() {
        $this->loader   = new Loader();
        $this->admin    = new Admin();
        $this->define_admin_filter_hooks(); 
        $this->define_admin_action_hooks();  
        $this->initialize_post_type();
        $this->initialize_services();
    }   

    public static function instance(): Bootstrap {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function initialize_post_type() {      
        (new ShopDiscountType());
    }     

    public function initialize_services() {    
        (new DiscountListTable());
        (new RuleBuilder());
    }

    private function define_admin_filter_hooks() {
        

    }
   

    private function define_admin_action_hooks() {
        //$this->loader->add_action('admin_enqueue_scripts', [$this->admin, 'cdwc_enqueue_admin_scripts']);
    }    

    public function run() {
        $this->loader->run();
    }

}
