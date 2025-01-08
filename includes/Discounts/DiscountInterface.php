<?php

namespace Supreme\ConditionalDiscounts\Discounts;

use WC_Cart;

/**
 * Interface DiscountInterface
 *
 * Defines the contract for all discount types in the plugin.
 *
 * @package Supreme\ConditionalDiscounts\Discounts
 */
interface DiscountInterface {

    /**
     * Checks if the discount conditions are met.
     *
     * @return bool True if the conditions are met, false otherwise.
     */
    public function isApplicable(WC_Cart $cart): bool;

    /**
     * Calculates the discount amount to be applied.
     *
     * @return float The discount amount.
     */
    public function calculateDiscount(): float;

    /**
     * Applies the discount to the appropriate items or cart total.
     *
     * @return void
     */
    public function apply($context): void;

    /**
     * Returns the name or description of the discount.
     *
     * @return string The discount name or description.
     */
    public function getDescription(): string;
}
