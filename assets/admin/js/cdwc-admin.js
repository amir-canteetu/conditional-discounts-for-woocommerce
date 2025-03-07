jQuery(document).ready(function($) {


  ["applicable_user_roles", "categories_for_discount", "products_for_discount", "tags_for_discount"].forEach((id) => {
    $(`#${id}`).select2({
      allowClear: true,
      width: "resolve",
    });
  });  

    function validateDiscountFields() {
        
        const errors = [];

        // Remove previous error styling
        $('#discount-settings-form-table input, #discount-settings-form-table select').removeClass('error-field');

        const $enableDiscount = $('#enable_discount');

        // Only validate if discount is enabled
        if ($enableDiscount.is(':checked')) {

          // Validate Discount Label
          const $discountLabel = $('#discount_label');
          if ($discountLabel.val().trim() === '') {
            errors.push('Discount label is required');
            $discountLabel.addClass('error-field');
          }

          // Validate Discount Value
          const $discountValue = $('#discount_value');
          const discountValue = parseFloat($discountValue.val());
          if (isNaN(discountValue) || discountValue <= 0) {
            errors.push('Discount value must be a positive number');
            $discountValue.addClass('error-field');
          } else if ($('#discount_value_type').val() === 'percentage' && discountValue > 100) {
            errors.push('Percentage discount cannot exceed 100%');
            $discountValue.addClass('error-field');
          }

        // Validate Dates and Times
        const $startDate = $('#discount_start_date');
        const $endDate = $('#discount_end_date');
        const $startTime = $('#discount_start_time');
        const $endTime = $('#discount_end_time');
        
        // Get combined date/time values
        const startDateVal = $startDate.val();
        const startTimeVal = $startTime.val();
        const endDateVal = $endDate.val();
        const endTimeVal = $endTime.val();
        
        // Create full datetime strings
        const startDateTimeString = startDateVal && startTimeVal 
            ? `${startDateVal}T${startTimeVal}` 
            : null;
        const endDateTimeString = endDateVal && endTimeVal 
            ? `${endDateVal}T${endTimeVal}` 
            : null;
        
        // Parse to Date objects
        const startDateTime = startDateTimeString ? new Date(startDateTimeString) : null;
        const endDateTime = endDateTimeString ? new Date(endDateTimeString) : null;

        // Validate start datetime
        if (!startDateVal || !startTimeVal) {
            errors.push('Start date and time are required');
            if (!startDateVal) $startDate.addClass('error-field');
            if (!startTimeVal) $startTime.addClass('error-field');
        } else if (!startDateTime || isNaN(startDateTime.getTime())) {
            errors.push('Invalid start date/time combination');
            $startDate.addClass('error-field');
            $startTime.addClass('error-field');
        }

        // Validate end datetime
        if (!endDateVal || !endTimeVal) {
            errors.push('End date and time are required');
            if (!endDateVal) $endDate.addClass('error-field');
            if (!endTimeVal) $endTime.addClass('error-field');
        } else if (!endDateTime || isNaN(endDateTime.getTime())) {
            errors.push('Invalid end date/time combination');
            $endDate.addClass('error-field');
            $endTime.addClass('error-field');
        }

        // Validate datetime order
        if (startDateTime && endDateTime && startDateTime >= endDateTime) {
            errors.push('End date/time must be after start date/time');
            $startDate.addClass('error-field');
            $startTime.addClass('error-field');
            $endDate.addClass('error-field');
            $endTime.addClass('error-field');
        }

          // Validate Minimum Cart Total
          const $minCartTotal = $('#minimum_cart_total');
          const minCartTotal = parseFloat($minCartTotal.val());
          if (isNaN(minCartTotal) || minCartTotal < 0) {
            errors.push('Minimum cart total must be ≥ 0');
            $minCartTotal.addClass('error-field');
          }
        }

        return errors;
    }
  
  function showValidationErrors(errors) {
    const $errorContainer = $('#discount-errors');
    $errorContainer.empty().hide();
    console.log(errors);
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
    if (discountSaved) return;
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

    // Build complete data object
    const discountData = {
        meta: {
            enabled: $('#enable_discount').is(':checked') ? 1 : 0,
            label: $('#discount_label').val().trim(),
            min_cart_total: parseFloat($('#minimum_cart_total').val()) || 0,
            min_cart_quantity: parseInt($('#minimum_cart_quantity').val()) || 0,
            discount_type: $('#discount_type').val(),
            value_type: $('#discount_value_type').val(),
            value: parseFloat($('#discount_value').val()) || 0,
            cap: $('#discount_cap').val().trim() !== '' ? parseFloat($('#discount_cap').val()) : null,
            products: $('#products_for_discount').val() || [],
            categories: $('#categories_for_discount').val() || [],
            tags: $('#tags_for_discount').val() || [],
            roles: $('#applicable_user_roles').val() || [],
            start_date: $('#discount_start_date').val() + 'T' + $('#discount_start_time').val(),
            end_date: $('#discount_end_date').val() + 'T' + $('#discount_end_time').val()
        },
        post: {
            id: $('#post_ID').val(),
            status: $('#post_status').val(),
            title: $('#discount_label').val().trim()
        }
    };

    // Sanitize and prepare data
    const sanitizedData = sanitizeDiscountData(discountData);
    const jsonData = JSON.stringify(sanitizedData);

    // AJAX request
    $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'save_discount_rules',
            data: jsonData,
            nonce: cdwcRules.api.nonce
        },
        dataType: 'json',
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        success: function(response) {
            if (response.success) {
                discountSaved = true;
                console.log(response);
                //$('#publish').removeAttr('disabled').trigger('click');
            } else {
                showValidationErrors(response.data.errors || [response.data]);
                $('#publish').removeAttr('disabled');
            }
        },
        error: function(xhr) {
            showValidationErrors(xhr.responseJSON?.data.errors);
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