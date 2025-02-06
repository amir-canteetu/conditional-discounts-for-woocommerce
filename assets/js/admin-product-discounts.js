jQuery(document).ready(function ($) {
  function toggleProductDiscountFields() {
    const isProductDiscountsEnabled = $("#cdwc_enable_product_discounts").is(
      ":checked"
    );
    // Disable or enable all other form fields
    $(".form-table input, .form-table select")
      .not("#cdwc_enable_product_discounts")
      .prop("disabled", !isProductDiscountsEnabled);
  }

  // Initial toggle on page load
  toggleProductDiscountFields();

  // Toggle on checkbox change
  $("#cdwc_enable_product_discounts").on("change", function () {
    toggleProductDiscountFields();
  });
});
