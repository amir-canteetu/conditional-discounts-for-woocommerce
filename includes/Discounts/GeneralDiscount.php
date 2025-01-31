<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

class GeneralDiscount implements DiscountInterface {

    private float $discountValue;  
    private float $discountCap;  
    private string $discountType;  
    private string $discountLabel;  
    private bool $combinability;
    private string $minCartTotal;  
    private string $maxCartTotal;  

    public function __construct() {
        $this->discountValue    = (float) get_option('cdwc_general_discount_value');
        $this->discountType     = get_option('cdwc_general_discount_type', 'percentage');
        $this->discountCap      = (float) get_option('cdwc_general_discounts_discount_cap');
        $this->discountLabel    = get_option('cdwc_general_discounts_discount_label', 'Store-wide Discount');      
        $this->combinability    = get_option('cdwc_general_discounts_combinability') == "yes" ? true : false;  
        $this->minCartTotal     = floatval(get_option('cdwc_general_discounts_minimum_cart_total', 0));
        $this->minCartQuantity  = intval(get_option('cdwc_general_discounts_minimum_cart_quantity', 0));   

    }

    public function isEnabled( ): bool {
            return get_option('cdwc_enable_general_discounts') == 'yes' ? true : false;
    }

    /**
     * Determines if the discount is applicable.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     * @return bool
     */
    public function isApplicable(WC_Cart $cart): bool {
        
        $enable_general_discounts = get_option('cdwc_enable_general_discounts', 'no');
        if ($enable_general_discounts !== 'yes') {
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
    
        $current_date = current_time('Y-m-d');
    
        $start_date = get_option('cdwc_general_discount_start_date', '');
        if (!empty($start_date) && $current_date < $start_date) {
            return false;
        }
    
        $end_date = get_option('cdwc_general_discount_end_date', '');
        if (!empty($end_date) && $current_date > $end_date) {
            return false;
        }
    
        // If all checks pass, the discount is applicable
        return true;
    }

    /**
     * Calculates the discount amount.
     *
     * @return float
     */
public function calculateDiscount(WC_Cart $cart): float {
     
        $cartTotal = (float) $cart->get_cart_contents_total();
        if ($cartTotal <= 0) { return 0.0; }
        $discount = 0.0;

        if ($this->discountType === 'percentage') {
            $discount = $cartTotal * ($this->discountValue / 100);

            if ($this->discountCap > 0) {
                $discount = min($discount, $this->discountCap);
            }
        } elseif ($this->discountType === 'fixed') {
            $discount = $this->discountValue;

            // Ensure fixed discount does not exceed cart total
            $discount = min($discount, $cartTotal);
        } else {
            // Unsupported discount type
            return 0.0;
        }

        // Ensure discount is a non-negative value
        return max($discount, 0.0);
}

    /**
     * Applies the discount to the WooCommerce cart.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     * @return void
     */
    public function apply($cart): void {
        $discountAmount = $this->calculateDiscount($cart);
        if ($discountAmount > 0) {
            $cart->add_fee( $this->discountLabel, -$discountAmount );
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


    public function isCombinable(): bool {
        return $this->combinability;
    }

    
}
