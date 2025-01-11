<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

class CartDiscount implements DiscountInterface {

    private string $endDate;  
    private string $startDate;  
    private float   $discountValue;  
    private float   $discountCap;  
    private string $minCartTotal;  
    private string $maxCartTotal;     
    private string $discountType;  
    private string $discountLabel;  
    private string $minCartQuantity;  
    private string $enableDiscounts;  

    public function __construct() {

        $this->endDate              = get_option('cdwc_cart_discount_end_date', '');   
        $this->startDate            = get_option('cdwc_cart_discount_start_date', '');
        $this->discountValue        = (float) get_option('cdwc_cart_discount_value');
        $this->discountCap          = (float) get_option('cdwc_global_discount_cap');
        $this->minCartTotal         = floatval(get_option('cdwc_minimum_cart_total', 0));
        $this->discountType         = get_option('cdwc_cart_discount_type', 'percentage');
        $this->discountLabel        = get_option('cdwc_cart_discount_label', 'Cart Discount');   
        $this->enableDiscounts      = get_option('cdwc_enable_cart_discounts', 'no') === 'yes';
        $this->minCartQuantity      = intval(get_option('cdwc_cart_quantity_discount', 0));   
    }

    public function isApplicable(WC_Cart $cart): bool {

            if (!$this->enableDiscounts) {
                return false;
            }

            $cartTotal = $cart->get_subtotal();

            if ($cartTotal < $this->minCartTotal) {
                return false;
            }

            $cartQuantity = $cart->get_cart_contents_count();
            if ($cartQuantity < $this->minCartQuantity) {
                return false;
            }

            $currentDate = current_time('Y-m-d');
            if (!empty($this->startDate) && $currentDate < $this->startDate) {
                return false;
            }

            if (!empty($this->endDate) && $currentDate > $this->endDate) {
                return false;
            }
        
            return true;
    }    

    

    public function calculateDiscount(WC_Cart $cart): float {
         
        $cartSubtotal = $cart->get_subtotal();
        $calculatedDiscount = 0.0;
    
        if ($this->discountType === 'percentage') {
            $calculatedDiscount = ($cartSubtotal * $this->discountValue) / 100;
        } elseif ($this->discountType === 'fixed') {
            $calculatedDiscount = $this->discountValue;
        }
    
        $calculatedDiscount = min($calculatedDiscount, $cartSubtotal);
        return $calculatedDiscount;
    }
       

    public function apply($cart): void {
        if (!$this->validate($cart)) {
            return;
        }

        $discount_value = $this->calculateDiscount($cart);

        if ($discount_value > 0) {
            $cart->add_fee(__($this->discountLabel, 'cdwc'), -$discount_value);
        }
    }

    /**
     * Validates if the discount can be applied.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     * @return bool
     */
    public function validate(WC_Cart $cart): bool {
        return $this->isApplicable($cart);
    }

    public function getDescription(): string {
        return __('Cart discount based on cart total.', 'cdwc');
    }

}
