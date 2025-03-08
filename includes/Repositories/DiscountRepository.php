<?php

namespace Supreme\ConditionalDiscounts\Repositories;

use Supreme\ConditionalDiscounts\Models\Discount;
use WP_Post;

/**
 * -- handles data persistence
 * -- for querying/filtering discounts
 *
 * @class   DiscountRepository 
 */


class DiscountRepository {
    
    private $wpdb;
    private $table_name;
    
    public function __construct($wpdb) {
        $this->wpdb         = $wpdb;
        $this->table_name   = $this->wpdb->prefix . 'cdwc_discount_rules';
    }  
    
    public function discountExists(int $post_id): bool {
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT 1 FROM {$this->table_name} WHERE discount_id = %d LIMIT 1",
                $post_id
            )
        );

        if ($this->wpdb->last_error) {
            error_log("Discount check failed: " . $this->wpdb->last_error);
            return false;
        }

        return (bool)$result;
    }
    
    public function create(int $post_id, array $data): bool {  

        $insert_data = [
            'discount_id'    => $post_id,
            'label'          => sanitize_text_field($data['label']),
            'version'        => sanitize_text_field($data['version']),
            'enabled'        => (bool)$data['enabled'],
            'discount_type'  => sanitize_text_field($data['discount_type']),
            'rules'          => wp_json_encode([
                'min_cart_total'     => (float)$data['min_cart_total'],
                'min_cart_quantity'  => (int)$data['min_cart_quantity'],
                'value_type'         => sanitize_text_field($data['value_type']),
                'value'              => (float)$data['value'],
                'cap'                => isset($data['cap']) ? (float)$data['cap'] : null,
                'products'           => array_map('intval', $data['products']),
                'categories'         => array_map('sanitize_text_field', $data['categories']),
                'roles'              => array_map('sanitize_text_field', $data['roles']),
                'start_date'         => sanitize_text_field($data['start_date']),
                'end_date'           => sanitize_text_field($data['end_date'])
            ])
        ];

        $formats = [
            'discount_id'   => '%d',
            'label'         => '%s',
            'version'       => '%s',
            'enabled'       => '%d',
            'discount_type' => '%s',
            'rules'         => '%s'
        ];

        $result = $this->wpdb->insert(
            $this->table_name,
            $insert_data,
            $formats
        );
        
        if ($result === false) {
            error_log('Discount creation failed: ' . $this->wpdb->last_error);
            return false;
        }  
        
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish',
            'post_title' => $insert_data['label']
        ]);        
        
        return true;
        
    }

    
    
    public function update(int $id, array $data): bool {
        $update_data = [];
        $format = [];

        // Handle direct columns
        if (isset($data['label'])) {
            $update_data['label'] = sanitize_text_field($data['label']);
            $format[] = '%s';
        }

        if (isset($data['enabled'])) {
            $update_data['enabled'] = (bool)$data['enabled'];
            $format[] = '%d';
        }

        if (isset($data['version'])) {
            $update_data['version'] = sanitize_text_field($data['version']);
            $format[] = '%s';
        }

        if (isset($data['discount_type'])) {
            $update_data['discount_type'] = sanitize_text_field($data['discount_type']);
            $format[] = '%s';
        }

        // Always update rules JSON
        $rules = [
            'min_cart_total' => isset($data['min_cart_total']) ? (float)$data['min_cart_total'] : 0,
            'min_cart_quantity' => isset($data['min_cart_quantity']) ? (int)$data['min_cart_quantity'] : 0,
            'value_type' => isset($data['value_type']) ? sanitize_text_field($data['value_type']) : 'percentage',
            'value' => isset($data['value']) ? (float)$data['value'] : 0,
            'cap' => isset($data['cap']) ? (float)$data['cap'] : null,
            'products' => array_map('intval', $data['products'] ?? []),
            'categories' => array_map('sanitize_text_field', $data['categories'] ?? []),
            'roles' => array_map('sanitize_text_field', $data['roles'] ?? []),
            'start_date' => isset($data['start_date']) ? sanitize_text_field($data['start_date']) : '',
            'end_date' => isset($data['end_date']) ? sanitize_text_field($data['end_date']) : ''
        ];

        $update_data['rules'] = wp_json_encode($rules);
        $format[] = '%s';  

        if (!empty($update_data)) {
           $rows_affected = $this->wpdb->update(
                    $this->table_name,
                    $update_data,
                    ['discount_id' => $id],
                    $format,
                    ['%d'] 
            );
           
            if (!$rows_affected) {
                error_log('Database update failed: ' . $this->wpdb->last_error);
                return false;
            }           
           
        }

        // Update WordPress post title if label changed
        if (isset($data['label'])) {
            wp_update_post([
                'ID' => $id,
                'post_title' => $update_data['label']
            ]);
        }

        return true;
    }      
    
    /**
     * Find a discount by ID.
     *
     * @param int $id
     * @return Discount|null
     */
    public function find(int $id): ?array {
        $query = $this->wpdb->prepare("
            SELECT d.*, p.post_status 
            FROM {$this->table_name} d
            INNER JOIN {$this->wpdb->posts} p ON d.discount_id = p.ID
            WHERE d.discount_id = %d
        ", $id);

        $result = $this->wpdb->get_row($query, ARRAY_A);

        if(!$result) return null;

        return $this->hydrate($result);
    }
   

    /**
     * Find all discounts.
     *
     * @return array
     */
    public function findAll(): array {
        $query = new \WP_Query([
            'post_type'      => 'shop_discount',
            'posts_per_page' => -1,
        ]);

        $discounts = [];
        foreach ($query->posts as $post) {
            $discounts[] = new Discount($post);
        }

        return $discounts;
    }

    /**
     * Find discounts by metadata.
     *
     * @param array $meta_query
     * @return array
     */
    public function findBy(array $meta_query): array {
        $query = new \WP_Query([
            'post_type'      => 'shop_discount',
            'meta_query'     => $meta_query,
            'posts_per_page' => -1,
        ]);

        $discounts = [];
        foreach ($query->posts as $post) {
            $discounts[] = new Discount($post);
        }

        return $discounts;
    }
    
    private function hydrate(array $data): array {
        return [
            'id' => (int)$data['discount_id'],
            'label' => $data['label'],
            'enabled' => (bool)$data['enabled'],
            'rules' => json_decode($data['rules'], true),
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at']
        ];
    }          


    /**
     * Delete a Discount instance.
     *
     * @param Discount $discount
     * @return bool
     */
    public function delete(int $id): bool {
        // Delete custom table entry
        $this->wpdb->delete(
            $this->table_name,
            ['discount_id' => $id],
            ['%d']
        );

        // Delete CPT
        return (bool)wp_delete_post($id, true);
    }

    /**
     * Find discounts by expiration status.
     *
     * @param bool $expired
     * @return array
     */
    public function findByExpiration(bool $expired): array {
        $meta_query = [
            [
                'key'     => 'expiry_date',
                'value'   => date('Y-m-d'),
                'compare' => $expired ? '<' : '>=',
                'type'    => 'DATE',
            ],
        ];

        return $this->findBy($meta_query);
    }
}
