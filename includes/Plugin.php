<?php


namespace Supreme\ConditionalDiscounts;

use Supreme\ConditionalDiscounts\Admin\AdminInterface;
use Supreme\ConditionalDiscounts\DiscountApplier;

if (!defined('ABSPATH')) { exit; }

class Plugin {

    
    public function __construct() {
       
    }
    
    
    public function initialize() {
        $this->register_services();
        $this->register_hooks();
    }



    private function register_services() {
        
        (new AdminInterface());
        (new DiscountApplier());      

    }

    private function register_hooks() {
        
      
    }
    
}