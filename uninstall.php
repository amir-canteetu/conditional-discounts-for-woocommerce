<?php
// Exit if accessed directly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Helper function to delete options by prefix
function cdwc_delete_options_by_prefix($prefix) {
    global $wpdb;

    // Fetch all option names that start with the prefix
    $options = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like($prefix) . '%'
        )
    );

    // Delete each option
    foreach ($options as $option) {
        delete_option($option);
    }

}

// Delete all options with the prefix "cdwc_"
cdwc_delete_options_by_prefix('cdwc_');
