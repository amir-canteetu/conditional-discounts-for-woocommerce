<?php

namespace Supreme\ConditionalDiscounts\Discounts;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class DiscountHandler
 *
 * Handles the application and management of conditional discounts in WooCommerce.
 */
class DiscountHandler {

    private $discounts = [];

    public function __construct() {
        $this->discounts[] = new GeneralDiscount();
        //$this->discounts[] = new CartDiscount();
        // $this->discounts[] = new ProductDiscount();        
    }    

    /**
     * Register hooks and filters for applying discounts.
     */
    public function register() {
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_discounts']);
    }

    /**
     * Apply cart discounts.
     *
     * Adds a custom fee or discount to the WooCommerce cart based on specific conditions.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     */
    public function apply_discounts($cart) {
        foreach ($this->discounts as $discount) {
            if ($discount->validate($cart)) {
                $discount->apply($cart);
            }
        }
    }


}
