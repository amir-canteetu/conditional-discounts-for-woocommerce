jQuery(document).ready(function ($) {
  function toggleCartDiscountFields() {
    const isCartDiscountsEnabled = $("#cdwc_cart_discount_enable").is(
      ":checked"
    );

    $(".form-table input")
      .not("#cdwc_cart_discount_enable")
      .prop("disabled", !isCartDiscountsEnabled);
  }

  // Initial toggle on page load
  toggleCartDiscountFields();

  // Toggle on checkbox change
  $("#cdwc_cart_discount_enable").on("change", function () {
    toggleCartDiscountFields();
  });
});
