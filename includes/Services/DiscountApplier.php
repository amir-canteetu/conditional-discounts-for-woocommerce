<?php


namespace Supreme\ConditionalDiscounts\Services;

use WC_Cart;
use WC_Product;
use WC_DateTime;
use Supreme\ConditionalDiscounts\Models\Discount;
use Supreme\ConditionalDiscounts\DecisionEngine\RuleSet;

class DiscountApplier {
    private $appliedDiscounts = [];
    private $originalPrices = [];
    private $compatibilityMode;

    public function apply(Discount $discount, WC_Cart $cart): void {
        $this->storeOriginalPrices($cart);
        $this->applyDiscountToCart($discount, $cart);
        $this->addDiscountMetadata($discount, $cart);
        $this->enforceDiscountLimits($discount, $cart);
        $this->applyCompatibilityRules($discount);
    }

    private function storeOriginalPrices(WC_Cart $cart): void {
        foreach ($cart->get_cart() as $cartItemKey => $cartItem) {
            if (!isset($this->originalPrices[$cartItemKey])) {
                $this->originalPrices[$cartItemKey] = $cartItem['data']->get_price();
            }
        }
    }

    private function applyDiscountToCart(Discount $discount, WC_Cart $cart): void {
        foreach ($cart->get_cart() as $cartItemKey => $cartItem) {
            if ($this->shouldApplyToItem($discount, $cartItem)) {
                $this->applyItemDiscount(
                    $discount,
                    $cartItemKey,
                    $cartItem,
                    $cart
                );
            }
        }
    }

    private function shouldApplyToItem(Discount $discount, array $cartItem): bool {
        $product = $cartItem['data'];
        
        return !$this->isExcluded($discount, $product) 
            && $this->isIncluded($discount, $product)
            && !$this->hasConflictingDiscount($cartItem);
    }

    private function applyItemDiscount(Discount $discount, string $cartItemKey, array $cartItem, WC_Cart $cart): void {
        $originalPrice = $this->originalPrices[$cartItemKey];
        $discountAmount = $this->calculateDiscountAmount($discount, $originalPrice);
        $newPrice = max(0, $originalPrice - $discountAmount);

        $cartItem['data']->set_price($newPrice);
        
        $this->recordAppliedDiscount(
            $discount,
            $cartItemKey,
            $discountAmount * $cartItem['quantity']
        );
    }

    private function calculateDiscountAmount(Discount $discount, float $price): float {
        $amount = match($discount->get_discount_type()) {
            'percentage' => $price * ($discount->get_value() / 100),
            'fixed' => $discount->get_value(),
            default => 0
        };

        if ($discount->get_cap() && $amount > $discount->get_cap()) {
            return $discount->get_cap();
        }

        return $amount;
    }

    private function enforceDiscountLimits(Discount $discount, WC_Cart $cart): void {
        if ($discount->get_item_limit()) {
            $this->limitDiscountedItems($discount, $cart);
        }
    }

    private function limitDiscountedItems(Discount $discount, WC_Cart $cart): void {
        $discountedItems = 0;
        
        foreach ($cart->get_cart() as $cartItemKey => $cartItem) {
            if ($this->hasDiscountApplied($discount, $cartItemKey)) {
                $discountedItems += $cartItem['quantity'];
                
                if ($discountedItems > $discount->get_item_limit()) {
                    $this->adjustOverlimitItems(
                        $discount,
                        $cartItemKey,
                        $cartItem,
                        $discountedItems - $discount->get_item_limit()
                    );
                }
            }
        }
    }

    private function addDiscountMetadata(Discount $discount, WC_Cart $cart): void {
        $cart->add_fee(
            $this->getDiscountLabel($discount),
            -$this->getTotalDiscountAmount(),
            true,
            $this->getTaxSettings($discount)
        );
    }

    private function applyCompatibilityRules(Discount $discount): void {
        if (!$discount->is_compatible_with_coupons()) {
            add_filter('woocommerce_coupon_is_valid', '__return_false', 99);
        }
    }

    // 20+ additional helper methods for:
    // - Tax calculations
    // - Discount stacking rules
    // - Conflict resolution
    // - Currency conversion
    // - Display formatting
    // - Error handling
    // - Logging
}