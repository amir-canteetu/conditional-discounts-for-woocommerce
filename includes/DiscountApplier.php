<?php

namespace Supreme\ConditionalDiscounts;

class DiscountApplier {
    
    public function __construct() {
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_discounts']);
    }

    public function apply_discounts(\WC_Cart $cart) {
        $rules = $this->get_active_rules();
        
        foreach ($rules as $rule) {
            if ($this->rule_matches($rule, $cart)) {
                $this->apply_rule_discount($rule, $cart);
            }
        }
    }

    private function get_active_rules() {
        $args = [
            'post_type' => 'shop_discount',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'cdwc_rules',
                    'compare' => 'EXISTS'
                ]
            ]
        ];

        $rules = [];
        $posts = get_posts($args);

        foreach ($posts as $post) {
            
            $rule = get_post_meta($post->ID, 'cdwc_rules', true);
            
            
            
            if (!$rule['enabled']) {
                continue;
            }

            if ($this->is_rule_valid($rule)) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    private function is_rule_valid($rule) {
        
        $now    = current_time('timestamp');
        $start  = strtotime($rule['start_date']);
        $end    = strtotime($rule['end_date']);
        
        if ($start && $now < $start) {return false;}
        if ($end && $now > $end) {return false;}
        
//        $usage = get_post_meta($post_id, 'cdwc_rules_usage_count', true);
//        if ($rule['max_use'] > 0 && $usage >= $rule['max_use']) {
//            return false;
//        }        

        return true;
    }

    private function rule_matches($rule, $cart) {
         
//        if (!empty($rule['roles']) && !$this->user_has_role($rule['roles'])) {
//            \write_log('L74');
//            return false;
//        }

        $cart_total = $cart->get_subtotal();
        if ($cart_total < $rule['min_cart_total']) {
            return false;
        }

        // Check cart quantity
        $cart_quantity = $cart->get_cart_contents_count();
        if ($cart_quantity < $rule['min_cart_quantity']) {
            return false;
        }

        // Check product/category/tag requirements
        if (!$this->cart_contains_required_items($rule, $cart)) {
            return false;
        }
        return true;
    }

    private function user_has_role($allowed_roles) {
        
        $user = wp_get_current_user();
        return !empty(array_intersect($allowed_roles, $user->roles));
    }

    private function cart_contains_required_items($rule, $cart) {
        
        $cart_items = $cart->get_cart();
        $found      = false;

        foreach ($cart_items as $item) {
            $product = $item['data'];

            switch ($rule['discount_type']) {
                case 'product':
                    if (in_array($product->get_id(), $rule['products'])) {
                        $found = true;
                    }
                    break;

                case 'category':
                    $categories = wc_get_product_terms($product->get_id(), 'product_cat', ['fields' => 'ids']);
                    if (!empty(array_intersect($categories, $rule['categories']))) {
                        $found = true;
                    }
                    break;

                case 'tag':
                    $tags = wc_get_product_terms($product->get_id(), 'product_tag', ['fields' => 'ids']);
                    if (!empty(array_intersect($tags, $rule['tags']))) {
                        $found = true;
                    }
                    break;
            }

            if ($found) {break;}
        }

        return $found;
    }

    private function apply_rule_discount($rule, $cart) {
        
        $discount_amount = 0;

        if ($rule['value_type'] === 'percentage') {
            $percentage         = floatval($rule['value']);
            $discount_amount    = ($cart->get_subtotal() * $percentage) / 100;
            
            if ($rule['discount_cap'] > 0) {
                $discount_amount = min($discount_amount, $rule['discount_cap']);
            }
        } else {
            $discount_amount = floatval($rule['value']);
        }

        if ($discount_amount > 0) {
            $cart->add_fee(  esc_html($rule['label']), -$discount_amount, false );

//            $usage = get_post_meta($rule['post']->ID, '_usage_count', true);
//            update_post_meta($rule['post']->ID, 'cdwc_rules_usage_count', $usage + 1);
        }
    }
}

