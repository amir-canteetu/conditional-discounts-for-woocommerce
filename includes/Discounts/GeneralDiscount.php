<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

class GeneralDiscount implements DiscountInterface {

    private float $discountValue;  
    private float $discountCap;  
    private string $discountType;  
    private string $discountLabel;  
    private bool $combinability;

    public function __construct() {
        $this->discountValue    = (float) get_option('cdwc_general_discount_value');
        $this->discountType     = get_option('cdwc_general_discount_type', 'percentage');
        $this->discountCap      = (float) get_option('cdwc_global_discount_cap');
        $this->discountLabel    = get_option('cdwc_global_discount_label', 'Store-wide Discount');      
        $this->combinability    = get_option('cdwc_discount_combinability') == "yes" ? true : false;     
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
            $cart->add_fee(
                __($this->discountLabel, 'conditional-discounts'),
                -$discountAmount  
            );
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

    /**
     * Provides a description of the discount.
     *
     * @return string
     */
    public function getDescription(): string {
        if ($this->discountType === 'percentage') {
            return sprintf(
                __('%s%% off for carts over $100.', 'conditional-discounts'),
                $this->discountValue
            );
        }

        return sprintf(
            __('$%s off for carts over $100.', 'conditional-discounts'),
            number_format($this->discountValue, 2)
        );
    }


    public function isCombinable(): bool {
        return $this->combinability;
    }

    
}
