jQuery(document).ready(function ($) {
  function toggleCartDiscountFields() {
    const isCartDiscountsEnabled = $("#cdwc_enable_cart_discounts").is(
      ":checked"
    );

    $(".form-table input")
      .not("#cdwc_enable_cart_discounts")
      .prop("disabled", !isCartDiscountsEnabled);
  }

  // Initial toggle on page load
  toggleCartDiscountFields();

  // Toggle on checkbox change
  $("#cdwc_enable_cart_discounts").on("change", function () {
    toggleCartDiscountFields();
  });
});
