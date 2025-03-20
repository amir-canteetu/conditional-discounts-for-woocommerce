<?php

namespace Supreme\ConditionalDiscounts;

class PluginActions {
    
    
        public function __construct() {
                    add_filter('post_updated_messages', [$this, 'conditional_update_notices']);
        }   
        
        
        
        public function conditional_update_notices($messages) {
            
            $post = get_post();

            $messages['shop_discount'] = array(
                    0  => '', // Unused. Messages start at index 1.
                    1  => __( 'Discount updated.', 'conditional-discounts-for-woocommerce' ),
                    2  => __( 'Discount field updated.', 'conditional-discounts-for-woocommerce' ),
                    3  => __( 'Discount field deleted.', 'conditional-discounts-for-woocommerce' ),
                    4  => __( 'Discount updated.', 'conditional-discounts-for-woocommerce' ),
                    5  => __( 'Revision restored.', 'conditional-discounts-for-woocommerce' ),
                    6  => __( 'Discount updated.', 'conditional-discounts-for-woocommerce' ),
                    7  => __( 'Discount saved.', 'conditional-discounts-for-woocommerce' ),
                    8  => __( 'Discount submitted.', 'conditional-discounts-for-woocommerce' ),
                    9  => sprintf(
                            /* translators: %s: date */
                            __( 'Discount scheduled for: %s.', 'conditional-discounts-for-woocommerce' ),
                            '<strong>' . date_i18n( __( 'M j, Y @ G:i', 'conditional-discounts-for-woocommerce' ), strtotime( $post->post_date ) ) . '</strong>'
                    ),
                    10 => __( 'Discount draft updated.', 'conditional-discounts-for-woocommerce' ),
            );

            return $messages;
        }        
        
        
        
        
        
        
        
        
        
        
        
    
    
    
}