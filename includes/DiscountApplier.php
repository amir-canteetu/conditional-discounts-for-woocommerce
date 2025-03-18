<?php

namespace Supreme\ConditionalDiscounts;

class DiscountApplier {
    
        public function __construct() {
                    add_action('woocommerce_cart_calculate_fees', [$this, 'apply_discounts']);
                    add_action('woocommerce_order_status_completed', [$this, 'update_usage_counts']);
                    add_action('woocommerce_order_status_processing', [$this, 'update_usage_counts']);

                    //add_action('woocommerce_order_refunded', [$this, 'decrement_usage_counts']);
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

            $global_usage = get_post_meta($rule['post_id'], 'cdwc_usage_count', true);
            if ($rule['max_use'] > 0 && $global_usage >= $rule['max_use']) {
                return false;
            }

            return true;
        }

        private function rule_matches($rule, $cart) {

    //         if (!empty($rule['roles']) && !$this->user_has_role($rule['roles'])) {
    //            \write_log('L74');
    //            return false;
    //         }

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
                
                if ($rule['max_use_per_user'] > 0) {
                    $user_id    = get_current_user_id();
                    $email      = $cart->get_customer() ? $cart->get_customer()->get_billing_email() : null;

                    // Track guests by email, logged-in users by ID
                    $identifier = $user_id ? "user_{$user_id}" : "email_" . md5($email);
                    $usage_key  = "cdwc_user_usage_{$identifier}";

                    // Get current usage count
                    $usage = get_post_meta($rule['post_id'], $usage_key, true) ?: 0;

                    if ($usage >= $rule['max_use_per_user']) {
                        return false;
                    }
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

                $total_discount     = 0;
                $eligible_products  = $this->get_eligible_product_ids($rule);

                // First calculate total potential discount
                foreach ($cart->get_cart() as $cart_item) {
                    if ($this->is_product_eligible($cart_item, $eligible_products, $rule['discount_type'])) {
                        $product_price = $cart_item['data']->get_price();
                        $quantity = $cart_item['quantity'];

                        if ($rule['value_type'] === 'percentage') {
                            // Calculate percentage discount without cap first
                            $item_discount = ($product_price * $rule['value']) / 100;
                            $total_discount += $item_discount * $quantity;
                        } else {
                            // Fixed amount per item
                            $total_discount += $rule['value'] * $quantity;
                        }
                    }
                }

                // Apply discount cap to total percentage discount
                if ($rule['value_type'] === 'percentage' && $rule['discount_cap'] > 0) {
                    $total_discount = min($total_discount, $rule['discount_cap']);
                }

                if ($total_discount > 0) {
                    $fee_name = sprintf(
                        '%s (CDWCID:%d)', 
                        esc_html($rule['label']), 
                        $rule['post_id']
                    );

                    $cart->add_fee($fee_name, -$total_discount, false);
                }

        }
        
        private function get_eligible_product_ids($rule) {
                switch ($rule['discount_type']) {
                    case 'product':
                        return $rule['products'];
                    case 'category':
                        return $this->get_products_in_terms($rule['categories'], 'product_cat');
                    case 'tag':
                        return $this->get_products_in_terms($rule['tags'], 'product_tag');
                    case 'brand':
                        return $this->get_products_in_terms($rule['brands'], 'product_brand');
                    default:
                        return [];
                }
        }

        private function get_products_in_terms($term_ids, $taxonomy) {
            if (empty($term_ids)) return [];

            return get_posts([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term_ids,
                    ]
                ]
            ]);
        }

        private function is_product_eligible($cart_item, $eligible_products, $discount_type) {
            $product_id = $cart_item['product_id'];

            // Check variations differently
            if ($cart_item['variation_id']) {
                $product_id = $cart_item['variation_id'];
            }

            return in_array($product_id, $eligible_products);
        }

        public function update_usage_counts($order_id) {
            $order = wc_get_order($order_id);
            $user_id = $order->get_customer_id();
            $email = $order->get_billing_email();

            foreach ($order->get_fees() as $fee) {
                $fee_name = $fee->get_name();

                if (preg_match('/CDWCID:(\d+)/', $fee_name, $matches)) {
                    $discount_id = (int) $matches[1];
                    $rule = get_post_meta($discount_id, 'cdwc_rules', true);

                    if ($rule['max_use_per_user'] > 0) {
                        $identifier = $user_id ? "user_{$user_id}" : "email_" . md5($email);
                        $usage_key = "cdwc_user_usage_{$identifier}";

                        // Increment user-specific count
                        $current_count = get_post_meta($discount_id, $usage_key, true) ?: 0;
                        update_post_meta($discount_id, $usage_key, $current_count + 1);
                    }

                    // Update global count
                    $global_usage = get_post_meta($discount_id, 'cdwc_usage_count', true) ?: 0;
                    update_post_meta($discount_id, 'cdwc_usage_count', $global_usage + 1);
                }
            }
        }
}

