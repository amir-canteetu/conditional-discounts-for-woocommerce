jQuery(document).ready(function ($) {
  function toggleCartDiscountFields() {
    const isCartDiscountsEnabled = $("#cd_enable_cart_discounts").is(
      ":checked"
    );

    $(".form-table input")
      .not("#cd_enable_cart_discounts")
      .prop("disabled", !isCartDiscountsEnabled);
  }

  // Initial toggle on page load
  toggleCartDiscountFields();

  // Toggle on checkbox change
  $("#cd_enable_cart_discounts").on("change", function () {
    toggleCartDiscountFields();
  });
});
