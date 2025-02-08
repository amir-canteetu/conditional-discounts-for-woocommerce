(function ($) {
  $(document).ready(function () {
    // Access the localized settings.
    const settings = cdDiscountSettings;

    // Example: Check if general discounts are enabled.
    if (settings.enable_general_discounts === "yes") {
      console.log("General discounts are enabled.");

      // Apply discount logic, if any.
      if (settings.general_discount_type === "percentage") {
        console.log(`Applying a ${settings.general_discount_value}% discount.`);
      } else if (settings.general_discount_type === "fixed") {
        console.log(
          `Applying a fixed discount of ${settings.general_discount_value}.`
        );
      }

      // Check the validity dates.
      const today = new Date().toISOString().split("T")[0];
      if (
        settings.discount_start_date &&
        settings.discount_end_date &&
        today >= settings.discount_start_date &&
        today <= settings.discount_end_date
      ) {
        console.log("Discount is currently valid.");
      } else {
        console.log("Discount is not valid.");
      }

      // Handle combinability.
      if (settings.discount_combinability === "no") {
        console.log("This discount cannot be combined with others.");
      }

      // Apply a global discount cap if set.
      if (settings.global_discount_cap) {
        console.log(
          `The maximum discount is capped at ${settings.global_discount_cap}.`
        );
      }
    } else {
      console.log("General discounts are disabled.");
    }
  });
})(jQuery);
