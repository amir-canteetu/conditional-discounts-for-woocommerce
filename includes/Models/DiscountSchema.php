<?php

/*
 * -- defines the structure and validation rules for the discount configurations.
 * -- serves as the centralized definition and validation blueprint for discount rules
 * -- ensures all discount configurations adhere to a strict structure and validation rules before being saved or applied.
 * -- DiscountSchema's role is purely to define the structure, while validation is handled by another class using that schema.
 */
namespace Supreme\ConditionalDiscounts\Models;

class DiscountSchema {
    
    const SCHEMA_FILE_PATH = 'config/schema.json';
    const DATE_FORMAT = 'Y-m-d H:i:s';
          
    /*
     * -- Returns full JSON Schema for validation
     */
    public static function get(): array {
        
        $schema_file = CDWC_PLUGIN_DIR . self::SCHEMA_FILE_PATH;
        
        if (!file_exists($schema_file)) {
            throw new \RuntimeException(
                'Schema file missing: ' . self::SCHEMA_FILE_PATH
            );
        }        
        
        return [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            '$id' => CDWC_PLUGIN_URL . self::SCHEMA_FILE_PATH,
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['version', 'requirements', 'discount'],
            'properties' => array_merge(
                self::baseProperties(),
                self::requirementProperties(),
                self::discountProperties(),
                self::temporalProperties()
            )
        ];
    }
    
    //Uses file modification time as version indicator
    private static function get_version(): string {
        return filemtime(CDWC_PLUGIN_DIR . self::SCHEMA_FILE_PATH) ?: time();
    }    
    
    /*
     * -- Provides valid empty template for new discounts
     */
    public static function getEmpty(): array {
        return [
            'version' => self::get_version(),
            'requirements' => [],
            'discount' => [],
            'temporal' => []
        ];
    }

    private static function baseProperties(): array {
        return [
            'version' => [
                'type' => 'string',
                'const' => self::get_version()
            ]
        ];
    }

    private static function requirementProperties(): array {
        return [
            'requirements' => [
                'type' => 'object',
                'properties' => [
                    'cart' => [
                        'type' => 'object',
                        'properties' => [
                            'min_total' => self::moneySchema(),
                            'min_quantity' => self::quantitySchema()
                        ]
                    ],
                    'products' => [
                        'type' => 'object',
                        'properties' => [
                            'categories' => self::taxonomySchema('product_cat'),
                            'tags' => self::taxonomySchema('product_tag'),
                          //  'excluded' => self::productIDSchema()
                        ]
                    ]
                ]
            ]
        ];
    }

    private static function discountProperties(): array {
        return [
            'discount' => [
                'type' => 'object',
                'required' => ['type', 'value'],
                'properties' => [
                    'type' => [
                        'type' => 'string',
                        'enum' => ['percentage', 'fixed_cart', 'fixed_product']
                    ],
                    'value' => self::moneySchema(0.01),
                    'cap' => self::moneySchema(),
                    'distribution' => [
                        'type' => 'string',
                        'enum' => ['cheapest', 'most_expensive', 'all']
                    ]
                ]
            ]
        ];
    }

    private static function temporalProperties(): array {
        return [
            'temporal' => [
                'type' => 'object',
                'properties' => [
                    'start' => self::dateTimeSchema(),
                    'end' => self::dateTimeSchema(),
                    'recurrence' => [
                        'type' => 'object',
                        'properties' => [
                            'pattern' => [
                                'type' => 'string',
                                'enum' => ['daily', 'weekly', 'monthly', 'yearly']
                            ],
                            'exceptions' => [
                                'type' => 'array',
                                'items' => self::dateTimeSchema()
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    // Reusable Schema Components
    private static function moneySchema(float $min = 0): array {
        return [
            'type' => 'number',
            'minimum' => $min,
            'maximum' => 999999
        ];
    }

    private static function quantitySchema(): array {
        return [
            'type' => 'integer',
            'minimum' => 1
        ];
    }

    private static function dateTimeSchema(): array {
        return [
            'type' => 'string',
            'format' => 'date-time',
            'pattern' => '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$'
        ];
    }

    private static function taxonomySchema(string $taxonomy): array {
        return [
            'type' => 'array',
            'items' => [
                'type' => 'integer',
                'minimum' => 1
            ],
            'taxonomy' => $taxonomy
        ];
    }
}


























