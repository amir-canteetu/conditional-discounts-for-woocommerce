<?php

namespace Supreme\ConditionalDiscounts\Admin;

use WC_Admin_List_Table;
use Supreme\ConditionalDiscounts\Models\Discount;
use Supreme\ConditionalDiscounts\PostTypes\ShopDiscountType;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Admin_List_Table', false ) ) {
    include_once( WP_PLUGIN_DIR . '/woocommerce/includes/admin/list-tables/abstract-class-wc-admin-list-table.php' );
}

class DiscountListTable extends \WC_Admin_List_Table {

        /**
         * Post type.
         *
         * @var string
         */
        protected $list_table_type = 'shop_discount';
        private $discount_object_cache = [];     

        public function __construct() {
            parent::__construct();
            add_filter('disable_months_dropdown', '__return_true');
        }

        /**
         * Render blank state.
         */
        protected function render_blank_state() {
            echo '<div class="woocommerce-BlankState">';
            echo '<h2 class="woocommerce-BlankState-message">' . esc_html__( 'Discounts are a great way to reward your customers and boost revenues. They will appear here once created.', 'conditional-discounts-for-woocommerce' ) . '</h2>';
            echo '<a class="woocommerce-BlankState-cta button-primary button" href="' . esc_url( admin_url( 'post-new.php?post_type=shop_discount' ) ) . '">' . esc_html__( 'Create your first discount', 'conditional-discounts-for-woocommerce' ) . '</a>';
            echo '</div>';
        } 
        
	/**
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_columns( $columns ) {
		$show_columns                   = [];
		$show_columns['cb']             = $columns['cb'];
		$show_columns['name']           = __( 'Name', 'conditional-discounts-for-woocommerce' );
//                $show_columns['type']           = __( 'Type', 'conditional-discounts-for-woocommerce' );
//		$show_columns['amount']         = __( 'Amount', 'conditional-discounts-for-woocommerce' );
//		$show_columns['products']       = __( 'Product IDs', 'conditional-discounts-for-woocommerce' );
//		$show_columns['categories']     = __( 'Categories', 'conditional-discounts-for-woocommerce' );
//                $show_columns['dates']          = __( 'Validity period', 'conditional-discounts-for-woocommerce' );

		return $show_columns;
	} 
        
        //runs in WC_Admin_List_Table::render_columns
	protected function prepare_row_data( $post_id ) {
            $this->object = $this->get_discount_object($post_id);

	}  

        private function get_discount_object($post_id): Discount {
            if (!isset($this->discount_object_cache[$post_id])) {
                $this->discount_object_cache[$post_id] = new Discount($post_id);
            }
            return $this->discount_object_cache[$post_id];
        } 
        
        
        public function render_columns($column, $post_id) {
                $discount = $this->get_discount_object($post_id);
                
//                write_log("/includes/Admin/DiscountListTable.php:render_columns:74"); 
//                write_log($discount); 

                switch($column) {
                    case 'name':
                        $this->render_name_column($discount);
                        break;
//                    case 'type':
//                        $this->render_type_column($discount);
//                        break;
//                    case 'amount':
//                        $this->render_amount_column($discount);
//                        break;
//                    case 'products':
//                        $this->render_products_column($discount);
//                        break;
//                    case 'categories':
//                        $this->render_categories_column($discount);
//                        break;   
//                    case 'dates':
//                        $this->render_dates_column($discount);
//                        break;                         
                }
        }        
        
        protected function render_name_column(Discount $discount) {
            
           $edit_link       = get_edit_post_link($discount->get_id());
           $title           = $discount->get_label();
            
           printf(
               '<strong><a class="row-title" href="%s">%s</a></strong>%s',
               esc_url($edit_link),
               esc_html($title),
               $this->row_actions($this->get_row_actions($discount))
           );
        }

        protected function get_row_actions($actions, $post): array {
                $discount = $this->get_discount_object($post->ID); 
                $actions = [
                   'edit' => sprintf(
                       '<a href="%s">%s</a>',
                       get_edit_post_link($discount->get_id()),
                       __('Edit', 'conditional-discounts-for-woocommerce')
                   ),
                   'duplicate' => sprintf(
                       '<a href="%s">%s</a>',
                       wp_nonce_url(admin_url('admin.php?action=cdw_duplicate&post='.$discount->get_id()), 'cdw-duplicate'),
                       __('Duplicate', 'conditional-discounts-for-woocommerce')
                   )
                ];

                return apply_filters('cdw_discount_row_actions', $actions, $discount);
        }       
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        












    }