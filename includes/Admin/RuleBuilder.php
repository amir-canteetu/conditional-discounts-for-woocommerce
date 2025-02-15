<?php

namespace Supreme\ConditionalDiscounts\Admin;

use Supreme\ConditionalDiscounts\Models\Discount;
use Supreme\ConditionalDiscounts\Models\DiscountSchema;
use Supreme\ConditionalDiscounts\Repositories\DiscountRepository;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class DiscountFormHandler {
    
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('wp_ajax_save_discount_rules', [$this, 'handle_save_rules'], 10, 3);
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
        
    // Development vs production detection
    $is_dev = file_exists(CDWC_PLUGIN_DIR . 'build/.vite-running');

    if ($is_dev) {
      wp_enqueue_script(
        'cdwc-vite',
        'http://localhost:5173/@vite/client',
        [],
        null
      );
      
      wp_enqueue_script(
        'cdwc-rule-builder',
        'http://localhost:5173/assets/js/main.jsx',
        ['wp-element', 'wp-components'],
        null,
        true
      );
    } else {
      wp_enqueue_script(
        'cdwc-rule-builder',
        CDWC_PLUGIN_URL . 'build/main.js',
        ['wp-element', 'wp-components'],
        filemtime(CDWC_PLUGIN_DIR . 'build/main.js'),
        true
      );
    }

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

    public function save_discount($post_id, $post, $update) {
        
        global $wpdb;
        
        if (!current_user_can('edit_shop_discount', $post_id)) return;
        
        $repository = new DiscountRepository($wpdb);
        $sanitized = RuleSanitizer::process($_POST['discount_rules']);
        
        $repository->update($post_id, [
            'rules' => $sanitized,
            'label' => sanitize_text_field($_POST['post_title'])
        ]);
    }
}