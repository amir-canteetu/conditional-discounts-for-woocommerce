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
    <?php 
        echo wp_kses($nonce_field, [
            'input' => [
                'type' => true,
                'id' => true,
                'name' => true,
                'value' => true
            ]
        ]);  
    ?>
    
    <table class="form-table widefat" id="discount-table">
        <tbody>
            <!-- Enable Discount -->
            <tr>
                <th scope="row">
                    <label for="discount_enabled">
                        <?php esc_html_e('Enable Discount', 'conditional-discounts-for-woocommerce'); ?>
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
                        <?php esc_html_e('Discount Type', 'conditional-discounts-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <select name="discount[discount_type]" id="discount_type">
                        <option value="product" <?php selected($discount_type, 'product'); ?>>
                            <?php esc_html_e('Product Discount', 'conditional-discounts-for-woocommerce'); ?>
                        </option>
                        <option value="category" <?php selected($discount_type, 'category'); ?>>
                            <?php esc_html_e('Category Discount', 'conditional-discounts-for-woocommerce'); ?>
                        </option>
                        <option value="brand" <?php selected($discount_type, 'brand'); ?>>
                            <?php esc_html_e('Brand Discount', 'conditional-discounts-for-woocommerce'); ?>
                        </option>                        
                        <option value="tag" <?php selected($discount_type, 'tag'); ?>>
                            <?php esc_html_e('Tag Discount', 'conditional-discounts-for-woocommerce'); ?>
                        </option>                        
                    </select>   
                </td>
            </tr>

            <!-- Discount Label -->
            <tr>
                <th scope="row">
                    <label for="discount_label">
                        <?php esc_html_e('Discount Label', 'conditional-discounts-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="text" name="discount[label]" value="<?php echo esc_attr($label); ?>" class="regular-text">
                    <p class="description">
                        <?php esc_html_e("Customer-facing name for this discount that will appear in the cart and checkout. Make it clear and recognizable (e.g., 'Summer Sale Discount').", 'conditional-discounts-for-woocommerce'); ?>
                    </p>
                </td>
            </tr>

            <!-- Discount Value Type -->
            <tr>
                <th scope="row">
                    <label for="value_type">
                        <?php esc_html_e('Discount Value Type', 'conditional-discounts-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <select name="discount[value_type]" id="value_type">
                        <option value="percentage" <?php selected($value_type, 'percentage'); ?>>
                            <?php esc_html_e('Percentage', 'conditional-discounts-for-woocommerce'); ?>
                        </option>
                        <option value="fixed" <?php selected($value_type, 'fixed'); ?>>
                            <?php esc_html_e('Fixed Amount', 'conditional-discounts-for-woocommerce'); ?>
                        </option>
                    </select>
                        <p class="description">
                            <?php
                            $description = sprintf(
                                /* translators: 
                                 * 1. Currency symbol (e.g. $, €) 
                                 * The %% is a literal percentage sign (needs double %% for sprintf)
                                 */
                                __("Choose how discounts are calculated:\n
                                    • Percentage: Discount applied to each eligible product's price (Example: 10%% off every qualifying product)\n
                                    Discount Cap (below) limits the total discount amount\n
                                    • Fixed Amount: Flat rate discount per eligible item (Example: %s5 off every qualifying product)", 
                                    'conditional-discounts-for-woocommerce'
                                ),
                                $currency_symbol
                            );

                            // Normalize line breaks (convert Windows and Mac line breaks to Unix style)
                            $description = str_replace(["\r\n", "\r"], "\n", $description);

                            // Replace single newlines with a single <br> tag
                            $description = preg_replace('/\n+/', '<br>', esc_html($description));

                            echo wp_kses_post($description);
                            ?>
                        </p>          
                </td>
            </tr>

            <!-- Discount Value -->
            <tr>
                <th scope="row">
                    <label for="discount_value">
                        <?php esc_html_e('Discount Value', 'conditional-discounts-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <div class="input-wrapper" id="value-input-wrapper">
                        <span class="symbol">
                            <?php echo ($value_type === 'percentage') ? '%' : esc_html($currency_symbol); ?>
                        </span>
                        <input type="number" name="discount[value]" id="discount_value" 
                               value="<?php echo esc_attr($value); ?>" 
                               class="small-text" 
                               min="0" 
                               step="0.01">
                    </div>
                </td>
            </tr>
            
            <!-- Discount Cap -->
            <tr id="discount_cap_row">
                <th scope="row">
                    <label for="discount_cap"><?php esc_html_e('Discount Cap', 'conditional-discounts-for-woocommerce'); ?></label>
                </th>
                <td>
                    <div class="input-wrapper">
                        <span class="symbol">
                            <?php echo esc_html(html_entity_decode($currency_symbol)); ?>
                        </span>                    
                        <input type="number" name="discount[discount_cap]" id="discount_cap" value="<?php echo esc_attr($discount_cap); ?>" min="0" step="any">
                    </div>
                        <p class="description">
                            <?php esc_html_e("Maximum discount amount allowed per cart when using percentage-based discounts. Enter 0 to allow unlimited discounts.", 'conditional-discounts-for-woocommerce'); ?>
                        </p>
                </td>
            </tr>            

            <!-- Apply to Products/Categories/Brands/Tags -->
            <tr class="product-field" id="product-field">
                <th scope="row">
                    <?php esc_html_e('Apply to Products', 'conditional-discounts-for-woocommerce'); ?>
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
                    <p class="description">
                       <?php esc_html_e('Select specific products to apply the discount. Leave empty to apply to <strong>all</strong> products.', 'conditional-discounts-for-woocommerce'); ?>
                   </p>                   
                </td>
            </tr>
            
            <tr class="brand-field" id="brand-field">
                <th scope="row">
                    <label><?php esc_html_e('Apply to Brands', 'conditional-discounts-for-woocommerce'); ?></label>
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
                    <p class="description">
                        <?php esc_html_e('Select specific brands to apply the discount. Leave empty to apply to <strong>all</strong> brands.', 'conditional-discounts-for-woocommerce'); ?>
                    </p>                    
                </td>
            </tr>            
            
            <tr class="category-field" id="category-field">
                <th scope="row">
                    <label><?php esc_html_e('Apply to Categories', 'conditional-discounts-for-woocommerce'); ?></label>
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
                    <p class="description">
                        <?php esc_html_e('Select specific categories to apply the discount. Leave empty to apply to <strong>all</strong> categories.', 'conditional-discounts-for-woocommerce'); ?>
                    </p>                    
                </td>
            </tr>   
            
            <tr class="tag-field" id="tag-field">
                <th scope="row">
                    <label><?php esc_html_e('Apply to Tags', 'conditional-discounts-for-woocommerce'); ?></label>
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
                    <p class="description">
                        <?php esc_html_e('Select specific tags to apply the discount. Leave empty to apply to <strong>all</strong> tags.', 'conditional-discounts-for-woocommerce'); ?>
                    </p>                    
                    
                </td>
            </tr>              
            
            <!-- User Roles -->
            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Allowed User Roles', 'conditional-discounts-for-woocommerce'); ?></label>
                </th>
                <td>
                    <div class="role-checkboxes">
                        <?php foreach (get_editable_roles() as $role => $details) : ?>
                            <label>
                                <input type="checkbox" name="discount[roles][]"  value="<?php echo esc_attr($role); ?>"  <?php checked(in_array($role, $roles)); ?>>
                                <?php echo esc_html(translate_user_role($details['name'])); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                        <div class="description" id="roles-description">
                            <p><?php esc_html_e('Restrict this discount to specific user roles. Rules:', 'conditional-discounts-for-woocommerce'); ?></p>
                            <ul>
                                <li>
                                    <?php
                                    printf(
                                        /* translators: %s: <strong> tag wrapped around "at least one" */
                                        esc_html__('Logged-in users must have %s selected role', 'conditional-discounts-for-woocommerce'),
                                        '<strong>' . esc_html__('at least one', 'conditional-discounts-for-woocommerce') . '</strong>'
                                    );
                                    ?>
                                </li>
                                <li><?php esc_html_e('Guests are excluded if any roles are selected', 'conditional-discounts-for-woocommerce'); ?></li>
                                <li>
                                    <?php
                                    printf(
                                        /* translators: %s: <strong> tag wrapped around "all users" */
                                        esc_html__('Leave all unselected to apply to %s (including guests)', 'conditional-discounts-for-woocommerce'),
                                        '<strong>' . esc_html__('all users', 'conditional-discounts-for-woocommerce') . '</strong>'
                                    );
                                    ?>
                                </li>
                                <li>
                                    <?php
                                    printf(
                                        /* translators: %s: <em> tag wrapped around "OR" */
                                        esc_html__('Uses %s logic: users need only one matching role', 'conditional-discounts-for-woocommerce'),
                                        '<em>"OR"</em>'
                                    );
                                    ?>
                                </li>
                            </ul>
                        </div>                      
                </td>
            </tr>
            
            <!-- Minimum Cart Total -->
            <tr>
                <th scope="row">
		   <label for="discount_min_cart_total"><?php esc_html_e('Minimum Cart Total', 'conditional-discounts-for-woocommerce'); ?></label>
                </th>
                <td>
                    <div class="input-wrapper">
                        <span class="symbol">
                            <?php echo esc_html(html_entity_decode($currency_symbol, ENT_QUOTES, 'UTF-8')); ?>
                        </span>                    
                        <input type="number" inputmode="numeric" pattern="[0-9]*" name="discount[min_cart_total]" id="discount_min_cart_total" value="<?php echo esc_attr($min_cart_total); ?>" min="0" step="any">
                    </div>
                    <p class="description">
                        <?php esc_html_e('The minimum cart subtotal (before discounts and taxes) required to activate this discount. Set to 0 to apply regardless of cart total.', 'conditional-discounts-for-woocommerce'); ?>
                    </p>                    
                </td>             
            </tr>   
            
            <!-- Minimum Cart Quantity -->
            <tr>
                <th scope="row">
		   <label for="discount_min_cart_quantity"><?php esc_html_e('Minimum Cart Quantity', 'conditional-discounts-for-woocommerce'); ?></label>
                </th>
                <td>
		 <input type="number" inputmode="numeric" pattern="[0-9]*" name="discount[min_cart_quantity]" id="discount_min_cart_quantity" value="<?php echo esc_attr($min_cart_quantity); ?>" min="0" step="1">
                    <p class="description">
                        <?php esc_html_e('The minimum number of items needed in the cart to qualify for this discount. Set to 0 to apply to carts of any size.', 'conditional-discounts-for-woocommerce'); ?>
                    </p>                   
                </td>
            </tr>        
            
            <!-- Maximum Uses -->
            <tr>
                <th scope="row">
		   <label for="max_use"><?php esc_html_e('Maximum Uses', 'conditional-discounts-for-woocommerce'); ?></label>
                </th>
                <td>
                    <input type="number" inputmode="numeric" pattern="[0-9]*" name="discount[max_use]" id="max_use" value="<?php echo esc_attr($max_use); ?>" min="0">
                    <p class="description">
                        <?php esc_html_e('Maximum number of times this discount can be applied. Enter 0 for unlimited uses. Applies across all customers.', 'conditional-discounts-for-woocommerce'); ?>
                    </p>  
                </td>
            </tr>     
            
            
            <!-- Maximum Uses Per User-->
            <tr>
                <th scope="row">
		    <label for="max_use_per_user"><?php esc_html_e('Max Uses Per User', 'conditional-discounts-for-woocommerce'); ?></label>
                </th>
                <td>
                <input type="number" name="discount[max_use_per_user]" id="max_use_per_user" value="<?php echo esc_attr($max_use_per_user); ?>" min="0">
                        <p class="description">
                            <?php esc_html_e('Maximum times a single user can use this discount (0 = unlimited)', 'conditional-discounts-for-woocommerce'); ?>
                        </p>
                </td>
            </tr>             

            <!-- Dates -->
            <tr>
                <th scope="row">
                    <label for="discount_start_date">
                        <?php esc_html_e('Start Date', 'conditional-discounts-for-woocommerce'); ?>
                    </label>
                </th>
                <td>
                    <input type="datetime-local" name="discount[start_date]" value="<?php echo esc_attr($start_date); ?>">
                    <p class="description">
                            <?php
                                printf(
                                    /* translators: %s: Timezone name */
                                    esc_html__('Optional date when this discount becomes active. Leave blank to start immediately. Timezone: %s', 'conditional-discounts-for-woocommerce'),
                                    esc_html($timezone)
                                );
                            ?>
                    </p>                      
                </td>
            </tr>
            
            <tr>
                <th scope="row">
		   <label for="discount_end_date"><?php esc_html_e('End Date', 'conditional-discounts-for-woocommerce'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" name="discount[end_date]" id="discount_end_date" value="<?php echo esc_attr($end_date); ?>">
                    <p class="description">
                        <?php
                            printf(
                                /* translators: %s: Timezone name */
                                esc_html__('Optional date when this discount will expire. Leave blank for no expiration. Timezone: %s', 'conditional-discounts-for-woocommerce'),
                                esc_html($timezone)
                            );
                        ?>
                    </p>  
                </td>
            </tr>               

        </tbody>
    </table>
</div>