<?php

namespace Supreme\ConditionalDiscounts\Admin;



class RuleValidator {


//    public static function process($discount_rules) {
//
//        $errors = [];
//    
//        if ($discount_rules['enabled']) {
//            // Required fields
//            if (empty($discount_rules['label'])) {
//                $errors[] = __('Discount label is required', 'conditional-discounts');
//            }
//    
//            // Value validation
//            if ($discount_rules['value'] <= 0) {
//                $errors[] = __('Discount value must be positive', 'conditional-discounts');
//            }
//    
//            if ($discount_rules['type'] === 'percentage' && $discount_rules['value'] > 100) {
//                $errors[] = __('Percentage discount cannot exceed 100%', 'conditional-discounts');
//            }
//    
//            // Date validation
//            $start = DateTime::createFromFormat('Y-m-d', $discount_rules['start_date']);
//            $end = DateTime::createFromFormat('Y-m-d', $discount_rules['end_date']);
//            
//            if (!$start || $start->format('Y-m-d') !== $discount_rules['start_date']) {
//                $errors[] = __('Invalid start date format', 'conditional-discounts');
//            }
//            
//            if (!$end || $end->format('Y-m-d') !== $discount_rules['end_date']) {
//                $errors[] = __('Invalid end date format', 'conditional-discounts');
//            }
//    
//            if ($start && $end && $start > $end) {
//                $errors[] = __('End date must be after start date', 'conditional-discounts');
//            }
//        }
//    
//        // Additional business logic
//        if ($discount_rules['min_cart_quantity'] > 0 && $discount_rules['min_cart_total'] > 0) {
//            $errors[] = __('Cannot require both quantity and total minimum', 'conditional-discounts');
//        }
//    
//        return $errors;
//    }
    
    
    public static function process(array $rules): array {
        
        $validated = [];

        $validated['enabled']   = filter_var($rules['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['label']     = sanitize_text_field($rules['label'] ?? __('New Discount', 'conditional-discounts'));

        $validated['min_cart_total']    = max(0, (float)($rules['min_cart_total'] ?? 0));
        $validated['min_cart_quantity'] = max(0, (int)($rules['min_cart_quantity'] ?? 0));

        // Discount type validation
        $validated['type'] = in_array($rules['type'] ?? 'percentage', ['percentage', 'fixed']) 
            ? $rules['type'] 
            : 'percentage';

        // Value validation with percentage cap
        $validated['value'] = (float)($rules['value'] ?? 0);
        if ($validated['type'] === 'percentage') {
            $validated['value'] = min(max($validated['value'], 0), 100);
        } else {
            $validated['value'] = max($validated['value'], 0);
        }

        // Cap validation (nullable)
        $validated['cap'] = isset($rules['cap']) ? max(0, (float)$rules['cap']) : null;

        // Array validation
        $validated['products'] = array_map('absint', (array)($rules['products'] ?? []));
        $validated['categories'] = array_map('sanitize_key', (array)($rules['categories'] ?? []));
        $validated['roles'] = array_intersect(
            (array)($rules['roles'] ?? []), 
            array_keys(get_editable_roles())
        );

        // Date validation
        $validated['start_date']    = self::validateDate($rules['start_date'] ?? '');
        $validated['end_date']      = self::validateDate($rules['end_date'] ?? '');

        // Ensure end date >= start date
        if ($validated['start_date'] && $validated['end_date']) {
            $end = new \DateTime($validated['end_date']);
            $start = new \DateTime($validated['start_date']);
            if ($end < $start) {
                $validated['end_date'] = $validated['start_date'];
            }
        }

        return $validated;
    }

    private static function validateDate(string $date): string {
        try {
            $dt = new \DateTime($date);
            return $dt->format('Y-m-d');
        } catch (Exception $e) {
            return current_time('Y-m-d');
        }
    }     


}