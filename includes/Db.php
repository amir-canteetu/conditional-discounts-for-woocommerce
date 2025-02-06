<?php

namespace Supreme\ConditionalDiscounts;

defined('ABSPATH') || exit;

class Db {
    /**
     * Create the custom table.
     */
   
    public static function create_table() {

        global $wpdb;
        $table_name         = $wpdb->prefix . 'cdwc_discount_rules';
        $charset_collate    = $wpdb->get_charset_collate();
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
            
            $sql = "CREATE TABLE $table_name (
                discount_id BIGINT UNSIGNED PRIMARY KEY,
                label VARCHAR(255) NOT NULL,
                enabled BOOLEAN DEFAULT false,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,                
                rules JSON NOT NULL,
                FOREIGN KEY (discount_id) REFERENCES {$wpdb->posts}(ID) ON DELETE CASCADE
            ) $charset_collate;";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);

            if ($wpdb->last_error) {
                error_log('Failed to create table: ' . $wpdb->last_error);
            }
            
        }        

    }    

    /**
     * Delete the custom table.
     */
    public static function delete_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cdwc_discount_rules';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    /**
     * Handle multisite table creation.
     */
    public static function create_table_multisite() {
        if (is_multisite()) {
            $site_ids = get_sites(['fields' => 'ids']);
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                self::create_table();
                restore_current_blog();
            }
        } else {
            self::create_table();
        }
    }

    /**
     * Handle multisite table deletion.
     */
    public static function delete_table_multisite() {
        if (is_multisite()) {
            $site_ids = get_sites(['fields' => 'ids']);
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                self::delete_table();
                restore_current_blog();
            }
        } else {
            self::delete_table();
        }
    }
    
    /**
     * Handle multisite deletion of all 'shop_discount' posts.
     */
    public static function delete_all_discounts_multisite() {
        if (is_multisite()) {
            $site_ids = get_sites(['fields' => 'ids']);
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                self::delete_all_discounts();
                restore_current_blog();
            }
        } else {
            self::delete_all_discounts();
        }
    }

    /**
     * Delete all 'shop_discount' posts and their associated data.
     */
    public static function delete_all_discounts() {
        global $wpdb;

        // Get all 'shop_discount' post IDs
        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
                'shop_discount'
            )
        );

        if (empty($post_ids)) {
            return; // No posts to delete
        }

        // Delete posts and their metadata
        foreach ($post_ids as $post_id) {
            wp_delete_post($post_id, true); // Force delete (skip trash)
        }

        // Log the deletion (optional)
        write_log('Deleted all shop_discounts.');
    }    
    
}