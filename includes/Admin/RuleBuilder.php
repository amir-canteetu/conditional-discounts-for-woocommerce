<?php

namespace Supreme\ConditionalDiscounts\Admin;

use Supreme\ConditionalDiscounts\Models\Discount;
use Supreme\ConditionalDiscounts\Admin\RuleSanitizer;
use Supreme\ConditionalDiscounts\Admin\RuleValidator;
use Supreme\ConditionalDiscounts\Models\DiscountSchema;
use Supreme\ConditionalDiscounts\Repositories\DiscountRepository;



if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class RuleBuilder {
    
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('wp_ajax_save_discount_rules', [$this, 'save_discount_rules'], 10);
        add_action('wp_ajax_nopriv_save_discount_rules', [$this, 'handle_unauthorized_access']);
    }

    public function handle_unauthorized_access() {
        wp_send_json_error([
            'message' => __('Authentication required', 'conditional-discounts')
        ], 401);
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

        $roles      = $this->get_roles();
        $categories = $this->get_product_categories_list();
        $products   = $this->get_products_list();
        $tags       = $this->get_product_tags_list();
        

        global $wpdb;
        $repository = new DiscountRepository($wpdb);            
        $discount   = $repository->find($post->ID) ?: new Discount($post->ID);
        
        $this->enqueue_assets($discount->get_rule_set());

        // Create template context array
        $context = [
            'roles' => $roles,
            'categories' => $categories,
            'products' => $products,
            'discount' => $discount,
            'post' => $post,
            'tags' => $tags
        ];

        $this->render_template(
            CDWC_PLUGIN_DIR . '/includes/Views/discount-rules-form.php',
            $context
        );
        
    }
    
    
    public static function get_default_rules(): array {

        // Set validity to current date at 00:00 UTC and end 7 days later at 23:59:59 UTC
        $validity_start = new \DateTime('today', new \DateTimeZone('UTC'));
        $validity_end = clone $validity_start;
        $validity_end->modify('+7 days')->setTime(23, 59, 59);
     

        return [
            'schema_version'      => '1.0',
            'enabled'             => false,
            'label'               => __('New Discount', 'conditional-discounts'),
            'discount_value_type' => 'percentage',
            'discount_type'       => 'product',
            'value'               => null,
            'cap'                 => null,
            'min_cart_total'      => 0.00,
            'minimum_quantity'    => 0,
            'products'            => [],
            'categories'          => [],
            'tags'                => [],  
            'user_roles'          => [],
            'notes'               => '',
            'validity'            => [
                'start_date'     => $validity_start->format(\DateTime::ATOM),
                'end_date'       => $validity_end->format(\DateTime::ATOM),
                'timezone'  => 'UTC'
            ]
        ];
        
    }       

    /**
     * Safe template rendering with output escaping
     */
    protected function render_template($template_path, array $context = []) {
        // Validate template path
        $resolved_path = realpath($template_path);
        $plugin_path = realpath(CDWC_PLUGIN_DIR);
        
        if (strpos($resolved_path, $plugin_path) !== 0) {
            _doing_it_wrong(__METHOD__, 'Template path must be within plugin directory', '1.0');
            return;
        }

        if (!file_exists($resolved_path)) {
            echo '<p>' . esc_html__('Template file not found.', 'conditional-discounts') . '</p>';
            return;
        }

        // Extract context variables with prefix for safety
        extract(array_merge(
            ['cdwc_template' => $context],
            $context
        ), EXTR_SKIP);

        // Buffer and require with limited scope
        ob_start();
        require $resolved_path;
        $output = ob_get_clean();

        // Allow filtering of output
        echo apply_filters('cdwc_template_output', $output, $template_path, $context);
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
                $categories[ $term->slug ] = $term->name;
            }
        }

        return $categories;
    } 
    
    private function get_product_tags_list() {
        $terms = get_terms(array(
            'taxonomy'   => 'product_tag',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ));

        $tags = [];

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $tags[$term->slug] = $term->name;
            }
        }
        

        return $tags;
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
            'posts_per_page' => -1,       
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
            'select2-js',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
            ['jquery'],
            '4.0.13',
            true
        );

        wp_enqueue_style(
            'cdwc-admin-css',
            CDWC_PLUGIN_URL . '/assets/admin/css/cdwc-admin.css',
            [],
            '1.0.0'
        );     
        
        wp_enqueue_style(
            'select2-css',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
            [],
            '4.0.13'
        );         
          
        wp_enqueue_script('jquery-validation', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js', ['jquery']);        

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
            'nonce' => wp_create_nonce('save_discount_rules_action')
          ],
          'schema' => DiscountSchema::get()
        ]);
    }
    
    public function save_discount_rules( ) { 

        try {

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'save_discount_rules_action' ) ) {
                throw new \Exception(__('Nonce verification failed.', 'conditional-discounts'), 403);
            }
            
            if (!isset($_POST['data'])) {
                throw new \Exception(__('Invalid request format', 'conditional-discounts'), 400);
            }                 
            
            //Decodes the JSON into a PHP associative array    
            $raw_data   = json_decode(wp_unslash($_POST['data']), true);           

            if (!is_array($raw_data)) {
                throw new \Exception(__('Malformed request data', 'conditional-discounts'), 400);
            }
            
            $post_id    = isset($raw_data['post']['id']) ? (int) $raw_data['post']['id'] : 0;   
                    
            if ( ! current_user_can( 'edit_shop_discount', $post_id ) ) {
                throw new \Exception(__('Insufficient permissions', 'conditional-discounts'), 400);
            } 
            
            $data = wp_parse_args($raw_data, [
                'meta' => [],
                'post' => ['id' => 0, 'status' => '']
            ]);

            // Validate post ID
            $post_id = absint($data['post']['id']);
            if (!$post_id || !get_post($post_id)) {
                throw new \Exception(__('Invalid discount entry', 'conditional-discounts'), 400);
            }
        
            // Check post ownership/capability
            if (!current_user_can('edit_post', $post_id)) {
                throw new \Exception(__('Unauthorized access', 'conditional-discounts'), 403);
            } 
            
            // Sanitize data
            $sanitized_rules    = RuleSanitizer::process($data['meta']);
            $validation_errors  = RuleValidator::process($sanitized_rules);        
            
            if (!empty($validation_errors)) {
                wp_send_json_error([
                    'message' => __('Validation failed', 'conditional-discounts'),
                    'errors' => $validation_errors
                ], 422);
            }
    
            global $wpdb;
            $repository = new DiscountRepository($wpdb);
            $repository->update($post_id, [
                'rules' => $sanitized_rules,
                'label' => sanitize_text_field($_POST['post_title']),
            ]);
    
            wp_send_json_success([
                'message' => __('Discount rules saved successfully', 'conditional-discounts'),
                'data' => $sanitized_rules
            ]);

        }  catch (\Exception $e) {

            $status_code = $e->getCode() ?: 500;
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code' => $status_code
            ], $status_code);
        }
        

    }    
}