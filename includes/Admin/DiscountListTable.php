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
            $this->table_name = $GLOBALS['wpdb']->prefix . 'cdwc_discount_rules';
        }
        
        
        /**
         * Render blank state.
         */
        protected function render_blank_state() {
            echo '<div class="woocommerce-BlankState">';
            echo '<h2 class="woocommerce-BlankState-message">' . esc_html__( 'Discounts are a great way to reward your customers and boost revenues. They will appear here once created.', 'conditional-discounts' ) . '</h2>';
            echo '<a class="woocommerce-BlankState-cta button-primary button" href="' . esc_url( admin_url( 'post-new.php?post_type=shop_discount' ) ) . '">' . esc_html__( 'Create your first discount', 'conditional-discounts' ) . '</a>';
            echo '</div>';
        } 

	protected function render_filters() {
		?>
		<select name="discount_type" id="dropdown_shop_discount_type">
			<option value=""><?php esc_html_e( 'Show all types', 'conditional-discounts' ); ?></option>
			<?php
			$discount_type = Discount::get_discount_types();

			foreach ( $discount_type as $name => $type ) {
				echo '<option value="' . esc_attr( $name ) . '"';

				if ( isset( $_GET['discount_type'] ) ) {  
					selected( $name, sanitize_text_field( wp_unslash( $_GET['discount_type'] ) ) );  
				}

				echo '>' . esc_html( $type ) . '</option>';
			}
			?>
		</select>
		<?php
	}        
        
        
        public function get_columns() {
            return [
                'cb'        => '<input type="checkbox" />', // Checkbox for bulk actions
                'label'     => __('Label', 'conditional-discounts'),
                'type'      => __('Type', 'conditional-discounts'),
                'value'     => __('Value', 'conditional-discounts'),
                'dates'     => __('Validity', 'conditional-discounts'),
                'status'    => __('Status', 'conditional-discounts'),
            ];
        }  
        
        public function prepare_items() {
            global $wpdb;

            $per_page = 20;
            $current_page = $this->get_pagenum();

            // Query your custom table
            $query = $wpdb->prepare(
                "SELECT * FROM {$this->table_name} 
                 ORDER BY discount_id DESC 
                 LIMIT %d OFFSET %d",
                $per_page,
                ($current_page - 1) * $per_page
            );

            $this->items = $wpdb->get_results($query, ARRAY_A);

            // Set pagination
            $total_items = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
            $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page'    => $per_page,
            ]);
        }    
        
        
        protected function column_label($item) {
            $edit_url = admin_url("post.php?post={$item['discount_id']}&action=edit");
            return sprintf(
                '<strong><a href="%s">%s</a></strong>',
                $edit_url,
                esc_html($item['label'])
            );
        }

        protected function column_status($item) {
            return $item['enabled'] 
                ? '<span class="status-enabled">' . __('Enabled', 'conditional-discounts') . '</span>'
                : '<span class="status-disabled">' . __('Disabled', 'conditional-discounts') . '</span>';
        }    
        
        // Define bulk actions
        public function get_bulk_actions() {
            return [
                'enable'  => __('Enable', 'conditional-discounts'),
                'disable' => __('Disable', 'conditional-discounts'),
                'delete'  => __('Delete', 'conditional-discounts'),
            ];
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
		$show_columns['name']           = __( 'Name', 'conditional-discounts' );
//                $show_columns['type']           = __( 'Type', 'conditional-discounts' );
//		$show_columns['amount']         = __( 'Amount', 'conditional-discounts' );
//		$show_columns['products']       = __( 'Product IDs', 'conditional-discounts' );
//		$show_columns['categories']     = __( 'Categories', 'conditional-discounts' );
//                $show_columns['dates']          = __( 'Validity period', 'conditional-discounts' );

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
                       __('Edit', 'conditional-discounts')
                   ),
                   'duplicate' => sprintf(
                       '<a href="%s">%s</a>',
                       wp_nonce_url(admin_url('admin.php?action=cdw_duplicate&post='.$discount->get_id()), 'cdw-duplicate'),
                       __('Duplicate', 'conditional-discounts')
                   )
                ];

                return apply_filters('cdw_discount_row_actions', $actions, $discount);
        }       
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        












    }