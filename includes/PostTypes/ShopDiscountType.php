<?php

namespace Supreme\ConditionalDiscounts\PostTypes;

class ShopDiscountType {

    public function __construct() {
        add_action('init', [$this, 'register_shop_discount_post_type']);
        add_action('init', [$this, 'add_shop_discount_caps']);
    }

    public function register_shop_discount_post_type() {
        $args = [
            'labels' => [
                'name'                  => __( 'Discounts', 'conditional-discounts' ),
                'singular_name'         => __( 'Discount', 'conditional-discounts' ),
                'menu_name'             => _x( 'Discounts', 'Admin menu name', 'conditional-discounts' ),
                'add_new'               => __( 'Add discount', 'conditional-discounts' ),
                'add_new_item'          => __( 'Add new discount', 'conditional-discounts' ),
                'edit'                  => __( 'Edit', 'conditional-discounts' ),
                'edit_item'             => __( 'Edit discount', 'conditional-discounts' ),
                'new_item'              => __( 'New discount', 'conditional-discounts' ),
                'view_item'             => __( 'View discount', 'conditional-discounts' ),
                'search_items'          => __( 'Search discounts', 'conditional-discounts' ),
                'not_found'             => __( 'No discounts found', 'conditional-discounts' ),
                'not_found_in_trash'    => __( 'No discounts found in trash', 'conditional-discounts' ),
                'parent'                => __( 'Parent discount', 'conditional-discounts' ),
                'filter_items_list'     => __( 'Filter discounts', 'conditional-discounts' ),
                'items_list_navigation' => __( 'Discounts navigation', 'conditional-discounts' ),
                'items_list'            => __( 'Discounts list', 'conditional-discounts' ),
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
            'capability_type'       => 'shop_discount',
            'map_meta_cap'          => true,
            'show_in_admin_bar'     => true,
            'capabilities'          => array(
                'edit_post'           => 'edit_shop_discount',
                'read_post'           => 'read_shop_discount',
                'delete_post'         => 'delete_shop_discount',
                'edit_posts'          => 'edit_shop_discounts',
                'edit_others_posts'   => 'edit_others_shop_discounts',
                'publish_posts'       => 'publish_shop_discounts',
                'read_private_posts'  => 'read_private_shop_discounts',
            ),
        ];

        register_post_type('shop_discount', $args);
    }
    
    
    public function add_shop_discount_caps() {
    $roles = ['administrator', 'shop_manager'];
    foreach ( $roles as $role_name ) {
        $role = get_role( $role_name );
        if ( $role ) {
            $role->add_cap( 'edit_shop_discount' );
            $role->add_cap( 'read_shop_discount' );
            $role->add_cap( 'delete_shop_discount' );
            $role->add_cap( 'edit_shop_discounts' );
            $role->add_cap( 'edit_others_shop_discounts' );
            $role->add_cap( 'publish_shop_discounts' );
            $role->add_cap( 'read_private_shop_discounts' );
        }
    }
}
}
