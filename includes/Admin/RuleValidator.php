<?php

namespace Supreme\ConditionalDiscounts\Admin;



class RuleValidator {


    public static function process($discount_rules) {

        $errors = [];
    
        if ($discount_rules['enabled']) {
            // Required fields
            if (empty($discount_rules['label'])) {
                $errors[] = __('Discount label is required', 'conditional-discounts');
            }
    
            // Value validation
            if ($discount_rules['value'] <= 0) {
                $errors[] = __('Discount value must be positive', 'conditional-discounts');
            }
    
            if ($discount_rules['type'] === 'percentage' && $discount_rules['value'] > 100) {
                $errors[] = __('Percentage discount cannot exceed 100%', 'conditional-discounts');
            }
    
            // Date validation
            $start = DateTime::createFromFormat('Y-m-d', $discount_rules['start_date']);
            $end = DateTime::createFromFormat('Y-m-d', $discount_rules['end_date']);
            
            if (!$start || $start->format('Y-m-d') !== $discount_rules['start_date']) {
                $errors[] = __('Invalid start date format', 'conditional-discounts');
            }
            
            if (!$end || $end->format('Y-m-d') !== $discount_rules['end_date']) {
                $errors[] = __('Invalid end date format', 'conditional-discounts');
            }
    
            if ($start && $end && $start > $end) {
                $errors[] = __('End date must be after start date', 'conditional-discounts');
            }
        }
    
        // Additional business logic
        if ($discount_rules['min_cart_quantity'] > 0 && $discount_rules['min_cart_total'] > 0) {
            $errors[] = __('Cannot require both quantity and total minimum', 'conditional-discounts');
        }
    
        return $errors;
    }


}