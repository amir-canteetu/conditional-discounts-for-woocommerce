<?php


namespace Supreme\ConditionalDiscounts\Admin;





class RuleSanitizer {


    public static function process($discount_rules) {
        $clean = [];
    
        // Basic fields
        $clean['enabled'] = rest_sanitize_boolean($discount_rules['enabled'] ?? false);
        $clean['label'] = sanitize_text_field(substr($discount_rules['label'] ?? '', 0, 255));
        $clean['min_cart_total'] = max(0, floatval($discount_rules['min_cart_total'] ?? 0));
        $clean['min_cart_quantity'] = max(0, intval($discount_rules['min_cart_quantity'] ?? 0));
        $clean['type'] = in_array($discount_rules['type'] ?? '', ['percentage', 'fixed']) ? $discount_rules['type']  : 'percentage';
        $clean['value'] = max(0, floatval($discount_rules['value'] ?? 0));
        $clean['cap'] = max(0, floatval($discount_rules['cap'] ?? 0));
    
        // Array fields
        $clean['products'] = array_map('absint', (array)($discount_rules['products'] ?? []));
        $clean['categories'] = array_map(
            fn($slug) => sanitize_title($slug), 
            (array)($discount_rules['categories'] ?? [])
        );
        $clean['roles'] = array_intersect(
            (array)($discount_rules['roles'] ?? []), 
            array_keys(get_editable_roles())
        );
    
        // Date validation
        $clean['start_date'] = sanitize_text_field($discount_rules['start_date'] ?? '');
        $clean['end_date'] = sanitize_text_field($discount_rules['end_date'] ?? '');
    
        return $clean;
    }



}

