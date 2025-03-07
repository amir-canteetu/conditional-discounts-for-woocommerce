<?php

namespace Supreme\ConditionalDiscounts\Admin;



class RuleValidator {


public static function process(array $discount_rules): array {
    $errors = [];
    

    if ($discount_rules['enabled']) {
        // Required fields
        if (empty($discount_rules['label'])) {
            $errors[] = __('Discount label is required', 'conditional-discounts');
        }

        // Discount type validation
        if (!in_array($discount_rules['discount_type'], ['product', 'category', 'tag'])) {
            $errors[] = __('Invalid discount type', 'conditional-discounts');
        }

        // Target validation based on type
        switch ($discount_rules['discount_type']) {
            case 'product':
                if (empty($discount_rules['products'])) {
                    $errors[] = __('At least one product must be selected', 'conditional-discounts');
                }
                break;
            case 'category':
                if (empty($discount_rules['categories'])) {
                    $errors[] = __('At least one category must be selected', 'conditional-discounts');
                }
                break;
            case 'tag':
                if (empty($discount_rules['tags'])) {
                    $errors[] = __('At least one tag must be selected', 'conditional-discounts');
                }
                break;
        }

        // Value validation
        if ($discount_rules['value'] <= 0) {
            $errors[] = __('Discount value must be positive', 'conditional-discounts');
        }

        if ($discount_rules['value_type'] === 'percentage') {
            if ($discount_rules['value'] > 100) {
                $errors[] = __('Percentage discount cannot exceed 100%', 'conditional-discounts');
            }
        } else {
            if ($discount_rules['value'] <= 0) {
                $errors[] = __('Fixed discount must be greater than 0', 'conditional-discounts');
            }
        }

        // Cap validation
        if ($discount_rules['cap'] !== null && $discount_rules['cap'] < 0) {
            $errors[] = __('Discount cap cannot be negative', 'conditional-discounts');
        }

        // Date validation
        try {
            $start = new \DateTime($discount_rules['start_date']);
            $end = new \DateTime($discount_rules['end_date']);
            
            if ($start >= $end) {
                $errors[] = __('End date/time must be after start date/time', 'conditional-discounts');
            }
        } catch (Exception $e) {
            $errors[] = __('Invalid date/time format', 'conditional-discounts');
        }

        // User roles validation
        $valid_roles = array_keys(get_editable_roles());
        foreach ($discount_rules['roles'] as $role) {
            if (!in_array($role, $valid_roles)) {
                $errors[] = __('Invalid user role selected', 'conditional-discounts');
                break;
            }
        }

        // Cart requirements
        if ($discount_rules['min_cart_quantity'] > 0 && $discount_rules['min_cart_total'] > 0) {
            $errors[] = __('Cannot require both quantity and total minimum', 'conditional-discounts');
        }

        if ($discount_rules['min_cart_quantity'] < 0) {
            $errors[] = __('Minimum quantity cannot be negative', 'conditional-discounts');
        }

        if ($discount_rules['min_cart_total'] < 0) {
            $errors[] = __('Minimum total cannot be negative', 'conditional-discounts');
        }
    }

    // General validation
    if (!empty($discount_rules['products']) && !array_filter($discount_rules['products'], 'is_numeric')) {
        $errors[] = __('Invalid products selected', 'conditional-discounts');
    }

    return array_unique($errors);
}
    
    
//    public static function process(array $rules): array {
//        
//        $validated = [];
//
//        $validated['enabled']   = filter_var($rules['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
//        $validated['label']     = sanitize_text_field($rules['label'] ?? __('New Discount', 'conditional-discounts'));
//
//        $validated['min_cart_total']    = max(0, (float)($rules['min_cart_total'] ?? 0));
//        $validated['min_cart_quantity'] = max(0, (int)($rules['min_cart_quantity'] ?? 0));
//
//        // Discount type validation
//        $validated['type'] = in_array($rules['type'] ?? 'percentage', ['percentage', 'fixed']) 
//            ? $rules['type'] 
//            : 'percentage';
//
//        // Value validation with percentage cap
//        $validated['value'] = (float)($rules['value'] ?? 0);
//        if ($validated['type'] === 'percentage') {
//            $validated['value'] = min(max($validated['value'], 0), 100);
//        } else {
//            $validated['value'] = max($validated['value'], 0);
//        }
//
//        // Cap validation (nullable)
//        $validated['cap'] = isset($rules['cap']) ? max(0, (float)$rules['cap']) : null;
//
//        // Array validation
//        $validated['products'] = array_map('absint', (array)($rules['products'] ?? []));
//        $validated['categories'] = array_map('sanitize_key', (array)($rules['categories'] ?? []));
//        $validated['roles'] = array_intersect(
//            (array)($rules['roles'] ?? []), 
//            array_keys(get_editable_roles())
//        );
//
//        // Date validation
//        $validated['start_date']    = self::validateDate($rules['start_date'] ?? '');
//        $validated['end_date']      = self::validateDate($rules['end_date'] ?? '');
//
//        // Ensure end date >= start date
//        if ($validated['start_date'] && $validated['end_date']) {
//            $end = new \DateTime($validated['end_date']);
//            $start = new \DateTime($validated['start_date']);
//            if ($end < $start) {
//                $validated['end_date'] = $validated['start_date'];
//            }
//        }
//
//        return $validated;
//    }

    private static function validateDate(string $date): string {
        try {
            $dt = new \DateTime($date);
            return $dt->format('Y-m-d');
        } catch (Exception $e) {
            return current_time('Y-m-d');
        }
    }     


}