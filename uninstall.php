<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . "/includes/Db.php";

// Delete the custom table
Supreme\ConditionalDiscounts\Db::delete_table_multisite();
Supreme\ConditionalDiscounts\Db::delete_all_discounts();

