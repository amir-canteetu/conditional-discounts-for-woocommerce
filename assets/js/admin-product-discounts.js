jQuery(document).ready(function ($) {
  function toggleProductDiscountFields() {
    const isProductDiscountsEnabled = $("#cd_enable_product_discounts").is(
      ":checked"
    );
    // Disable or enable all other form fields
    $(":input")
      .not("#cd_enable_product_discounts")
      .prop("disabled", !isProductDiscountsEnabled);
  }

  // Initial toggle on page load
  toggleProductDiscountFields();

  // Toggle on checkbox change
  $("#cd_enable_product_discounts").on("change", function () {
    toggleProductDiscountFields();
  });
});
