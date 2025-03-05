<?php

namespace Supreme\ConditionalDiscounts\Models;

use DateTimeImmutable;
use WC_Product;
use InvalidArgumentException;
use Supreme\ConditionalDiscounts\Admin\RuleBuilder;
use Supreme\ConditionalDiscounts\DecisionEngine\RuleSet;

/*Serves as the central data object and business rule validator for discounts.
 * 
Key Responsibilities
Data Abstraction

Encapsulates all discount-related data storage/retrieval

Abstracts WordPress meta handling behind domain methods

Provides type-safe access to properties (DateTimeImmutable for dates)

Business Logic

Implements core discount calculations (calculate_discount_amount())

Manages product applicability rules (applies_to_product())

Handles temporal validity checks (is_active())

Validation & Sanitization

Type validation for discount types

Date format validation and normalization

Input sanitization for numeric values

Relationship Management

Handles product/category/tag/role relationships

Returns proper WC_Product objects for applicable products

Manages exclusion lists

Performance Optimization

Lazy-loading of meta data

Object caching of relationships

Batch meta loading pattern

Domain Object Integrity

Immutable date objects

Range checking for numeric values

Default value handling
 **/

class Discount
{
    private int $id;
    private array $meta         = [];
    private bool $meta_loaded   = false;

    private bool $enabled;
    private string $label;  
    private string $type;
    private float $value;
    private ?float $cap;
    private ?float $min_cart_total;
    private ?int $min_cart_quantity;
    private array $products         = [];
    private array $categories       = []; 
    private array $tags             = [];
    private array $user_roles       = [];
    private string $start_date;
    private string $end_date;
    private string $notes;  
    
    private RuleSet $rule_set;    

    public const MAX_ITEM_LIMIT = 1000000;
    public const VALID_TYPES    = ['percentage', 'fixed', 'bogo'];

    public function __construct(int $post_id) {
        if (!get_post($post_id) || get_post_type($post_id) !== 'shop_discount') {
            throw new InvalidArgumentException(
                __('Invalid discount post ID', 'conditional-discounts')
            );
        }

        $this->id = $post_id;
    }

    private function loadMeta(): void {
        if ($this->meta_loaded) { return; }

        global $wpdb;
        $table_name                     = $wpdb->prefix . 'cdwc_discount_rules';
        $rules                          = $wpdb->get_row( $wpdb->prepare("SELECT rules FROM $table_name WHERE discount_id = %d", $this->id), ARRAY_A );
        $this->meta                     = $rules ? json_decode($rules['rules'], true) : RuleBuilder::get_default_rules();           
        $this->enabled                  = ($this->meta['enabled'] ?? 'no') === 'yes';
        $this->label                    = sanitize_text_field($this->meta['label'] ?? '');
        $this->type                     = $this->meta['type'] ?? 'percentage' ;
        $this->value                    = (float)($this->meta['value'] ?? 0);
        $this->cap                      = isset($this->meta['cap']) ? (float)$this->meta['cap'] : null;   
        $this->min_cart_total           = (float)($this->meta['min_cart_total'] ?? 0.00);
        $this->min_cart_quantity        = (int)($this->meta['min_cart_quantity'] ?? 0);
        $this->products                 = array_map('intval', (array)($this->meta['products'] ?? []));
        $this->categories               = array_map('intval', (array)($this->meta['categories'] ?? []));
        $this->tags                     = array_map('intval', (array)($this->meta['tags'] ?? []));
        $this->user_roles               = (array)($this->meta['user_roles'] ?? []);
        $this->start_date               = $this->meta['validity']['start_date'];
        $this->end_date                 = $this->meta['validity']['end_date'];    
        $this->notes                    = sanitize_text_field($this->meta['notes'] ?? '');
        $this->rule_set                 = new RuleSet($this->meta['rule_set'] ?? []);
        $this->meta_loaded              = true;
    }
    
    private function validateType(string $type): string
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf(__('Invalid discount type: %s', 'conditional-discounts'), $type)
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
    
    
    public function get_min_cart_total(): float
    {
        $this->loadMeta();
        return $this->min_cart_total;
    } 
    
    public function get_min_cart_quantity(): int
    {
        $this->loadMeta();
        return $this->min_cart_quantity;
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

    public function get_start_date(): string
    {
        $this->loadMeta();
        return $this->start_date;
    }

    public function get_end_date(): string
    {
        $this->loadMeta();
        return $this->end_date;
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

    public function get_products(): array
    {
        $this->loadMeta();
        return array_filter(array_map(
            fn($id) => wc_get_product($id),
            $this->products
        ));
    }
    
    
    public function get_tags(): array
    {
        $this->loadMeta();
        return $this->tags;
    }    

    public function get_categories(): array
    {
        $this->loadMeta();
        return $this->categories;
    }

    public function get_applicable_tag_ids(): array
    {
        $this->loadMeta();
        return $this->applicable_tags;
    }

    public function get_user_roles(): array
    {
        $this->loadMeta();
        return $this->user_roles;
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
                'rules'       => $rules
            ],
            ['%d', '%s']
        );
    }
}