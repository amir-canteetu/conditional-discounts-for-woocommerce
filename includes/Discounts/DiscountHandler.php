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
        $this->discounts[] = new UserDiscount();          
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
        $generalDiscount = null;

        // Identify the GeneralDiscount object
        foreach ($this->discounts as $discount) {
            if ($discount instanceof GeneralDiscount) {
                $generalDiscount = $discount;
                break;
            }
        }

        // Check GeneralDiscount's status and combinability
        if ($generalDiscount && $generalDiscount->isEnabled()) {
            if ($generalDiscount->isCombinable()) {
                // Apply GeneralDiscount and others
                foreach ($this->discounts as $discount) {
                    if ($discount->validate($cart)) {
                        $discount->apply($cart);
                    }
                }
            } else {
                // Apply only GeneralDiscount and exit
                if ($generalDiscount->validate($cart)) {
                    $generalDiscount->apply($cart);
                }
                return;
            }
        } else {
            // Apply other discounts when GeneralDiscount is not enabled
            foreach ($this->discounts as $discount) {
                if (!($discount instanceof GeneralDiscount) && $discount->validate($cart)) {
                    $discount->apply($cart);
                }
            }
        }
    }
    


}
