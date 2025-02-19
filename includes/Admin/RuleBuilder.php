<?php

namespace Supreme\ConditionalDiscounts\Admin;

use Supreme\ConditionalDiscounts\Models\Discount;
use Supreme\ConditionalDiscounts\Models\DiscountSchema;
use Supreme\ConditionalDiscounts\Repositories\DiscountRepository;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class RuleBuilder {
    
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('wp_ajax_save_discount_rules', [$this, 'save_discount_rules'], 10, 3);
    }

    public function add_meta_box() {
        add_meta_box(
            'discount-rules',
            __('Discount Rules', 'conditional-discounts'),
            [$this, 'render_meta_box'],
            'shop_discount'
        );
    }

    public function render_meta_box($post) {
        
        global $wpdb;
        $repository = new DiscountRepository($wpdb);            
        $discount   = $repository->find($post->ID);

        if (!$discount) {
            $discount = new Discount($post->ID);
        }
        
        $this->enqueue_assets($discount->get_rule_set());
        echo '<div id="cdwc-rule-builder-root"></div>';
        
    }
    

    private function enqueue_assets($initial_rules) {

        wp_enqueue_script(
          'cdwc-rule-builder',
          CDWC_PLUGIN_URL . '/assets/admin/js/cdwc-admin.js',
          ['wp-element', 'wp-components'],
          filemtime(CDWC_PLUGIN_DIR . '/assets/admin/js/cdwc-admin.js'),
          true
        );

        wp_localize_script('cdwc-rule-builder', 'cdwcRules', [
          'initialData' => $initial_rules,
          'api' => [
            'saveUrl' => admin_url('admin-ajax.php'),
            'action' => 'save_discount_rules',
            'nonce' => wp_create_nonce('cdwc_save_rules')
          ],
          'schema' => DiscountSchema::get()
        ]);
    }
       

    public function save_discount_rules($post_id, $post, $update) {
        
        global $wpdb;
        
        if (!current_user_can('edit_shop_discount', $post_id)) return;
        
        $repository = new DiscountRepository($wpdb);
        $sanitized  = RuleSanitizer::process($_POST['discount_rules']);
        
        $repository->update($post_id, [
            'rules' => $sanitized,
            'label' => sanitize_text_field($_POST['post_title'])
        ]);
    }
}