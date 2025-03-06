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
    
    public function create(array $data): int {
        $post_id = wp_insert_post([
            'post_title' => $data['label'],
            'post_type' => 'shop_discount',
            'post_status' => 'publish'
        ]);

        if(is_wp_error($post_id)) {
            throw new Exception(__('Failed to create discount', 'conditional-discounts'));
        }

        $this->wpdb->insert($this->table_name, [
            'discount_id' => $post_id,
            'label' => sanitize_text_field($data['label']),
            'enabled' => (bool)($data['enabled'] ?? false),
            'rules' => json_encode($this->validateRules($data['rules']))
        ], [
            '%d', '%s', '%d', '%s'
        ]);

        return $post_id;
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
    
    public function update(int $id, array $data): bool {
        $update_data = [];
        $format = [];

        if(isset($data['label'])) {
            $update_data['label'] = sanitize_text_field($data['label']);
            $format[] = '%s';
        }

        if(isset($data['enabled'])) {
            $update_data['enabled'] = (bool)$data['enabled'];
            $format[] = '%d';
        }

//        if(isset($data['rules'])) {
//            $update_data['rules'] = json_encode($this->validateRules($data['rules']));
//            $format[] = '%s';
//        }
//        
//        \write_log($update_data);

        if(!empty($update_data)) {
            $this->wpdb->update(
                $this->table_name,
                $update_data,
                ['discount_id' => $id],
                $format,
                ['%d']
            );
        }

        // Update post title if label changed
        if(isset($data['label'])) {
            wp_update_post([
                'ID' => $id,
                'post_title' => $data['label']
            ]);
        }

        return true;
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
