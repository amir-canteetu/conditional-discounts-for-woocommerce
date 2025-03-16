jQuery(function($) {
    // Initialize product search
    $('.cdwc-product-search').each(function() {
        $(this).select2({
            ajax: {
                url: cdwcAdmin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term, // Search term
                        page: params.page || 1,
                        action: 'cdwc_search_products',
                        security: cdwcAdmin.nonce
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            placeholder: $(this).data('placeholder'),
            width: '100%',
            templateResult: formatProductResult,
            escapeMarkup: function(markup) { return markup; }
        });
    });

    // Initialize taxonomy search (categories/tags)
    $('.cdwc-taxonomy-search').each(function() {
        const taxonomy = $(this).data('taxonomy');
        
        $(this).select2({
            ajax: {
                url: cdwcAdmin.ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1,
                        taxonomy: taxonomy,
                        action: 'cdwc_search_taxonomy',
                        security: cdwcAdmin.nonce
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            placeholder: $(this).data('placeholder'),
            width: '100%'
        });
    });

    // Optional: Format product results with additional info
    function formatProductResult(product) {
        if (product.loading) return product.text;
        
        const container = $(
            '<div class="product-result">' +
                '<span class="title"></span>' +
                '<span class="meta"></span>' +
            '</div>'
        );

        container.find('.title').text(product.text);
        
        // Add additional product info if needed
        // You would need to modify the AJAX response to include more data
        // container.find('.meta').text('SKU: ' + product.sku);
        
        return container;
    }

    // Handle value type display
    $('#value_type').on('change', function() {
        const symbol = $(this).val() === 'percentage' ? '%' : cdwcAdmin.currency_symbol;
        $('#discount_value').next('.description').text(symbol);
    }).trigger('change');

    
        
    const $discountType     = $('#discount_type');
    const $productField     = $('#product-field');
    const $categoryField    = $('#category-field');
    const $tagField         = $('#tag-field');
    const $brandField       = $('#brand-field');

    // Function to toggle visibility
    function toggleDiscountFields() {
        const discountType = $discountType.val();
        
        // Hide all fields first
        $productField.hide();
        $categoryField.hide();
        $tagField.hide();
        $brandField.hide();

        // Show relevant field based on selected type
        switch(discountType) {
            case 'product':
                $productField.show();
                break;
            case 'category':
                $categoryField.show();
                break;
            case 'tag':
                $tagField.show();
                break;
            case 'brand':
                $brandField.show();
                break;                
        }
    }

    // Run on page load
    toggleDiscountFields();

    // Update on change
    $discountType.on('change', toggleDiscountFields);
    
    
});