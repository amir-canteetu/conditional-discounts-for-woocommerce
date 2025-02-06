<?php

namespace Supreme\ConditionalDiscounts\Repositories;

use WooCommerceDiscounts\Discounts\Discount;
use WP_Post;

class DiscountRepository {
    /**
     * Find a discount by ID.
     *
     * @param int $id
     * @return Discount|null
     */
    public function find(int $id): ?Discount {
        $post = get_post($id);

        if ($post && $post->post_type === 'shop_discount') {
            return new Discount($post);
        }

        return null;
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

    /**
     * Save a Discount instance.
     *
     * @param Discount $discount
     * @return bool
     */
    public function save(Discount $discount): bool {
        $discount->save();
        return true;
    }

    /**
     * Delete a Discount instance.
     *
     * @param Discount $discount
     * @return bool
     */
    public function delete(Discount $discount): bool {
        return $discount->delete();
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
