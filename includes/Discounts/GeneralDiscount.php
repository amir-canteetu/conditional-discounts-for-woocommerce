<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

class GeneralDiscount implements DiscountInterface {

    private float $cartTotal        = 0.0;
    private float $percentage       = 10.0;  
    private float $discountCap      = 0.0;  
    private float $fixedAmount      = 0.0;  
    private string $discountType    = 'percentage';  

    public function __construct() {
        // Load settings from WooCommerce options
        $this->percentage   = (float) get_option('cd_general_discount_value', 10.0);
        $this->discountType = get_option('cd_general_discount_type', 'percentage');
        $this->discountCap  = (float) get_option('cd_global_discount_cap', 0.0);
    }

    /**
     * Determines if the discount is applicable.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     * @return bool
     */
    public function isApplicable(WC_Cart $cart): bool {
        // Check if General Discounts are enabled
        $enable_general_discounts = get_option('cd_enable_general_discounts', 'no');
        if ($enable_general_discounts !== 'yes') {
            return false;
        }
    
        // Get the current date
        $current_date = current_time('Y-m-d');
    
        // Check the start date
        $start_date = get_option('cd_general_discount_start_date', '');
        if (!empty($start_date) && $current_date < $start_date) {
            return false;
        }
    
        // Check the end date
        $end_date = get_option('cd_general_discount_end_date', '');
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
    public function calculateDiscount(): float {
        $discount = 0.0;

        if ($this->discountType === 'percentage') {
            $discount = $this->cartTotal * ($this->percentage / 100);
        } elseif ($this->discountType === 'fixed') {
            $discount = $this->fixedAmount;
        }

        // Apply discount cap if set
        if ($this->discountCap > 0) {
            $discount = min($discount, $this->discountCap);
        }

        return $discount;
    }

    /**
     * Applies the discount to the WooCommerce cart.
     *
     * @param WC_Cart $cart The WooCommerce cart object.
     * @return void
     */
    public function apply($cart): void {
        $discountAmount = $this->calculateDiscount();

        if ($discountAmount > 0) {
            $cart->add_fee(
                __('Store-wide Discount', 'conditional-discounts'),
                -$discountAmount // WooCommerce "fee" supports negative values as discounts
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
                $this->percentage
            );
        }

        return sprintf(
            __('$%s off for carts over $100.', 'conditional-discounts'),
            number_format($this->fixedAmount, 2)
        );
    }
}
