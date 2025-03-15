<?php

namespace Supreme\ConditionalDiscounts;

class DiscountRule {
    public $id;
    public $conditions;
    public $discounts;
    public $priority;
    
    public function __construct($post) {
        $this->id = $post->ID;
        $this->conditions = get_post_meta($post->ID, '_conditions', true);
        $this->discounts = get_post_meta($post->ID, '_discounts', true);
        $this->priority = get_post_meta($post->ID, '_priority', true) ?: 10;
    }
}

