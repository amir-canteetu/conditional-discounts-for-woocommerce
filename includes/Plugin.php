<?php


namespace Supreme\ConditionalDiscounts;

use Supreme\ConditionalDiscounts\Admin\AdminInterface;
use Supreme\ConditionalDiscounts\DiscountApplier;
use Supreme\ConditionalDiscounts\PluginActions;

if (!defined('ABSPATH')) { exit; }

class Plugin {

    
    public function __construct() {
       
    }
    
    
    public function initialize() {
        $this->register_services();
    }



    private function register_services() {
        
        (new AdminInterface());
        (new DiscountApplier());  
        (new PluginActions());  

    }
    
}