<?php

if (!defined('ABSPATH')) {
    exit;
}

?>



<form id="discount-settings-form" method="post">
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="enable_discount">Enable Discount</label></th>
            <td>
                <input type="checkbox" id="enable_discount" name="enable_discount" value="1" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="discount_label">Discount Label</label></th>
            <td>
                <input type="text" id="discount_label" name="discount_label" class="regular-text" value="New Discount" />
            </td>
        </tr>        
        <tr valign="top">
            <th scope="row"><label for="minimum_cart_total">Minimum Cart Total</label></th>
            <td>
                <input type="number" id="minimum_cart_total" name="minimum_cart_total" class="small-text" step="0.01" value="101" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="minimum_cart_quantity">Minimum Cart Quantity</label></th>
            <td>
                <input type="number" id="minimum_cart_quantity" name="minimum_cart_quantity" class="small-text" value="3" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="discount_type">Discount Type</label></th>
            <td>
                <select id="discount_type" name="discount_type">
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="discount_value">Discount Value</label></th>
            <td>
                <input type="number" id="discount_value" name="discount_value" class="small-text" step="0.01" value="3" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="discount_cap">Discount Cap</label></th>
            <td>
                <input type="number" id="discount_cap" name="discount_cap" class="small-text" step="0.01" value="50" />
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="products_for_discount">Products for Discount</label></th>
            <td>
                <select id="products_for_discount" name="products_for_discount[]" multiple="multiple" style="min-width: 200px;">
                    <?php foreach ($products as $id => $title) : ?>
                        <option value="<?= esc_attr($id) ?>" <?= selected($id === array_key_first($products), true, false) ?>>
                            <?= esc_html($title) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="categories_for_discount">Categories for Discount</label></th>
            <td>
                <select id="categories_for_discount" name="categories_for_discount[]" multiple="multiple" style="min-width: 200px;">
                    <?php foreach ($categories as $slug => $name) : ?>
                        <option value="<?= esc_attr($slug) ?>" <?= selected($slug === array_key_first($categories), true, false) ?>>
                            <?= esc_html($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="applicable_user_roles">Applicable User Roles</label></th>
            <td>
                <select id="applicable_user_roles" name="applicable_user_roles[]" multiple="multiple" style="min-width: 200px;">
                    <?php foreach ($roles as $slug => $name) : ?>
                        <option value="<?= esc_attr($slug) ?>" <?= selected($slug === array_key_first($roles), true, false) ?>>
                            <?= esc_html($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description ">Select the user roles eligible for this discount.</p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="discount_start_date">Validity Start Date</label></th>
            <td>
                <input type="date" id="discount_start_date" name="discount_start_date" value="2025-02-23" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="discount_end_date">Validity End Date</label></th>
            <td>
                <input type="date" id="discount_end_date" name="discount_end_date" value="2025-02-28" />
            </td>
        </tr>    
        <input type="hidden" id="discount_rules" name="discount_rules" />
         
    </table>

</form>
