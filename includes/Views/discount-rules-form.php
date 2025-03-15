<?php
/** 
 * Discount Rules Form Template
 * 
 * @var array $data Template data containing:
 * - array $discount Processed discount rules
 * - WP_Post $post Current post object
 * - string $nonce_field Security nonce field
 * - string $currency_symbol Currency symbol
 */
?>
        <div class="discount-rules-container">
            <?php echo $nonce_field; ?>

            <div class="options-group">
                
                <div class="form-field">
                    <label for="discount_enabled">
                        <input type="checkbox" name="discount[enabled]" id="discount_enabled" <?php checked($enabled, true); ?>>
                        <?php _e('Enable this discount', 'conditional-discounts'); ?>
                    </label>
                </div>
                
                <div class="form-field">
                    <label for="discount_type"><?php _e('Discount Type', 'conditional-discounts'); ?></label>
                    <select name="discount[discount_type]" id="discount_type">
                        <option value="product" <?php selected($discount_type, 'product'); ?>>
                            <?php _e('Product Discount', 'conditional-discounts'); ?>
                        </option>
                        <option value="category" <?php selected($discount_type, 'category'); ?>>
                            <?php _e('Category Discount', 'conditional-discounts'); ?>
                        </option>
                        <option value="tag" <?php selected($discount_type, 'tag'); ?>>
                            <?php _e('Tag Discount', 'conditional-discounts'); ?>
                        </option>                        
                    </select>
                </div> 

                <div class="form-field">
                    <label for="discount_label"><?php _e('Discount Label', 'conditional-discounts'); ?></label>
                    <input type="text" name="discount[label]" value="<?php echo esc_attr($label); ?>" class="widefat">
                </div>
                
                <p class="description">
                    <?php _e('This will be shown on the cart', 'conditional-discounts'); ?>
                </p>    

                
                <div class="form-field">
                    <label for="value_type"><?php _e('Discount Value Type', 'conditional-discounts'); ?></label>
                    <select name="discount[value_type]" id="value_type">
                        <option value="percentage" <?php selected($value_type, 'percentage'); ?>>
                            <?php _e('Percentage', 'conditional-discounts'); ?>
                        </option>
                        <option value="fixed" <?php selected($value_type, 'fixed'); ?>>
                            <?php _e('Fixed Amount', 'conditional-discounts'); ?>
                        </option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="discount_value"><?php _e('Discount Value', 'conditional-discounts'); ?></label>
                    <input type="number" name="discount[value]" id="discount_value"  value="<?php echo esc_attr($value); ?>"  min="0" step="0.01">
                    <span class="description">
                        <?php echo ($value_type === 'percentage') ? '%' : get_woocommerce_currency_symbol(); ?>
                    </span>
                </div>

                <div class="form-field">
                    <label for="discount_cap"><?php _e('Discount Cap', 'conditional-discounts'); ?></label>
                    <input type="number" name="discount[discount_cap]" id="discount_cap" value="<?php echo esc_attr($discount_cap); ?>" min="0">
                    <p class="description">
                        <?php _e('0 for unlimited cap', 'conditional-discounts'); ?>
                    </p>
                </div>   
                

                <div class="form-field">
                    <label><?php _e('Apply to Products', 'conditional-discounts'); ?></label>
                    <select name="discount[products][]" 
                            class="cdwc-product-search" 
                            multiple="multiple"
                            data-placeholder="<?php echo esc_attr($labels['search_products']); ?>"
                            data-taxonomy="product">
                        <?php foreach ($products as $id => $name) : ?>
                            <option value="<?php echo esc_attr($id); ?>" selected>
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label><?php _e('Apply to Categories', 'conditional-discounts'); ?></label>
                    <select name="discount[categories][]" 
                            class="cdwc-taxonomy-search" 
                            multiple="multiple"
                            data-placeholder="<?php echo esc_attr($labels['search_cats']); ?>"
                            data-taxonomy="product_cat">
                        <?php foreach ($categories as $id) : 
                            $term = get_term($id); 
                            if ($term) : ?>
                                <option value="<?php echo esc_attr($id); ?>" selected>
                                    <?php echo esc_html($term->name); ?>
                                </option>
                            <?php endif;
                        endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                   <label><?php _e('Apply to Tags', 'conditional-discounts'); ?></label>
                   <select name="discount[tags][]" 
                           class="cdwc-taxonomy-search" 
                           multiple="multiple"
                           data-placeholder="<?php echo esc_attr($labels['search_tags']); ?>"
                           data-taxonomy="product_tag">
                       <?php foreach ($tags as $id) : 
                           $term = get_term($id);
                           if ($term) : ?>
                               <option value="<?php echo esc_attr($id); ?>" selected>
                                   <?php echo esc_html($term->name); ?>
                               </option>
                           <?php endif;
                       endforeach; ?>
                   </select>
               </div>                  

                <div class="form-field">
                    <label><?php _e('Allowed User Roles', 'conditional-discounts'); ?></label>
                    <div class="role-checkboxes">
                        <?php foreach (get_editable_roles() as $role => $details) : ?>
                            <label>
                                <input type="checkbox" name="discount[roles][]"  value="<?php echo esc_attr($role); ?>"  <?php checked(in_array($role, $roles)); ?>>
                                <?php echo translate_user_role($details['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-field">
                    <label for="discount_min_cart_total"><?php _e('Minimum Cart Total', 'conditional-discounts'); ?></label>
                    <input type="number" name="discount[min_cart_total]" id="discount_min_cart_total" value="<?php echo esc_attr($min_cart_total); ?>" min="0" step="0.01">
                </div>

                <div class="form-field">
                    <label for="discount_min_cart_quantity"><?php _e('Minimum Cart Quantity', 'conditional-discounts'); ?></label>
                    <input type="number" name="discount[min_cart_quantity]" id="discount_min_cart_quantity" value="<?php echo esc_attr($min_cart_quantity); ?>" min="0">
                </div>                 
                
                <div class="form-field">
                    <label for="max_use"><?php _e('Maximum Uses', 'conditional-discounts'); ?></label>
                    <input type="number" name="discount[max_use]" id="max_use" value="<?php echo esc_attr($max_use); ?>" min="0">
                    <p class="description">
                        <?php _e('0 for unlimited uses', 'conditional-discounts'); ?>
                    </p>
                </div>                

                <div class="form-field">
                    <label for="discount_start_date"><?php _e('Start Date', 'conditional-discounts'); ?></label>
                    <input type="datetime-local" name="discount[start_date]" value="<?php echo esc_attr($start_date); ?>">
                </div>

                <div class="form-field">
                    <label for="discount_end_date"><?php _e('End Date', 'conditional-discounts'); ?></label>
                    <input type="datetime-local" name="discount[end_date]" id="discount_end_date" value="<?php echo esc_attr($end_date); ?>">
                </div>                 
                
            </div>


        </div>