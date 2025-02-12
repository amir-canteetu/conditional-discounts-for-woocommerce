<?php


use Supreme\ConditionalDiscounts\Repositories\DiscountRepository;

add_action('woocommerce_before_calculate_totals', function(WC_Cart $cart) {
    $discounts = (new DiscountRepository())->findActiveDiscounts();
    
    foreach ($discounts as $discount) {
        if ((new DecisionEngine())->evaluate($discount, $cart)) {
            (new DiscountApplier())->apply($discount, $cart);
        }
    }
});

