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
    
    <table class="form-table widefat">
        <tbody>
            <!-- Enable Discount -->
            <tr>
                <th scope="row">
                    <label for="discount_enabled">
                        <?php _e('Enable Discount', 'conditional-discounts'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" name="discount[enabled]" id="discount_enabled" <?php checked($enabled, true); ?>>
                </td>
            </tr>

            <!-- Discount Type -->
            <tr>
                <th scope="row">
                    <label for="discount_type">
                        <?php _e('Discount Type', 'conditional-discounts'); ?>
                    </label>
                </th>
                <td>
                    <select name="discount[discount_type]" id="discount_type">
                        <option value="product" <?php selected($discount_type, 'product'); ?>>
                            <?php _e('Product Discount', 'conditional-discounts'); ?>
                        </option>
                        <option value="category" <?php selected($discount_type, 'category'); ?>>
                            <?php _e('Category Discount', 'conditional-discounts'); ?>
                        </option>
                        <option value="brand" <?php selected($discount_type, 'brand'); ?>>
                            <?php _e('Brand Discount', 'conditional-discounts'); ?>
                        </option>                        
                        <option value="tag" <?php selected($discount_type, 'tag'); ?>>
                            <?php _e('Tag Discount', 'conditional-discounts'); ?>
                        </option>                        
                    </select>   
                </td>
            </tr>

            <!-- Discount Label -->
            <tr>
                <th scope="row">
                    <label for="discount_label">
                        <?php _e('Discount Label', 'conditional-discounts'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" name="discount[label]" value="<?php echo esc_attr($label); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('This will be shown on the cart', 'conditional-discounts'); ?>
                    </p>
                </td>
            </tr>

            <!-- Value Type -->
            <tr>
                <th scope="row">
                    <label for="value_type">
                        <?php _e('Value Type', 'conditional-discounts'); ?>
                    </label>
                </th>
                <td>
                    <select name="discount[value_type]" id="value_type">
                        <option value="percentage" <?php selected($value_type, 'percentage'); ?>>
                            <?php _e('Percentage', 'conditional-discounts'); ?>
                        </option>
                        <option value="fixed" <?php selected($value_type, 'fixed'); ?>>
                            <?php _e('Fixed Amount', 'conditional-discounts'); ?>
                        </option>
                    </select>
                </td>
            </tr>

            <!-- Discount Value -->
            <tr>
                <th scope="row">
                    <label for="discount_value">
                        <?php _e('Discount Value', 'conditional-discounts'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" name="discount[value]" id="discount_value" value="<?php echo esc_attr($value); ?>" class="small-text" min="0" step="0.01">
                    <span class="description">
                        <?php echo ($value_type === 'percentage') ? '%' : get_woocommerce_currency_symbol(); ?>
                    </span>
                </td>
            </tr>
            
            <!-- Discount Cap -->
            <tr>
                <th scope="row">
                    <label for="discount_cap"><?php _e('Discount Cap', 'conditional-discounts'); ?></label>
                </th>
                <td>
                    <input type="number" name="discount[discount_cap]" id="discount_cap" value="<?php echo esc_attr($discount_cap); ?>" min="0">
                    <p class="description">
                        <?php _e('0 for unlimited cap', 'conditional-discounts'); ?>
                    </p>
                </td>
            </tr>            

            <!-- Apply to Products/Categories/Brands/Tags -->
            <tr class="product-field" id="product-field">
                <th scope="row">
                    <?php _e('Apply to Products', 'conditional-discounts'); ?>
                </th>
                <td>
                    <select name="discount[products][]" 
                            class="cdwc-product-search" 
                            multiple="multiple"
                            data-placeholder="<?php echo esc_attr($labels['search_products']); ?>">
                        <?php foreach ($selected_products as $id => $name) : ?>
                            <option value="<?php echo esc_attr($id); ?>" selected="selected">
                                <?php echo esc_html($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr class="brand-field" id="brand-field">
                <th scope="row">
                    <label><?php _e('Apply to Brands', 'conditional-discounts'); ?></label>
                </th>
                <td>
                    <select name="discount[brands][]" 
                            class="cdwc-taxonomy-search" 
                            multiple="multiple"
                            data-placeholder="<?php echo esc_attr($labels['search_brands']); ?>"
                            data-taxonomy="product_brand">
                            <?php foreach ($brands as $id) : 
                            $term = get_term($id);
                            if ($term) : ?>
                                <option value="<?php echo esc_attr($id); ?>" selected>
                                    <?php echo esc_html($term->name); ?>
                                </option>
                            <?php endif;
                        endforeach; ?>
                    </select>
                </td>
            </tr>            
            
            <tr class="category-field" id="category-field">
                <th scope="row">
                    <label><?php _e('Apply to Categories', 'conditional-discounts'); ?></label>
                </th>
                <td>
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
                </td>
            </tr>   
            
            <tr class="tag-field" id="tag-field">
                <th scope="row">
                    <label><?php _e('Apply to Tags', 'conditional-discounts'); ?></label>
                </th>
                <td>
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
                </td>
            </tr>              
            
            <!-- User Roles -->
            <tr>
                <th scope="row">
                    <label><?php _e('Allowed User Roles', 'conditional-discounts'); ?></label>
                </th>
                <td>
                    <div class="role-checkboxes">
                        <?php foreach (get_editable_roles() as $role => $details) : ?>
                            <label>
                                <input type="checkbox" name="discount[roles][]"  value="<?php echo esc_attr($role); ?>"  <?php checked(in_array($role, $roles)); ?>>
                                <?php echo translate_user_role($details['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </td>
            </tr>
            
            <!-- Minimum Cart Total -->
            <tr>
                <th scope="row">
		   <label for="discount_min_cart_total"><?php _e('Minimum Cart Total', 'conditional-discounts'); ?></label>
                </th>
                <td>
		 <input type="number" name="discount[min_cart_total]" id="discount_min_cart_total" value="<?php echo esc_attr($min_cart_total); ?>" min="0" step="0.01">
                </td>
            </tr>   
            
            <!-- Minimum Cart Quantity -->
            <tr>
                <th scope="row">
		   <label for="discount_min_cart_quantity"><?php _e('Minimum Cart Quantity', 'conditional-discounts'); ?></label>
                </th>
                <td>
		 <input type="number" name="discount[min_cart_quantity]" id="discount_min_cart_quantity" value="<?php echo esc_attr($min_cart_quantity); ?>" min="0">
                </td>
            </tr>        
            
            <!-- Maximum Uses -->
            <tr>
                <th scope="row">
		   <label for="max_use"><?php _e('Maximum Uses', 'conditional-discounts'); ?></label>
                </th>
                <td>
                    <input type="number" name="discount[max_use]" id="max_use" value="<?php echo esc_attr($max_use); ?>" min="0">
                    <p class="description">
                        <?php _e('0 for unlimited uses', 'conditional-discounts'); ?>
                    </p>
                </td>
            </tr>                        

            <!-- Dates -->
            <tr>
                <th scope="row">
                    <label for="discount_start_date">
                        <?php _e('Start Date', 'conditional-discounts'); ?>
                    </label>
                </th>
                <td>
                    <input type="datetime-local" name="discount[start_date]" value="<?php echo esc_attr($start_date); ?>">
                </td>
            </tr>
            
            <tr>
                <th scope="row">
		   <label for="discount_end_date"><?php _e('End Date', 'conditional-discounts'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" name="discount[end_date]" id="discount_end_date" value="<?php echo esc_attr($end_date); ?>">
                </td>
            </tr>               

        </tbody>
    </table>
</div>