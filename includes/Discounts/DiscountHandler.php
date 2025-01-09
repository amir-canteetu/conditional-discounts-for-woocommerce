<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

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
        $this->discounts[] = new CartDiscount();
        $this->discounts[] = new ProductDiscount();        
    }    

    /**
     * Register hooks and filters for applying discounts.
     */
    public function register() {
        add_action('woocommerce_cart_calculate_fees', [$this, 'applyDiscounts']);
    }

    /**
     * Apply cart discounts.
     *
     * Adds a custom fee or discount to the WooCommerce cart based on specific conditions.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     */
    public function applyDiscounts(WC_Cart $cart): void {
        foreach ($this->discounts as $discount) {
            if ($discount instanceof DiscountInterface && $discount->validate($cart)) {
                $discount->apply($cart);
            }
        }
    }


}
