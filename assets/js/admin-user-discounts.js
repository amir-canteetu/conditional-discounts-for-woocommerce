jQuery(document).ready(function ($) {
  function toggleUsertDiscountFields() {
    const isUserDiscountsEnabled = $("#cdwc_user_discount_enable").is(
      ":checked"
    );
    // Disable or enable all other form fields
    $(".form-table input, .form-table select")
      .not("#cdwc_user_discount_enable")
      .prop("disabled", !isUserDiscountsEnabled);
  }

  // Initial toggle on page load
  toggleUsertDiscountFields();

  // Toggle on checkbox change
  $("#cdwc_user_discount_enable").on("change", function () {
    toggleUsertDiscountFields();
  });
});
