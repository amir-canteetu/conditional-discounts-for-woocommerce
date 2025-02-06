<?php
namespace Supreme\ConditionalDiscounts\Discounts;


class Discount {
    
    public function __construct() {
        add_action('init', [$this, 'register_shop_discount_post_type']);
    }    
    
}
