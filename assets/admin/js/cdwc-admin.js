jQuery(document).ready(function($) {
    
    $('#applicable_user_roles').select2({
        allowClear: true,
        width: 'resolve'  
    }); 
    
    $('#categories_for_discount').select2({
        allowClear: true,
        width: 'resolve'  
    });
    
    $('#products_for_discount').select2({
        allowClear: true,
        width: 'resolve'  
    });       
  
});


jQuery(document).ready(function($) {
    const discountSaved = false;
    const formConfig = {
        selectors: {
            publish: '#publish',
            form: '#discount-settings-form',
            fields: {
                enable: '#enable_discount',
                label: '#discount_label',
                cartTotal: '#minimum_cart_total',
                cartQuantity: '#minimum_cart_quantity',
                type: '#discount_type',
                value: '#discount_value',
                cap: '#discount_cap',
                products: '#products_for_discount',
                categories: '#categories_for_discount',
                roles: '#applicable_user_roles',
                startDate: '#discount_start_date',
                endDate: '#discount_end_date',
                rules: '#discount_rules'
            }
        }
    };

    const discountSchema = {
      "$schema": "http://json-schema.org/draft-07/schema#",
      "title": "DiscountRules",
      "type": "object",
      "properties": {
        "enable_discount": { "type": "boolean" },
        "discount_label": { "type": "string", "minLength": 2 },
        "minimum_cart_total": { "type": "number", "minimum": 0 },
        "minimum_cart_quantity": { "type": "integer", "minimum": 0 },
        "discount_type": { 
          "type": "string", 
          "enum": ["percentage", "fixed"]
        },
        "discount_value": {
          "type": "number",
          "minimum": 0,
          "allOf": [
            {
              "if": { "properties": { "discount_type": { "const": "percentage" } } },
              "then": { "maximum": 100 }
            }
          ]
        },
        "discount_cap": { 
          "type": ["number", "null"], 
          "minimum": 0,
          "default": null
        },
        "products_for_discount": { 
          "type": "array", 
          "items": { "type": "integer", "minimum": 1 },
          "uniqueItems": true
        },
        "categories_for_discount": { 
          "type": "array", 
          "items": { 
            "type": "string",
            "pattern": "^[a-z0-9_]+$"
          },
          "minItems": 1
        },
        "applicable_user_roles": { 
          "type": "array", 
          "items": { "type": "string" }
        },
        "discount_start_date": { 
          "type": "string", 
          "format": "date",
          "maximum": { "$data": "1/discount_end_date" }
        },
        "discount_end_date": { 
          "type": "string", 
          "format": "date",
          "minimum": { "$data": "1/discount_start_date" }
        }
      },
      "required": ["discount_type", "discount_value"],
      "if": {
        "properties": { "discount_type": { "const": "percentage" } },
        "required": ["discount_type"]
      },
      "then": {
        "required": ["discount_cap"]
      }
    };

    // Initialize AJV with better configuration
    const ajv = new Ajv({
        allErrors: true,
        jsonPointers: true, // Required for dataPath formatting
        verbose: true,
        coerceTypes: true,
        useDefaults: true,
        $data: true,
        messages: false
    });
    
    // Add custom format validation for dates
    ajv.addFormat('date', {
        validate: (dateString) => !isNaN(Date.parse(dateString))
    });

    const validateDiscount = ajv.compile(discountSchema);

    function showValidationErrors(errors) {
        // Clear previous errors
        $('.validation-error').removeClass('validation-error');
        $('.error-message').remove();

        errors.forEach(error => {
            let fieldName = error.dataPath?.replace('/', '') || '';
            let $field;

            // Handle array field naming convention
            if (fieldName.endsWith('_for_discount') || fieldName.endsWith('_user_roles')) {
                fieldName += '[]'; // Match multi-select name attributes
            }

            // Special case for minItems error
            if (error.keyword === 'minItems') {
                switch(fieldName) {
                    case 'categories_for_discount':
                        fieldName = 'categories_for_discount[]';
                        break;
                    case 'products_for_discount':
                        fieldName = 'products_for_discount[]';
                        break;
                    case 'applicable_user_roles':
                        fieldName = 'applicable_user_roles[]';
                        break;
                }
            }

            $field = $(`[name="${fieldName}"]`);

            // Fallback for select2 elements
            if (!$field.length && fieldName.endsWith('[]')) {
                $field = $(`#${fieldName.replace('[]', '')}`);
            }

            if (!$field.length) {
                console.warn('No matching field for error:', error);
                return;
            }

            const $parent = $field.closest('tr');
            $parent.addClass('validation-error');

            // Create error message with context-specific text
            const errorMessage = error.keyword === 'minItems' 
                ? 'Please select at least one option' 
                : error.message;

            $parent.append(`
                <div class="error-message" style="color:red; margin-top:5px;">
                    ${errorMessage}
                </div>
            `);

            // Add visual indicator to select2 elements
            if ($field.hasClass('select2-hidden-accessible')) {
                $field.next('.select2-container').css('border', '1px solid #dc3232');
            }
        });

        // Focus first invalid field
        $('.validation-error input, .validation-error select').first().trigger('focus');
    }

    function normalizeFormData(rawData) {
        return {
            ...rawData,
            minimum_cart_total: parseFloat(rawData.minimum_cart_total) || 0,
            minimum_cart_quantity: parseInt(rawData.minimum_cart_quantity, 10) || 0,
            discount_value: parseFloat(rawData.discount_value),
            discount_cap: rawData.discount_cap ? parseFloat(rawData.discount_cap) : null,
            products_for_discount: rawData.products_for_discount.map(Number),
            discount_start_date: rawData.discount_start_date || null,
            discount_end_date: rawData.discount_end_date || null
        };
    }

    function handleValidationError(message) {
        const $publish = $(formConfig.selectors.publish);
        $publish.prop('disabled', false);
        alert(message);
    }

    $(formConfig.selectors.publish).on('click', async function(e) {
        if (discountSaved) return;
        e.preventDefault();

        const rawData = {
            enable_discount: $(formConfig.selectors.fields.enable).is(':checked'),
            discount_label: $(formConfig.selectors.fields.label).val(),
            minimum_cart_total: $(formConfig.selectors.fields.cartTotal).val(),
            minimum_cart_quantity: $(formConfig.selectors.fields.cartQuantity).val(),
            discount_type: $(formConfig.selectors.fields.type).val(),
            discount_value: $(formConfig.selectors.fields.value).val(),
            discount_cap: $(formConfig.selectors.fields.cap).val(),
            products_for_discount: $(formConfig.selectors.fields.products).val() || [],
            categories_for_discount: $(formConfig.selectors.fields.categories).val() || [],
            applicable_user_roles: $(formConfig.selectors.fields.roles).val() || [],
            discount_start_date: $(formConfig.selectors.fields.startDate).val(),
            discount_end_date: $(formConfig.selectors.fields.endDate).val()
        };

        const formData = normalizeFormData(rawData);
        const isValid = validateDiscount(formData);

        if (!isValid) {
            showValidationErrors(validateDiscount.errors);
            return;
        }

        try {
            $(this).prop('disabled', true);
            const response = await $.ajax({
                type: 'POST',
                url: ajaxurl,
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    post_id: $('#post_ID').val(),
                    discount_data: formData,
                    action: 'save_discount_rules',
                    nonce: cdwcRules.api.nonce
                })
            });

            if (response.success) {
                discountSaved = true;
                $(this).trigger('click');
            } else {
                handleValidationError(response.data.message || 'Server validation failed');
            }
        } catch (error) {
            const errorMessage = error.responseJSON?.data?.message || 
                               error.statusText || 
                               'An unknown error occurred';
            handleValidationError(errorMessage);
        }
    });
});
