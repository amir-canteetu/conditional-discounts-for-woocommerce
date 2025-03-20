# Conditional Discounts for WooCommerce

Conditional Discounts for WooCommerce allows you to apply dynamic, rule-based discounts to your WooCommerce store, enhancing your store's promotional capabilities. With this extension, you can create and manage a variety of discount rules based on cart totals, item quantities, user roles, and specific product, category, tag, or brand requirements.

---

## Features

- **Rule-Based Discounts:**  
  Create discount rules that apply based on conditions such as:
  - Minimum cart total
  - Minimum cart item quantity
  - Specific products, categories, tags, or brands in the cart
  - User roles (logged-in or guest via email hashing)

- **Time-Sensitive Discounts:**  
  Define start and end dates for discount rules, ensuring that discounts are active only when intended.

- **Usage Limits:**  
  Control discount usage by:
  - Setting a maximum number of uses globally.
  - Limiting the number of times a user or guest can use a discount.

- **Flexible Discount Types:**  
  Apply discounts either as a percentage or as a fixed amount per item, with support for discount caps.

- **Seamless WooCommerce Integration:**  
  The extension hooks into WooCommerce's cart and order processes, automatically applying discounts and updating usage counts as orders are processed, completed, or refunded.

---

## Installation

1. **Download and Upload:**
   - Download the extension package.
   - Upload the extension files to your WordPress plugins directory (usually `wp-content/plugins/`).

2. **Activate the Plugin:**
   - Go to your WordPress Admin Dashboard.
   - Navigate to **Plugins > Installed Plugins**.
   - Locate **Conditional Discounts for WooCommerce** and click **Activate**.

3. **Configure WooCommerce:**
   - Ensure WooCommerce is installed and activated.
   - The extension automatically integrates with WooCommerce upon activation.

---

## Configuration

### Creating Discount Rules

Discount rules are stored as a custom post type (`shop_discount`). You can manage these rules via the WordPress admin:

1. **Add a New Discount Rule:**
   - Go to **Discounts > Add New Discount**.
   - Fill in the rule details including:
     - **Label:** A descriptive name for the discount.
     - **Discount Type:** Choose from product, category, tag, or brand.
     - **Value Type:** Set the discount as a percentage or fixed amount.
     - **Discount Value:** Specify the discount amount.
     - **Discount Cap:** (For percentage discounts) Limit the maximum discount applied.
     - **Minimum Cart Total:** The cart subtotal must meet this minimum.
     - **Minimum Cart Quantity:** The cart must contain at least this many items.
     - **Time Constraints:** Set start and end dates if needed.
     - **Usage Limits:** Define maximum global uses and per-user uses.
     - **Applicable Items:** Specify products, categories, tags, or brands where the discount applies.
     - **User Roles:** Restrict the discount to specific user roles if required.

2. **Enable or Disable Discounts:**
   - Use the rule’s **enabled** flag to activate or deactivate specific discount rules.
   - Disabled rules or those that do not meet the conditions (time-based or usage limits) will be automatically skipped.

---

## How It Works

- **Applying Discounts:**  
  When a customer adds items to their cart, the extension:
  1. Retrieves all active discount rules.
  2. Validates each rule based on time, usage limits, cart subtotal, item count, and applicable products or categories.
  3. Calculates the discount (either percentage or fixed amount) and applies it to the cart as a negative fee.

- **Usage Tracking:**  
  The extension updates discount usage counts when orders are processed:
  - **Increase Counts:** When an order is marked as processing or completed.
  - **Decrease Counts:** When an order is refunded or partially refunded, ensuring that usage limits remain accurate.

- **User Identification:**  
  The extension distinguishes between logged-in users and guests:
  - Logged-in users are tracked using their user ID.
  - Guests are tracked via a hashed version of their billing email.

---

## License

This extension is released under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) license. Feel free to use, modify, and distribute it as per the license terms.

