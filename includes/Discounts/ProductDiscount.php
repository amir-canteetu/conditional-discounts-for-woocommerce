<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

class ProductDiscount implements DiscountInterface {

    private string  $endDate;  
    private string  $startDate;  
    private float   $discountCap;  
    private string  $minCartTotal;  
    private string  $maxCartTotal;     
    private string  $discountType; 
    private float   $discountValue;  
    private string  $discountLabel;  
    private string  $minCartQuantity;  
    private string  $enableDiscounts;  
    private array   $discountedProducts;  
    private array   $discountedCategories;  

    public function __construct() {

        $this->endDate              = get_option('cd_product_discount_end_date');   
        $this->startDate            = get_option('cd_product_discount_start_date');
        $this->discountValue        = (float) get_option('cd_product_discount_value');
        $this->minCartTotal         = floatval(get_option('cd_product_minimum_cart_total'));
        $this->discountType         = get_option('cd_product_discount_type', 'percentage');
        $this->discountLabel        = get_option('cd_product_discount_label', 'Product Discount');   
        $this->enableDiscounts      = get_option('cd_enable_product_discounts');
        $this->minCartQuantity      = intval(get_option('cd_product_min_cart_quantity'));   
        
    }    

    public function validate(WC_Cart $cart): bool {
        return $this->isApplicable($cart);
    }    
       
    public function isApplicable(WC_Cart $cart): bool {

        if ($this->enableDiscounts !== 'yes') { return false; }

        $currentDate = current_time('Y-m-d');
        if ((!empty($this->startDate) && $currentDate < $this->startDate) || (!empty($this->endDate) && $currentDate > $this->endDate)) { return false; }
        if ($cart->get_cart_contents_total() < $this->minCartTotal ) { return false;   }
        if ($cart->get_cart_contents_count() < $this->minCartQuantity ) { return false; }

        return true;
    }
           

    public function apply($cart): void {

        $discount_value = $this->calculateDiscount($cart);

        if ($discount_value > 0) {
            $cart->add_fee(__($this->discountLabel, 'cdwc'), -$discount_value);
        }
    }
    
    public function calculateDiscount(WC_Cart $cart): float {

        $discounted_products    = get_option('cd_select_discounted_products', []);
        $discounted_categories  = get_option('cd_select_discounted_categories', []);
        if ( $this->discountValue <= 0 || empty($discounted_products) && empty($discounted_categories)) {
            return 0.0;
        }

        $total_discount = 0.0;
    
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {

            $product_id         = $cart_item['product_id'];
            $product            = wc_get_product($product_id);
            $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
            $product_price      = $product->get_price();
            $quantity           = $cart_item['quantity'];
            $is_eligible        = in_array($product_id, $discounted_products, true) || array_intersect($discounted_categories, $product_categories);

            if ($is_eligible) {
                if ($this->discountType === 'percentage') {
                    $discount = ($product_price * $this->discountValue / 100) * $quantity;
                } else { 
                    $discount = $this->discountValue * $quantity;
                }
                $total_discount += $discount;
            }
        }

        return $total_discount;
    }    

    public function getDescription(): string {
        return __('Flat $10 off for carts over $100.', 'cdwc');
    }


}