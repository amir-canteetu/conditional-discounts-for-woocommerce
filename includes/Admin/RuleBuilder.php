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
        add_action('wp_ajax_save_discount_rules', [$this, 'save_discount_rules'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_select2_scripts']);
    }

    public function add_meta_box() {
        add_meta_box(
            'discount-rules',
            __('Discount Rules', 'conditional-discounts'),
            [$this, 'render_meta_box'],
            'shop_discount'
        );
    }
    
    public function enqueue_select2_scripts( ) {
        wp_enqueue_script(
            'select2-js',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
            ['jquery'],
            '4.0.13',
            true
        );

        wp_enqueue_style(
            'select2-css',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
            [],
            '4.0.13'
        );        
    }

    public function render_meta_box($post) {

        $roles      = array_merge( ["all"=>"All"], $this->get_roles() );
        $categories = array_merge( ["all"=>"All"], $this->get_product_categories_list() );
        $products   = array_merge( ["all"=>"All"], $this->get_products_list() );
    
        
        global $wpdb;
        $repository = new DiscountRepository($wpdb);            
        $discount   = $repository->find($post->ID);

        if (!$discount) {
            $discount = new Discount($post->ID);
        }
        
        $this->enqueue_assets($discount->get_rule_set());
        
        $template_path = CDWC_PLUGIN_DIR . '/includes/Views/discount-rules-form.php';

        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<p>Template file not found.</p>';
        }
        
    }
    
    
    private function get_roles() {
        
        static $roles = null;
        if (null === $roles) {
            global $wp_roles;
            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();
            }
            $roles = $wp_roles->get_names();
        }
        return $roles;
        
    } 
    
    
    private function get_product_categories_list() {
         
        $terms = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        $categories = [];

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                // Use slug as key and name as value.
                $categories[ $term->slug ] = $term->name;
            }
        }

        return $categories;
    }  
    
    
    /**
     * Retrieves all published WooCommerce products.
     *
     * @return array Associative array of products (ID => Title).
     */
    private function get_products_list() {
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,       // Retrieve all products. Consider pagination if you have many products.
            'orderby'        => 'title',
            'order'          => 'ASC'
        );

        $query      = new \WP_Query( $args );
        $products   = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $products[ get_the_ID() ] = get_the_title();
            }
        }

        wp_reset_postdata();

        return $products;
    }    
    

    private function enqueue_assets($initial_rules) {

        wp_enqueue_script(
          'cdwc-rule-builder',
          CDWC_PLUGIN_URL . '/assets/admin/js/cdwc-admin.js',
          ['wp-element', 'wp-components'],
          filemtime(CDWC_PLUGIN_DIR . '/assets/admin/js/cdwc-admin.js'),
          true
        );
        
        wp_enqueue_script(
          'Ajv',
          'https://cdnjs.cloudflare.com/ajax/libs/ajv/6.12.6/ajv.min.js',
          [],
          '6.12.6'
        );        

        wp_localize_script('cdwc-rule-builder', 'cdwcRules', [
          'initialData' => $initial_rules,
          'api' => [
            'saveUrl' => admin_url('admin-ajax.php'),
            'action' => 'save_discount_rules',
            'nonce' => wp_create_nonce('save_discount_rules_action')
          ],
          'schema' => DiscountSchema::get()
        ]);
    }
    
    public function save_discount_rules( ) { 
        
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'save_discount_rules_action' ) ) {
            wp_send_json_error('Nonce verification failed.');
            return;
        }

        if ( ! current_user_can( 'edit_shop_discount', $_POST['post_id'] ) ) {
            wp_send_json_error('Insufficient permissions.');
            return;
        }

        $discount_rules     = isset($_POST['discount_rules']) ? $_POST['discount_rules'] : '';
        $sanitized_rules    = RuleSanitizer::process($discount_rules);

        global $wpdb;
        $repository = new DiscountRepository($wpdb);
        $repository->update($_POST['post_id'], [
            'rules' => $sanitized_rules,
            'label' => sanitize_text_field($_POST['post_title']),
        ]);

        wp_send_json_success('Discount rules updated successfully.');
    }    
    
    
    
}