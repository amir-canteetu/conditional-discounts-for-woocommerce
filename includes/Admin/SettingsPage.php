<?php

namespace Supreme\ConditionalDiscounts\Admin;

use WC_Settings_Page;
use WC_Admin_Settings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


    /**
     * Class Settings
     *
     * Handles the registration and rendering of plugin settings in the WooCommerce Settings tabs.
     */
    class SettingsPage extends WC_Settings_Page {

        /**
         * Constructor.
         *
         * Registers the settings page with WooCommerce.
         */
        public function __construct() {
            $this->id    = 'conditional_discounts';
            $this->label = __('Conditional Discounts', 'conditional-discounts-for-woocommerce');

            add_filter('woocommerce_get_sections_' . $this->id, [$this, 'get_sections']);
            add_filter('woocommerce_get_settings_' . $this->id, [$this, 'get_settings'], 10, 2);  
            add_filter('woocommerce_admin_settings_sanitize_option', [$this, 'validate_fields_by_type'], 10, 3);   
            
            parent::__construct();
        } 


        /**
         * Get Sections
         *
         * Returns the sections for the settings page.
         *
         * @return array
         */
        public function get_sections() {
            return [
                ''                => __('General', 'conditional-discounts-for-woocommerce'),
                'cart_discounts'  => __('Cart Discounts', 'conditional-discounts-for-woocommerce'),
                'product_discounts' => __('Product Discounts', 'conditional-discounts-for-woocommerce'),
            ];
        }

        /**
         * Get Settings
         *
         * Returns the settings for the current section.
         *
         * @param string $current_section The current section ID.
         * @return array
         */
        public function get_settings($current_section = '') {
            $settings = [];

            switch ($current_section) {
                case 'cart_discounts':
                    $settings = $this->get_cart_discount_settings();
                    break;

                case 'product_discounts':
                    $settings = $this->get_product_discount_settings();
                    break;

                default:
                    $settings = $this->get_general_settings();
                    break;
            }

            return $settings;
        }

        /**
         * General Settings
         *
         * @return array
         */
        private function get_general_settings() {
            return [
                [
                    'title'    => __('General (Store-Wide) Discounts', 'conditional-discounts-for-woocommerce'),
                    'type'     => 'title',
                    'desc'     => __('Configure general discount rules that apply across the store.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discounts_section',
                ],
                [
                    'title'    => __('Enable General Discounts', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Enable or disable general discounts for your store.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_enable_general_discounts',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                ],
                [
                    'title'    => __('Minimum Cart Total', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set a minimum cart total required for the discount to apply.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discounts_minimum_cart_total',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => __('Enter a value in your store\'s currency.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'title'    => __('Minimum Cart Quantity', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Apply discount when the number of items in the cart exceeds this value.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discounts_minimum_cart_quantity',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => true,
                    'custom_attributes' => [
                        'min' => '0',
                    ],
                ],                
                [
                    'title'    => __('Discount Type', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Choose whether the discount is a percentage or a fixed amount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discount_type',
                    'default'  => 'percentage',
                    'type'     => 'select',
                    'options'  => [
                        'percentage' => __('Percentage', 'conditional-discounts-for-woocommerce'),
                        'fixed'      => __('Fixed Amount', 'conditional-discounts-for-woocommerce'),
                    ],
                ],
                [
                    'title'    => __('Discount Value', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Enter the discount value.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discount_value',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => true,
                    'custom_attributes' => [
                        'min' => '0',
                        'step' => '0.01',
                    ],
                ], 
                [
                    'title'    => __('Discount Combinability', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Allow this discount to combine with other discounts.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discounts_combinability',
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                ],
                [
                    'title'    => __('Global Discount Cap', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set a maximum discount amount for this discount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discounts_discount_cap',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => __('Enter a maximum discount value, e.g., 50 for $50. Leave blank to disable.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'title'    => __('Global Discount Label', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set a label for this discount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discounts_discount_label',
                    'default'  => 'Store-wide Discount',
                    'type'     => 'text',
                    'desc_tip' => __('Enter discount label.', 'conditional-discounts-for-woocommerce'),
                ],            
                [
                    'title'    => __('General Discount Validity Start Date', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set the start date for the discount validity.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discount_start_date',
                    'default'  => '',
                    'type'     => 'date',
                    'desc_tip' => __('Select the starting date for the discount to be valid.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'title'    => __('General Discount Validity End Date', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set the end date for the discount validity.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_general_discount_end_date',
                    'default'  => '',
                    'type'     => 'date',
                    'desc_tip' => __('Select the ending date for the discount to be valid.', 'conditional-discounts-for-woocommerce'),
                ],             
                [
                    'type'     => 'sectionend',
                    'id'       => 'cdwc_general_discounts_section',
                ],
            ];
        }

        /**
         * Cart Discount Settings
         *
         * @return array
         */
        private function get_cart_discount_settings() {
            return [
                [
                    'title'    => __('Cart-Based Discounts', 'conditional-discounts-for-woocommerce'),
                    'type'     => 'title',
                    'desc'     => __('Set up discounts based on the contents of the shopping cart.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_section',
                ],
                [
                    'title'    => __('Enable Cart-Based Discounts', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Enable or disable cart-based discounts for your store.','conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_enable',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                ],
                [
                    'title'    => __('Minimum Cart Total', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set a minimum cart total required for the discount to apply.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_minimum_cart_total',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => __('Enter a value in your store\'s currency.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'title'    => __('Minimum Cart Quantity', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Apply discount when the number of items in the cart exceeds this value.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_minimum_cart_quantity',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => true,
                    'custom_attributes' => [
                        'min' => '0',
                    ],
                ],
                [
                    'title'    => __('Discount Type', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Choose whether the discount is a percentage or a fixed amount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_type',
                    'default'  => 'percentage',
                    'type'     => 'select',
                    'options'  => [
                        'percentage' => __('Percentage', 'conditional-discounts-for-woocommerce'),
                        'fixed'      => __('Fixed Amount', 'conditional-discounts-for-woocommerce'),
                    ],
                ],
                [
                    'title'    => __('Discount Value', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Enter the discount value.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_value',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => true,
                    'custom_attributes' => [
                        'min' => '0',
                        'step' => '0.01',
                    ],
                ],                  
                [
                    'title'    => __('Cart Discount Label', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set a label for this discount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_label',
                    'default'  => 'Cart Discount',
                    'type'     => 'text',
                    'desc_tip' => __('Enter discount label.', 'conditional-discounts-for-woocommerce'),
                ],                         
                [
                    'title'    => __('Cart Discount Validity Start Date', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set the start date for the discount validity.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_start_date',
                    'default'  => '',
                    'type'     => 'date',
                    'desc_tip' => __('Select the starting date for the discount to be valid.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'title'    => __('Cart Discount Validity End Date', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set the end date for the discount validity.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_cart_discount_end_date',
                    'default'  => '',
                    'type'     => 'date',
                    'desc_tip' => __('Select the ending date for the discount to be valid.', 'conditional-discounts-for-woocommerce'),
                ],            
                [
                    'type'     => 'sectionend',
                    'id'       => 'cdwc_cart_based_discounts_section',
                ],
            ];
        }
    

        /**
         * Product Discount Settings
         *
         * @return array
         */
        private function get_product_discount_settings() {
            return [
                [
                    'title'    => __('Product-Based Discounts', 'conditional-discounts-for-woocommerce'),
                    'type'     => 'title',
                    'desc'     => __('Set up discounts based on specific products or product categories.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_discount_section',
                ],
                [
                    'title'    => __('Enable Product-Based Discounts', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Enable or disable product-based discounts for your store.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_enable_product_discounts',
                    'default'  => 'no',
                    'type'     => 'checkbox',
                ],
                [
                    'title'    => __('Select Products for Discount', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Choose specific products to apply the discount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_select_discounted_products',
                    'default'  => '',
                    'type'     => 'multiselect',
                    'class'    => 'wc-enhanced-select',
                    'options'  => $this->get_products_list(),
                ],
                [
                    'title'    => __('Select Categories for Discount', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Choose product categories to apply the discount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_select_discounted_categories',
                    'default'  => '',
                    'type'     => 'multiselect',
                    'class'    => 'wc-enhanced-select',
                    'options'  => $this->get_categories_list(),
                ],
                [
                    'title'    => __('Minimum Cart Total', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set a minimum cart total required for the discount to apply.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_minimum_cart_total',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => __('Enter a value in your store\'s currency.', 'conditional-discounts-for-woocommerce'),
                ],  
                [
                    'title'    => __('Minimum Cart Quantity', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Apply discount when the number of items in the cart exceeds this value.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_min_cart_quantity',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => true,
                    'custom_attributes' => [
                        'min' => '0',
                    ],
                ],                               
                [
                    'title'    => __('Discount Type', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Choose whether the discount is a percentage or a fixed amount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_discount_type',
                    'default'  => 'percentage',
                    'type'     => 'select',
                    'options'  => [
                        'percentage' => __('Percentage', 'conditional-discounts-for-woocommerce'),
                        'fixed'      => __('Fixed Amount', 'conditional-discounts-for-woocommerce'),
                    ],
                ],
                [
                    'title'    => __('Discount Value', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Enter the discount value. ', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_discount_value',
                    'default'  => '',
                    'type'     => 'number',
                    'desc_tip' => true,
                    'custom_attributes' => [
                        'min' => '0',
                        'step' => '0.01',
                    ],
                    'desc_tip' => __('When this is a fixed amount, the total discount will be multiplied by the quantity of eligible goods.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'title'    => __('Product Discount Label', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set a label for this discount.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_discount_label',
                    'default'  => 'Cart Discount',
                    'type'     => 'text',
                    'desc_tip' => __('Enter discount label.', 'conditional-discounts-for-woocommerce'),
                ],                 
                [
                    'title'    => __('Product Discount Validity Start Date', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set the start date for the discount validity.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_discount_start_date',
                    'default'  => '',
                    'type'     => 'date',
                    'desc_tip' => __('Select the starting date for the discount to be valid.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'title'    => __('Product Discount Validity End Date', 'conditional-discounts-for-woocommerce'),
                    'desc'     => __('Set the end date for the discount validity.', 'conditional-discounts-for-woocommerce'),
                    'id'       => 'cdwc_product_discount_end_date',
                    'default'  => '',
                    'type'     => 'date',
                    'desc_tip' => __('Select the ending date for the discount to be valid.', 'conditional-discounts-for-woocommerce'),
                ],
                [
                    'type'     => 'sectionend',
                    'id'       => 'cdwc_product_based_discounts_section',
                ],
            ];
        }
        
        private function get_products_list() {
            $products = wc_get_products(['limit' => -1]);
            $options = [];
            foreach ($products as $product) {
                $options[$product->get_id()] = $product->get_name();
            }
            return $options;
        }
        
        private function get_categories_list() {
            $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
            $options = [];
            foreach ($categories as $category) {
                $options[$category->term_id] = $category->name;
            }
            return $options;
        }

        public function validate_fields_by_type($value, $option, $raw_value) {
            switch ($option['type']) {
                case 'date':
                    if (empty($raw_value)) {
                        WC_Admin_Settings::add_error( __('Date fields are required.', 'conditional-discounts-for-woocommerce') );
                        $value = '';
                    }                                      
            }
            return $value;
        }
     
    }

    return new SettingsPage();



