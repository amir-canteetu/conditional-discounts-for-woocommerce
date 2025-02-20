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
    
  $('#discount-rules-form').on('submit', function(e) {
    e.preventDefault();
    
     
    var ruleData = {
      amount: $('#discount_rule_amount').val(),
      type: $('#discount_rule_type').val()
    };

    var discountRulesJson = JSON.stringify(ruleData);

    $('#discount_rules').val(discountRulesJson);
    

    $.ajax({
      type: 'POST',
      url: cdwcRules.api.saveUrl,  
      data: $(this).serialize(),   
      success: function(response) {
        console.log('Discount rules saved:', response);
      },
      error: function(error) {
        console.error('Error saving discount rules:', error);
      }
    });
    

  });
});