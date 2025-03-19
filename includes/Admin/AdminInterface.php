<?php

namespace Supreme\ConditionalDiscounts\Admin;

use Supreme\ConditionalDiscounts\Views\View;

class AdminInterface {
    
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('init', [$this, 'register_shop_discount_post_type']);
        add_action('wp_ajax_cdwc_search_products', [$this, 'ajax_search_products']);
        add_action('wp_ajax_cdwc_search_taxonomy', [$this, 'ajax_search_taxonomy']);
        add_action('save_post_shop_discount', [$this, 'save_discount_rules']);
    }

    public function register_shop_discount_post_type() {
        $args = [
            'labels' => [
                'name'                  => __( 'Discounts', 'conditional-discounts-for-woocommerce' ),
                'singular_name'         => __( 'Discount', 'conditional-discounts-for-woocommerce' ),
                'menu_name'             => _x( 'Discounts', 'Admin menu name', 'conditional-discounts-for-woocommerce' ),
                'add_new'               => __( 'Add discount', 'conditional-discounts-for-woocommerce' ),
                'add_new_item'          => __( 'Add new discount', 'conditional-discounts-for-woocommerce' ),
                'edit'                  => __( 'Edit', 'conditional-discounts-for-woocommerce' ),
                'edit_item'             => __( 'Edit discount', 'conditional-discounts-for-woocommerce' ),
                'new_item'              => __( 'New discount', 'conditional-discounts-for-woocommerce' ),
                'view_item'             => __( 'View discount', 'conditional-discounts-for-woocommerce' ),
                'search_items'          => __( 'Search discounts', 'conditional-discounts-for-woocommerce' ),
                'not_found'             => __( 'No discounts found', 'conditional-discounts-for-woocommerce' ),
                'not_found_in_trash'    => __( 'No discounts found in trash', 'conditional-discounts-for-woocommerce' ),
                'parent'                => __( 'Parent discount', 'conditional-discounts-for-woocommerce' ),
                'filter_items_list'     => __( 'Filter discounts', 'conditional-discounts-for-woocommerce' ),
                'items_list_navigation' => __( 'Discounts navigation', 'conditional-discounts-for-woocommerce' ),
                'items_list'            => __( 'Discounts list', 'conditional-discounts-for-woocommerce' ),
            ],
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => current_user_can( 'edit_others_shop_orders' ) ? 'woocommerce' : true,
            'publicly_queryable'    => false,
            'supports'              => ['title'],
            'has_archive'           => false,
            'show_in_rest'          => false,
            'rewrite'               => ['slug' => 'shop_discount'],
            'menu_icon'             => 'dashicons-tag',
            'hierarchical'          => false,
            'exclude_from_search'   => true,
            'show_in_nav_menus'     => false,
            'capability_type'       => 'shop_coupon',
            'map_meta_cap'          => true,
            'show_in_admin_bar'     => true,
        ];

        register_post_type('shop_discount', $args);
    } 
    
    
    public function add_meta_box() {
        add_meta_box(
            'discount-rules',
            __('Discount Rules', 'conditional-discounts-for-woocommerce'),
            [$this, 'render_meta_box'],
            'shop_discount'
        );
    }
    
    public function render_meta_box($post) {

            $discountData = get_post_meta($post->ID, 'cdwc_rules', true);
            
            $defaults = [
                'enabled' => false,
                'label' => 'New Discount',
                'min_cart_total' => 0,
                'min_cart_quantity' => 0,
                'discount_type' => 'product',
                'value_type' => 'percentage',
                'value' => 0,
                'discount_cap' => 0,
                'max_use' => 1,
                'products' => [],
                'brands' => [],
                'categories' => [],
                'tags' => [],
                'roles' => [],
                'start_date' => '',
                'end_date' => '',
                'max_use_per_user' => 1
            ];
            
            $discount                       = wp_parse_args($discountData, $defaults);
            $discount['post']               = $post;
            $discount['currency_symbol']    = get_woocommerce_currency_symbol(); 
            $discount['nonce_field']        = wp_nonce_field( 'save_discount_rules', 'discount_rules_nonce', true,  false );  
            $discount['labels'] = [
                'search_products' => __('Search products...', 'conditional-discounts-for-woocommerce'),
                'search_cats' => __('Search categories...', 'conditional-discounts-for-woocommerce'),
                'search_tags' => __('Search tags...', 'conditional-discounts-for-woocommerce'),
                'search_brands' => __('Search brands...', 'conditional-discounts-for-woocommerce'),
            ];   
            
            $selected_products = [];
            if (!empty($discount['products'])) {
                $products = wc_get_products([
                    'include' => $discount['products'],
                    'limit' => -1,
                ]);
                foreach ($products as $product) {
                    $selected_products[$product->get_id()] = $product->get_formatted_name();
                }
            }

            $discount['selected_products']  = $selected_products;
            $discount['currency_symbol']    = get_woocommerce_currency_symbol();
            $discount['timezone']           = wp_timezone_string();
            
            $this->enqueue_assets();
            
            View::render_template( CDWC_PLUGIN_PATH . '/includes/Views/discount-rules-form.php', $discount );              

        }   
        
    private function enqueue_assets() {

        wp_enqueue_script(
            'cdwc-select2',
            CDWC_PLUGIN_URL . '/assets/select2.full.min.js',
            ['jquery'],
            '4.0.13',
            true
        );

        wp_enqueue_style(
            'cdwc-select2',
            CDWC_PLUGIN_URL . '/assets/select2.min.css',
            [],
            '4.0.13'
        );

        wp_enqueue_style(
            'cdwc-admin-css',
            CDWC_PLUGIN_URL . '/assets/cdwc-admin.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'cdwc-admin-js',
            CDWC_PLUGIN_URL . '/assets/cdwc-admin.js',
            ['jquery', 'cdwc-select2'],
            filemtime(CDWC_PLUGIN_PATH . 'assets/cdwc-admin.js'),
            true
        );

        wp_localize_script('cdwc-admin-js', 'cdwcAdmin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('cdwc-search-nonce'),
            'labels'  => [
                'search_products' => __('Search products...', 'conditional-discounts-for-woocommerce'),
                'search_cats'     => __('Search categories...', 'conditional-discounts-for-woocommerce'),
                'search_tags'     => __('Search tags...', 'conditional-discounts-for-woocommerce'),
            ],
            'currency_symbol' => get_woocommerce_currency_symbol()
        ]);
    }
    
    public function ajax_search_products() {
        $this->verify_ajax_request();
        
        $search_term = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $page = isset($_GET['page']) ? absint($_GET['page']) : 1;
        
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => 20,
            'paged'          => $page,
            's'              => $search_term,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        $query = new \WP_Query($args);
        $results = [];

        foreach ($query->posts as $post) {
            $product = wc_get_product($post->ID);
            if ($product) {
                $results[] = [
                    'id'   => $post->ID,
                    'text' => $product->get_formatted_name(),
                ];
            }
        }

        wp_send_json([
            'results' => $results,
            'pagination' => [
                'more' => $query->max_num_pages > $page
            ]
        ]);
    }

    public function ajax_search_taxonomy() {
        $this->verify_ajax_request();

        $search_term = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $taxonomy = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';
        $page = isset($_GET['page']) ? absint($_GET['page']) : 1;

        $valid_taxonomies = ['product_cat', 'product_tag', 'product_brand'];
        if (!in_array($taxonomy, $valid_taxonomies, true)) {
            wp_send_json_error('Invalid taxonomy');
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'name__like' => $search_term,
            'hide_empty' => false,
            'number'     => 20,
            'offset'     => ($page - 1) * 20,
        ]);

        $results = [];
        foreach ($terms as $term) {
            $results[] = [
                'id'   => $term->term_id,
                'text' => $term->name,
            ];
        }

        wp_send_json([
            'results' => $results,
            'pagination' => [
                'more' => count($terms) === 20
            ]
        ]);
    }

    private function verify_ajax_request() {
        check_ajax_referer('cdwc-search-nonce', 'security');

        if (!current_user_can('edit_shop_coupons')) {
            wp_send_json_error('Unauthorized');
        }
    }

    public function save_discount_rules($post_id) {

        if (!isset($_POST['discount_rules_nonce']) || 
            !wp_verify_nonce($_POST['discount_rules_nonce'], 'save_discount_rules')) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user capabilities
        if (!current_user_can('edit_shop_coupon', $post_id)) {
            return;
        }

        $sanitized_data = [
            'enabled' => false,
            'label' => '',
            'min_cart_total' => 0,
            'min_cart_quantity' => 0,
            'discount_type' => 'product',
            'value_type' => 'percentage',
            'value' => 0,
            'discount_cap' => 0,
            'max_use' => 1,
            'products' => [],
            'categories' => [],
            'tags' => [],
            'roles' => [],
            'start_date' => '',
            'end_date' => '', 
            'post_id' => $post_id
        ];

        // Sanitize each field
        if (isset($_POST['discount'])) {
            
            $input                      = $_POST['discount'];
            $sanitized_data['enabled']  = isset($input['enabled']);
            $sanitized_data['label']    = sanitize_text_field($input['label'] ?? '');
            
            $sanitized_data['min_cart_total']       = max(0, floatval($input['min_cart_total'] ?? 0));
            $sanitized_data['min_cart_quantity']    = max(0, intval($input['min_cart_quantity'] ?? 0));
            $sanitized_data['value']                = max(0, floatval($input['value'] ?? 0));
            $sanitized_data['discount_cap']         = max(0, floatval($input['discount_cap'] ?? 0));
            $sanitized_data['max_use']              = max(0, intval($input['max_use'] ?? 1));
            $sanitized_data['max_use_per_user']     = max(0, intval($input['max_use_per_user'] ?? 0));

            // Select fields
            $sanitized_data['discount_type']    = in_array($input['discount_type'] ?? '', ['product', 'category', 'tag', 'brand'])  ? $input['discount_type']  : 'product';
            $sanitized_data['value_type']       = in_array($input['value_type'] ?? '', ['percentage', 'fixed']) ? $input['value_type']  : 'percentage';

            // Array fields
            $sanitized_data['products']     = isset($input['products'])  ? array_map('absint', (array)$input['products'])  : [];
            $sanitized_data['categories']   = isset($input['categories'])  ? array_map('absint', (array)$input['categories']) : [];
            $sanitized_data['tags']         = isset($input['tags'])  ? array_map('absint', (array)$input['tags'])  : [];
            $sanitized_data['brands']       = isset($input['brands'])   ? array_map('absint', (array)$input['brands'])   : [];
            $sanitized_data['roles']        = isset($input['roles'])  ? array_map('sanitize_key', (array)$input['roles'])  : [];

            // Date fields
            $sanitized_data['start_date']   = $this->sanitize_datetime($input['start_date'] ?? '');
            $sanitized_data['end_date']     = $this->sanitize_datetime($input['end_date'] ?? '');
        }

        update_post_meta($post_id, 'cdwc_rules', $sanitized_data);
        do_action('cdwc_discount_saved', $post_id, $sanitized_data);
}


    private function sanitize_datetime($date) {
        
        if (empty($date)) return '';

        $timestamp = strtotime($date);
        return $timestamp ? gmdate('Y-m-d H:i:s', $timestamp) : '';
        
    }    

}

