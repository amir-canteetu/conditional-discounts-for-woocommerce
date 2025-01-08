<?php

namespace Supreme\ConditionalDiscounts\Discounts;

class ProductDiscount implements DiscountInterface {

    public function apply($context): void {
        $cart = $context; // Assume $context is WC_Cart
        $cart->add_fee(__('Product Discount', 'cdwc'), -10); // Example: Apply a flat $10 discount
    }

    public function validate($context): bool {
        $cart = $context; // Assume $context is WC_Cart
        return $cart->get_subtotal() >= 100; // Example: Apply discount only if cart total >= $100
    }

    public function getDescription(): string {
        return __('Flat $10 off for carts over $100.', 'cdwc');
    }

    /**
     * Apply product discounts.
     *
     * Adjusts the price of products based on custom discount rules.
     *
     * @param float    $price The original product price.
     * @param WC_Product $product The WooCommerce product object.
     * @return float The modified price.
     */
    public function apply_product_discounts($price, $product) {
        // Check if product discounts are enabled.
        $enable_discounts = get_option('cdwc_enable_product_discounts', 'yes');
        if ($enable_discounts !== 'yes') {
            return $price;
        }

        // Example: Apply a 15% discount to products in a specific category.
        $discount_percentage = floatval(get_option('cdwc_product_discount_percentage', 0));

        if ($discount_percentage > 0 && has_term('discounted-category', 'product_cat', $product->get_id())) {
            $price = $price - ($price * ($discount_percentage / 100));
        }

        return $price;
    }










}