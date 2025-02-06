<?php

namespace Supreme\ConditionalDiscounts\Models;

use DateTimeImmutable;
use WC_Product;
use InvalidArgumentException;
use ConditionalDiscounts\DecisionEngine\RuleSet;

class DiscountModel
{
    private int $id;
    private array $meta         = [];
    private bool $meta_loaded   = false;

    private ?float $cap;
    private string $type;
    private float $value;
    private bool $enabled;
    private string $label;  
    private ?int $item_limit;
    private RuleSet $rule_set;    
    private bool $exclude_sale_items;
    private ?DateTimeImmutable $end_date;
    private array $excluded_products = [];
    private ?DateTimeImmutable $start_date;

    private array $applicable_tags          = [];
    private array $applicable_roles         = [];
    private array $applicable_products      = [];
    private array $applicable_categories    = [];

    public const MAX_ITEM_LIMIT = 1000000;
    public const VALID_TYPES    = ['percentage', 'fixed', 'bogo'];

    public function __construct(int $post_id)
    {
        if (!get_post($post_id) || get_post_type($post_id) !== 'shop_discount') {
            throw new InvalidArgumentException(
                __('Invalid discount post ID', 'conditional-discounts-for-woocommerce')
            );
        }

        $this->id = $post_id;
    }

    private function loadMeta(): void {
        if ($this->meta_loaded) { return; }

        global $wpdb;
        $table_name                     = $wpdb->prefix . 'cdwc_discount_rules';
        $rules                          = $wpdb->get_row( $wpdb->prepare("SELECT rules FROM $table_name WHERE discount_id = %d", $this->id), ARRAY_A );
        $this->meta                     = $rules ? json_decode($rules['rule_value'], true) : [];

        $this->enabled                  = ($this->meta['_enabled'] ?? 'no') === 'yes';
        $this->type                     = $this->validateType($this->meta['_discount_type'] ?? '');
        $this->value                    = (float)($this->meta['_discount_value'] ?? 0);
        $this->cap                      = isset($this->meta['_discount_cap']) ? (float)$this->meta['_discount_cap'] : null;
        $this->label                    = sanitize_text_field($this->meta['_label'] ?? '');
        $this->start_date               = $this->parseDate($this->meta['_start_date'] ?? '');
        $this->end_date                 = $this->parseDate($this->meta['_end_date'] ?? '');
        $this->exclude_sale_items       = (bool)($this->meta['_exclude_sale_items'] ?? false);
        $this->excluded_products        = array_map('intval', (array)($this->meta['_excluded_products'] ?? []));
        $this->item_limit               = isset($this->meta['_item_limit']) ? min((int)$this->meta['_item_limit'], self::MAX_ITEM_LIMIT) : null;

        $this->applicable_products      = array_map('intval', (array)($this->meta['_applicable_products'] ?? []));
        $this->applicable_categories    = array_map('intval', (array)($this->meta['_applicable_categories'] ?? []));
        $this->applicable_tags          = array_map('intval', (array)($this->meta['_applicable_tags'] ?? []));
        $this->applicable_roles         = (array)($this->meta['_applicable_roles'] ?? []);

        $this->rule_set                 = new RuleSet($this->meta['_rule_set'] ?? []);

        $this->meta_loaded = true;
    }

    private function validateType(string $type): string
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf(__('Invalid discount type: %s', 'conditional-discounts-for-woocommerce'), $type)
            );
        }
        return $type;
    }

    private function parseDate(?string $date): ?DateTimeImmutable
    {
        try {
            return $date ? new DateTimeImmutable($date) : null;
        } catch (\Exception $e) {
            error_log("Invalid date format for discount {$this->id}: {$date}");
            return null;
        }
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function is_enabled(): bool
    {
        $this->loadMeta();
        return $this->enabled;
    }

    public function get_type(): string
    {
        $this->loadMeta();
        return $this->type;
    }

    public function get_value(): float
    {
        $this->loadMeta();
        return $this->value;
    }

    public function get_cap(): ?float
    {
        $this->loadMeta();
        return $this->cap;
    }

    public function get_label(): string
    {
        $this->loadMeta();
        return $this->label;
    }

    public function get_start_date(): ?DateTimeImmutable
    {
        $this->loadMeta();
        return $this->start_date;
    }

    public function get_end_date(): ?DateTimeImmutable
    {
        $this->loadMeta();
        return $this->end_date;
    }

    public function should_exclude_sale_items(): bool
    {
        $this->loadMeta();
        return $this->exclude_sale_items;
    }

    public function get_excluded_products(): array
    {
        $this->loadMeta();
        return $this->excluded_products;
    }

    public function get_item_limit(): ?int
    {
        $this->loadMeta();
        return $this->item_limit;
    }

    public function get_rule_set(): RuleSet
    {
        $this->loadMeta();
        return $this->rule_set;
    }

    public function get_applicable_products(): array
    {
        $this->loadMeta();
        return array_filter(array_map(
            fn($id) => wc_get_product($id),
            $this->applicable_products
        ));
    }

    public function get_applicable_category_ids(): array
    {
        $this->loadMeta();
        return $this->applicable_categories;
    }

    public function get_applicable_tag_ids(): array
    {
        $this->loadMeta();
        return $this->applicable_tags;
    }

    public function get_applicable_roles(): array
    {
        $this->loadMeta();
        return $this->applicable_roles;
    }

    public function is_active(): bool
    {
        if (!$this->is_enabled()) {
            return false;
        }

        $now = new DateTimeImmutable('now');
        $valid_start = !$this->start_date || $this->start_date <= $now;
        $valid_end = !$this->end_date || $this->end_date >= $now;

        return $valid_start && $valid_end;
    }

    public function calculate_discount_amount(float $base_amount): float
    {
        $amount = match ($this->type) {
            'percentage' => $base_amount * ($this->value / 100),
            'fixed' => $this->value,
            default => 0.0,
        };

        if ($this->cap !== null) {
            $amount = min($amount, $this->cap);
        }

        return max(0, $amount);
    }

    public function applies_to_product(WC_Product $product): bool
    {
        if (in_array($product->get_id(), $this->excluded_products, true)) {
            return false;
        }

        if ($this->applicable_products && 
            !in_array($product->get_id(), $this->applicable_products, true)
        ) {
            return false;
        }

        if ($this->applicable_categories) {
            $product_cats = $product->get_category_ids();
            if (empty(array_intersect($product_cats, $this->applicable_categories))) {
                return false;
            }
        }

        if ($this->applicable_tags) {
            $product_tags = $product->get_tag_ids();
            if (empty(array_intersect($product_tags, $this->applicable_tags))) {
                return false;
            }
        }

        return true;
    }

    public function save(array $data): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cdwc_discount_rules';

        $rules = json_encode($data);
        $wpdb->replace(
            $table_name,
            [
                'discount_id' => $this->id,
                'rule_value' => $rules
            ],
            ['%d', '%s']
        );
    }
}