<?php

namespace Supreme\ConditionalDiscounts\DecisionEngine;

use WC_Cart;
use WP_User;


/**
 * RuleSet class.
 *
 * -- Encapsulates all the logic related to evaluating and managing rule conditions
 * 
 * -- The RuleSet class would need to load the rules from the database. Since the rules are stored as JSON, the class should parse and validate 
 * this JSON. Validation is important to ensure that the rules are in the correct format and contain valid values. For example, checking that 
 * numerical values are positive, dates are in the correct format, and arrays of product IDs are integers.
 *
 */

class RuleSet {
    private array $rules;
    private array $context;
    private array $evaluationCache = [];

    public function __construct(array $rules) {
        $this->rules = $this->validateRules($rules);
    }

    public function evaluate(WC_Cart $cart, WP_User $user): bool {
        $this->context = $this->buildContext($cart, $user);
        
        return $this->evaluateGroup($this->rules);
    }

    private function buildContext(WC_Cart $cart, WP_User $user): array {
        return [
            'cart' => [
                'total' => $cart->get_subtotal(),
                'quantity' => $cart->get_cart_contents_count(),
                'items' => array_map(function($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'categories' => wc_get_product_cat_ids($item['product_id']),
                        'tags' => wp_get_post_terms($item['product_id'], 'product_tag', ['fields' => 'ids']),
                        'on_sale' => $item['data']->is_on_sale()
                    ];
                }, $cart->get_cart())
            ],
            'user' => [
                'roles' => $user->roles,
                'meta' => get_user_meta($user->ID)
            ],
            'datetime' => current_time('timestamp')
        ];
    }

    private function evaluateGroup(array $group): bool {
        $operator = $group['operator'] ?? 'AND';
        $results = [];

        foreach ($group['conditions'] as $condition) {
            if (isset($condition['conditions'])) {
                $results[] = $this->evaluateGroup($condition);
            } else {
                $results[] = $this->evaluateCondition($condition);
            }
        }

        return $operator === 'AND' 
            ? !in_array(false, $results, true)
            : in_array(true, $results, true);
    }

    private function evaluateCondition(array $condition): bool {
        $cacheKey = md5(serialize($condition));
        if (isset($this->evaluationCache[$cacheKey])) {
            return $this->evaluationCache[$cacheKey];
        }

        $handler = match($condition['type']) {
            'cart_total' => [$this, 'handleCartTotal'],
            'product_category' => [$this, 'handleProductCategory'],
            'user_role' => [$this, 'handleUserRole'],
            'date_range' => [$this, 'handleDateRange'],
            default => throw new \InvalidArgumentException("Invalid condition type: {$condition['type']}")
        };

        $result = $handler($condition);
        $this->evaluationCache[$cacheKey] = $result;
        
        return $result;
    }

    // Condition Handlers
    private function handleCartTotal(array $condition): bool {
        $cartTotal = $this->context['cart']['total'];
        
        return match($condition['operator']) {
            '>' => $cartTotal > $condition['value'],
            '>=' => $cartTotal >= $condition['value'],
            '<' => $cartTotal < $condition['value'],
            '<=' => $cartTotal <= $condition['value'],
            '==' => $cartTotal == $condition['value'],
            default => false
        };
    }

    private function handleProductCategory(array $condition): bool {
        $categoryIds = array_unique(array_reduce(
            $this->context['cart']['items'],
            fn($carry, $item) => array_merge($carry, $item['categories']),
            []
        ));

        return match($condition['operator']) {
            'any' => !empty(array_intersect($categoryIds, $condition['values'])),
            'all' => empty(array_diff($condition['values'], $categoryIds)),
            'none' => empty(array_intersect($categoryIds, $condition['values'])),
            default => false
        };
    }

    private function handleUserRole(array $condition): bool {
        return match($condition['operator']) {
            'any' => !empty(array_intersect($this->context['user']['roles'], $condition['values'])),
            'all' => empty(array_diff($condition['values'], $this->context['user']['roles'])),
            'none' => empty(array_intersect($this->context['user']['roles'], $condition['values'])),
            default => false
        };
    }

    private function handleDateRange(array $condition): bool {
        $now = $this->context['datetime'];
        $start = strtotime($condition['start']);
        $end = strtotime($condition['end']);
        
        return $now >= $start && $now <= $end;
    }

    private function validateRules(array $rules): array {
        // Implement JSON Schema validation
        return $rules;
    }

    // Utility Methods
    public function toArray(): array {
        return $this->rules;
    }

    public function getApplicableConditions(): array {
        return $this->extractConditionTypes($this->rules);
    }

    private function extractConditionTypes(array $node): array {
        $types = [];
        
        foreach ($node['conditions'] as $condition) {
            if (isset($condition['conditions'])) {
                $types = array_merge($types, $this->extractConditionTypes($condition));
            } else {
                $types[] = $condition['type'];
            }
        }
        
        return array_unique($types);
    }
}
