<?php

namespace Supreme\ConditionalDiscounts;

class DiscountApplier {
    public function apply_discounts(\WC_Cart $cart) {
        $rules = $this->get_active_rules();
        
        foreach ($rules as $rule) {
            if ((new ConditionChecker())->rule_matches($rule, $cart)) {
                $this->apply_rule_discounts($rule, $cart);
            }
        }
    }

    private function apply_rule_discounts(DiscountRule $rule, $cart) {
        foreach ($rule->discounts as $discount) {
            switch ($discount['type']) {
                case 'percentage':
                    $cart->add_fee($discount['name'], -$this->calculate_percentage($discount, $cart));
                    break;
                case 'fixed':
                    $cart->add_fee($discount['name'], -$discount['amount']);
                    break;
            }
        }
    }
    
    // Helper calculation methods...
}

