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
        add_action('wp_ajax_save_discount_rules', [$this, 'handle_save_rules'], 10, 3);
        // Add MIME type filter
       add_filter('wp_check_filetype_and_ext', function($types, $file, $filename, $mimes) {
         if (pathinfo($filename, PATHINFO_EXTENSION) === 'js') {
           $types['type'] = 'text/javascript';
           $types['ext'] = 'js';
         }
         return $types;
       }, 10, 4);       
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
    
    private function is_dev_mode () {
        return file_exists(CDWC_PLUGIN_DIR . 'build/.vite-running');
    }

    private function enqueue_assets($initial_rules) {
        
        add_filter('script_loader_tag', [$this, 'add_module_attribute'], 10, 3);

        if ( $this->is_dev_mode() ) {
            
          wp_enqueue_script(
            'cdwc-vite',
            'http://localhost:5173/@vite/client',
            [],
            null
          );

          wp_enqueue_script(
            'cdwc-main',
            CDWC_PLUGIN_URL . 'build/main.jsx',
            ['wp-element', 'wp-components'],
            null,
            [
              'in_footer' => true,
              'strategy' => 'defer',
              'type' => 'text/jsx'  
            ]            
          );
        } else {
          wp_enqueue_script(
            'cdwc-rule-builder',
            CDWC_PLUGIN_URL . 'build/main.jsx',
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
    
    public function add_module_attribute($tag, $handle, $src) {
      if (in_array($handle, ['cdwc-main', 'cdwc-vite'])) {
        return '<script type="module" src="' . esc_url($src) . '"></script>';
      }
      return $tag;
    }    

    public function save_discount($post_id, $post, $update) {
        
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