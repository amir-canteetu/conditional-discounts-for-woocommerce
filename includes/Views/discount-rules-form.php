<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<table class="form-table" id="discount-settings-form-table">
    <?php if (!empty($cdwc_template['errors'])) : ?>
    <tr valign="top">
        <td colspan="2">
            <div id="discount-errors" class="notice notice-error" style="display: block; margin: 15px 0;">
                <?php foreach ($cdwc_template['errors'] as $error) : ?>
                    <p><?php echo esc_html($error); ?></p>
                <?php endforeach; ?>
            </div>
        </td>
    </tr>
    <?php endif; ?>
    
    <tr valign="top">
        <th scope="row"><label for="discount_type"><?php esc_html_e('Discount Type', 'conditional-discounts'); ?></label></th>
        <td>
            <select id="discount_type" name="discount_type">
                <option value="product" <?php selected($discount->get_type(), 'product'); ?>>
                    <?php esc_html_e('Product', 'conditional-discounts'); ?>
                </option>
                <option value="category" <?php selected($discount->get_type(), 'category'); ?>>
                    <?php esc_html_e('Category', 'conditional-discounts'); ?>
                </option>
                <option value="tag" <?php selected($discount->get_type(), 'tag'); ?>>
                    <?php esc_html_e('Tag', 'conditional-discounts'); ?>
                </option>                
            </select>
        </td>
    </tr>    

    <tr valign="top">
        <th scope="row"><label for="enable_discount"><?php esc_html_e('Enable Discount', 'conditional-discounts'); ?></label></th>
        <td>
            <input type="checkbox" id="enable_discount" name="enable_discount" value="1" 
                <?php checked($cdwc_template['discount']->is_enabled(), 1); ?> />
        </td>
    </tr>
    
    <tr valign="top">
        <th scope="row"><label for="discount_label"><?php esc_html_e('Discount Label', 'conditional-discounts'); ?></label></th>
        <td>
            <input type="text" id="discount_label" name="discount_label" class="regular-text" 
                value="<?php echo esc_attr($cdwc_template['discount']->get_label()); ?>" />
        </td>
    </tr>    

    <tr valign="top">
        <th scope="row"><label for="products_for_discount"><?php esc_html_e('Products for Discount', 'conditional-discounts'); ?></label></th>
        <td>
            <select id="products_for_discount" name="products_for_discount[]" multiple="multiple" style="min-width: 200px;">
                <?php foreach ($cdwc_template['products'] as $id => $title) : ?>
                    <option value="<?php echo esc_attr($id); ?>"
                        <?php selected(in_array($id, $cdwc_template['discount']->get_products())); ?>>
                        <?php echo esc_html($title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    
    <tr valign="top">
        <th scope="row"><label for="categories_for_discount"><?php esc_html_e('Categories for Discount', 'conditional-discounts'); ?></label></th>
        <td>
            <select id="categories_for_discount" name="categories_for_discount[]" multiple="multiple" style="<?php echo esc_attr('min-width: 200px;'); ?>">
                <?php foreach ($cdwc_template['categories'] as $slug => $name) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"
                        <?php selected(in_array($slug, $cdwc_template['discount']->get_categories())); ?>>
                        <?php echo esc_html($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
    
    <tr valign="top">
        <th scope="row"><label for="tags_for_discount"><?php esc_html_e('Tags for Discount', 'conditional-discounts'); ?></label></th>
        <td>
            <select id="tags_for_discount" name="tags_for_discount[]" multiple="multiple" style="<?php echo esc_attr('min-width: 200px;'); ?>">
                <?php foreach ($cdwc_template['tags'] as $slug => $name) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"
                        <?php selected(in_array($slug, $cdwc_template['discount']->get_tags())); ?>>
                        <?php echo esc_html($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>     
    
    <tr valign="top">
        <th scope="row"><label for="minimum_cart_total"><?php esc_html_e('Minimum Cart Total', 'conditional-discounts'); ?></label></th>
        <td>
            <input type="number" id="minimum_cart_total" name="minimum_cart_total" class="small-text" step="0.01" 
                value="<?php echo esc_attr($cdwc_template['discount']->get_min_cart_total()); ?>" />
        </td>
    </tr>
    
    <tr valign="top">
       <th scope="row"><label for="minimum_cart_quantity"><?php esc_html_e('Minimum Cart Quantity', 'conditional-discounts'); ?></label></th>
       <td>
           <input type="number" id="minimum_cart_quantity" name="minimum_cart_quantity" class="small-text" value="<?php echo esc_attr($cdwc_template['discount']->get_min_cart_quantity()); ?>" />
       </td>
   </tr>   
   
    <tr valign="top">
        <th scope="row"><label for="discount_value_type"><?php esc_html_e('Discount-Value Type', 'conditional-discounts'); ?></label></th>
        <td>
            <select id="discount_value_type" name="discount_value_type">
                <option value="percentage" <?php selected($discount->get_discount_value_type(), 'percentage'); ?>>
                    <?php esc_html_e('Percentage', 'conditional-discounts'); ?>
                </option>
                <option value="fixed" <?php selected($discount->get_discount_value_type(), 'fixed'); ?>>
                    <?php esc_html_e('Fixed', 'conditional-discounts'); ?>
                </option>
            </select>
        </td>
    </tr>
   
    <tr valign="top">
        <th scope="row"><label for="discount_value"><?php esc_html_e('Discount Value', 'conditional-discounts'); ?></label></th>
        <td>
            <input type="number" id="discount_value" name="discount_value" class="small-text" step="0.01" value="<?php echo esc_attr($discount->get_value()); ?>" />
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><label for="discount_cap"><?php esc_html_e('Discount Cap', 'conditional-discounts'); ?></label></th>
        <td>
            <input type="number" 
                   id="discount_cap" 
                   name="discount_cap" 
                   class="small-text" 
                   step="0.01" 
                   value="<?php echo $discount->get_cap() !== null ? esc_attr($discount->get_cap()) : ''; ?>" />
        </td>
    </tr>        
    
    <tr valign="top">
        <th scope="row"><label for="applicable_user_roles"><?php esc_html_e('Applicable User Roles', 'conditional-discounts'); ?></label></th>
        <td>
            <select id="applicable_user_roles" name="applicable_user_roles[]" multiple="multiple" style="<?php echo esc_attr('min-width: 200px;'); ?>">
                <?php foreach ($cdwc_template['roles'] as $slug => $name) : ?>
                    <option value="<?php echo esc_attr($slug); ?>"
                        <?php selected(in_array($slug, $cdwc_template['discount']->get_user_roles())); ?>>
                        <?php echo esc_html($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e('Select the user roles eligible for this discount.', 'conditional-discounts'); ?></p>
        </td>
    </tr>
    
    <tr valign="top">
        <th scope="row"><label for="discount_start_date"><?php esc_html_e('Validity Start Date', 'conditional-discounts'); ?></label></th>
        <td>
            <input type="date" id="discount_start_date" name="discount_start_date" 
                value="<?php echo esc_attr((new DateTime($cdwc_template['discount']->get_start_date()))->format('Y-m-d')); ?>" />
        </td>
    </tr>
    
    <tr valign="top">
        <th scope="row"><label for="discount_end_date"><?php esc_html_e('Validity End Date', 'conditional-discounts'); ?></label></th>
        <td>
            <input type="date" id="discount_end_date" name="discount_end_date" 
                value="<?php echo esc_attr((new DateTime($cdwc_template['discount']->get_end_date()))->format('Y-m-d')); ?>" />
        </td>
    </tr>
    
<tr valign="top">
    <th scope="row"><label for="discount_start_time"><?php esc_html_e('Validity Start Time', 'conditional-discounts'); ?></label></th>
    <td>
        <input type="time" 
               id="discount_start_time" 
               name="discount_start_time" 
               value="<?php echo esc_attr(
                   (new DateTime($cdwc_template['discount']->get_start_date()))->format('H:i')
               ); ?>" 
               step="3600" />
        <p class="description"><?php esc_html_e('Time in 24-hour format', 'conditional-discounts'); ?></p>
    </td>
</tr>

<tr valign="top">
    <th scope="row"><label for="discount_end_time"><?php esc_html_e('Validity End Time', 'conditional-discounts'); ?></label></th>
    <td>
        <input type="time" 
               id="discount_end_time" 
               name="discount_end_time" 
               value="<?php echo esc_attr(
                   (new DateTime($cdwc_template['discount']->get_end_date()))->format('H:i')
               ); ?>" 
               step="3600" />
        <p class="description"><?php esc_html_e('Time in 24-hour format', 'conditional-discounts'); ?></p>
    </td>
</tr>    
    
    <input type="hidden" id="discount_rules" name="discount_rules"  value="<?php // echo esc_attr(wp_json_encode($cdwc_template['discount']->get_rules())); ?>" />
</table>

