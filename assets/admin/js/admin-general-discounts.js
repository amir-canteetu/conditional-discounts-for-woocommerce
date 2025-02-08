jQuery(document).ready(function ($) {
  // toggle the fields based on enabling/disabling General Discounts
  //Todo: rework on this logic
  function toggleGeneralDiscountFields() {
    const isGeneralDiscountsEnabled = $("#cdwc_enable_general_discounts").is(
      ":checked"
    );
    // Disable or enable all other form fields
    $(".form-table input, .form-table select")
      .not("#cdwc_enable_general_discounts")
      .prop("disabled", !isGeneralDiscountsEnabled);
  }

  // Initial toggle on page load
  toggleGeneralDiscountFields();

  // Add event listener to the General Discounts checkbox
  $("#cdwc_enable_general_discounts").on("change", function () {
    toggleGeneralDiscountFields();
  });
});
