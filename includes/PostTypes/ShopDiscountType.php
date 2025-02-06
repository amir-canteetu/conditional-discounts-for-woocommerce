<?php

namespace Supreme\ConditionalDiscounts\PostTypes;

class ShopDiscountType {

    public function __construct() {
        add_action('init', [$this, 'register_shop_discount_post_type']);
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
            'supports'              => ['title', 'editor'],
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
}
