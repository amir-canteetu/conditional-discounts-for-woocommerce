jQuery(document).ready(function($) {


  ["applicable_user_roles", "categories_for_discount", "products_for_discount", "tags_for_discount"].forEach((id) => {
    $(`#${id}`).select2({
      allowClear: true,
      width: "resolve",
    });
  });  

  function validateDiscountFields() {

    const errors = [];
    const $enableDiscount = $('#enable_discount');
  
    // Only validate if discount is enabled
    if ($enableDiscount.is(':checked')) {

      // Validate Discount Label
      if ($('#discount_label').val().trim() === '') {
        errors.push('Discount label is required');
      }
  
      // Validate Discount Value
      const discountValue = parseFloat($('#discount_value').val());
      if (isNaN(discountValue) || discountValue <= 0) {
        errors.push('Discount value must be a positive number');
      } else if (
        $('#discount_type').val() === 'percentage' && discountValue > 100
      ) {
        errors.push('Percentage discount cannot exceed 100%');
      }
  
      // Validate Dates
      const endDate = new Date($('#discount_end_date').val());
      const startDate = new Date($('#discount_start_date').val());
      
      if (!startDate.getTime()) {errors.push('Start date is required');}
      if (!endDate.getTime()) {errors.push('End date is required');}
      if (startDate > endDate) {errors.push('End date must be after start date');}
  
      // Validate Minimum Cart Total
      const minCartTotal = parseFloat($('#minimum_cart_total').val());
      if (isNaN(minCartTotal) || minCartTotal < 0) {
        errors.push('Minimum cart total must be ≥ 0');
      }
    }
  
    return errors;
  }
  
  function showValidationErrors(errors) {
    const $errorContainer = $('#discount-errors');
    $errorContainer.empty().hide();
    
    if (errors.length > 0) {
      $errorContainer.append(
        `<p><strong>Discount validation errors:</strong></p>` +
        errors.map(error => `<p>• ${error}</p>`).join('')
      ).show();
    }
  }  

  function sanitizeDiscountData(data) {
    return {
        meta: {
            ...data.meta,
            label: data.meta.label.substring(0, 255), // Limit length
            value: Math.round(data.meta.value * 100) / 100, // 2 decimal places
            products: data.meta.products.map(Number).filter(id => !isNaN(id)),
            tags: data.meta.tags.map(slug => slug.replace(/[^a-z0-9-_]/gi, '')),
            categories: data.meta.categories.map(slug => slug.replace(/[^a-z0-9-_]/gi, ''))
        },
        post: {
            ...data.post,
            id: parseInt(data.post.id) || 0
        }
    };
}  

  // Flag to avoid infinite loop when re-triggering the publish click.
  var discountSaved = false;

  $('#publish').on('click', function(e) {

    if (discountSaved) {return;}
    
    e.preventDefault();

    // Clear previous errors
    $('#discount-errors').hide().empty();
  
    // Run validation
    const errors = validateDiscountFields();

    if (errors.length > 0) {
      showValidationErrors(errors);
      $('#publish').removeAttr('disabled');
      return;
    }
  
    // Proceed with AJAX if validation passes
    $('#publish').attr('disabled', true);

    const discountData = {
      meta: {
          enabled: $('#enable_discount').is(':checked') ? 1 : 0,
          label: $('#discount_label').val().trim(),
          min_cart_total: parseFloat($('#minimum_cart_total').val()) || 0,
          min_cart_quantity: parseInt($('#minimum_cart_quantity').val()) || 0,
          type: $('#discount_type').val(),
          value: parseFloat($('#discount_value').val()) || 0,
          cap: parseFloat($('#discount_cap').val()) || 0,
          products: $('#products_for_discount').val() || [],
          categories: $('#categories_for_discount').val() || [],
          tags: $('#tags_for_discount').val() || [],
          roles: $('#applicable_user_roles').val() || [],
          start_date: $('#discount_start_date').val(),
          end_date: $('#discount_end_date').val()
      },
      post: {
          id: $('#post_ID').val(),
          status: $('#post_status').val()
      }
    };


    const discount = JSON.stringify(sanitizeDiscountData(discountData));
    console.log(discount);

    // Trigger the AJAX call to save discount rules.
    $.ajax({
        type: 'POST',
        url: ajaxurl, // Provided by WordPress.
        data: {
            action: 'save_discount_rules',
            data: discount,
            nonce: cdwcRules.api.nonce  
        },
        success: function(response) {
            if (response.success) {
                // Mark that discount rules are saved.
                discountSaved = true;
                // Optionally show a success message.
                // Re-enable the publish button (if it was disabled).
                $('#publish').removeAttr('disabled');
                // Trigger the click again so the normal post save occurs.
                $('#publish').trigger('click');
            } else {
                // Handle error; show message to the user.
                console.log('Error saving discount rules: ' + response.data);
                // Re-enable the publish button.
                $('#publish').removeAttr('disabled');
            }
        },
        error: function(xhr, status, error) {
            $('#publish').removeAttr('disabled');
        }
    });
  });
  
    function toggleDiscountFields() {
        // Hide all fields first
        $('#products_for_discount, #categories_for_discount, #tags_for_discount').closest('tr').hide();
        
        // Get selected discount type
        var discountType = $('#discount_type').val();
        
        // Show relevant field based on type
        switch(discountType) {
            case 'product':
                $('#products_for_discount').closest('tr').show();
                break;
            case 'category':
                $('#categories_for_discount').closest('tr').show();
                break;
            case 'tag':
                $('#tags_for_discount').closest('tr').show();
                break;
        }
    }

    // Initial state on page load
    toggleDiscountFields();

    // Update on change
    $('#discount_type').on('change', toggleDiscountFields);  
  
});