<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

class CartDiscount implements DiscountInterface {

    public function apply($context): void {
        $cart = $context; // Assume $context is WC_Cart
        $cart->add_fee(__('Cart Discount', 'cdwc'), -10); // Example: Apply a flat $10 discount
    }

    public function validate($context): bool {
        $cart = $context; // Assume $context is WC_Cart
        return $cart->get_subtotal() >= 100; // Example: Apply discount only if cart total >= $100
    }

    public function getDescription(): string {
        return __('Flat $10 off for carts over $100.', 'cdwc');
    }

    public function isApplicable(): bool {
        return $this->cartTotal >= 100; // Example condition: Minimum cart total
    }

    public function calculateDiscount(): float {
        return $this->cartTotal * ($this->percentage / 100);
    }    

}