<?php

 use JsonSchema\Validator;
 use JsonSchema\Constraints\Constraint;
 
 class RuleValidator {
     private array $schema;
     private array $supportedRoles;
 
     public function __construct() {
         $this->schema = $this->buildSchema();
         $this->supportedRoles = $this->getAvailableRoles();
     }
 
     private function buildSchema(): array {

        /*The schema will outline all the possible conditions, 
        their required properties, allowed values, and constraints. For example, a minimum cart 
        total should be a number greater than or equal to zero.
        */        
         return [
             '$schema' => "http://json-schema.org/draft-07/schema#",
             'type' => 'object',
             'additionalProperties' => false,
             'required' => ['requirements', 'discount_config'],
             'properties' => [
                 'requirements' => [
                     'type' => 'object',
                     'properties' => [
                         'min_cart_total' => [
                             'type' => 'number',
                             'minimum' => 0,
                             'maximum' => 999999
                         ],
                         'min_cart_quantity' => [
                             'type' => 'integer',
                             'minimum' => 1
                         ],
                         'product_conditions' => [
                             'type' => 'object',
                             'properties' => [
                                 'categories' => $this->getTaxonomySchema('product_cat'),
                                 'tags' => $this->getTaxonomySchema('product_tag'),
                                 'excluded_products' => [
                                     'type' => 'array',
                                     'items' => ['type' => 'integer']
                                 ]
                             ]
                         ],
                         'user_conditions' => [
                             'type' => 'object',
                             'properties' => [
                                 'roles' => [
                                     'type' => 'array',
                                     'items' => [
                                         'type' => 'string',
                                         'enum' => $this->supportedRoles
                                     ]
                                 ]
                             ]
                         ]
                     ]
                 ],
                 'discount_config' => [
                     'type' => 'object',
                     'required' => ['type', 'value'],
                     'properties' => [
                         'type' => [
                             'type' => 'string',
                             'enum' => ['percentage', 'fixed']
                         ],
                         'value' => [
                             'type' => 'number',
                             'minimum' => 0.01
                         ],
                         'cap' => ['type' => 'number'],
                         'compatibility' => [
                             'type' => 'object',
                             'properties' => [
                                 'coupons' => ['type' => 'boolean'],
                                 'other_discounts' => ['type' => 'boolean']
                             ]
                         ]
                     ]
                 ],
                 'temporal_rules' => [
                     'type' => 'object',
                     'properties' => [
                         'start' => $this->getDateTimeSchema(),
                         'end' => $this->getDateTimeSchema(),
                         'recurring' => ['type' => 'boolean']
                     ]
                 ]
             ]
         ];
     }
 
     private function getDateTimeSchema(): array {
         return [
             'type' => 'string',
             'pattern' => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/'
         ];
     }
 
     private function getTaxonomySchema(string $taxonomy): array {
         return [
             'type' => 'array',
             'items' => [
                 'type' => 'integer',
                 'minimum' => 1
             ],
             'taxonomy' => $taxonomy
         ];
     }
 
     public function validate(array $rules): array {
         // Validate against JSON Schema
         $validator = new Validator();
         $validator->validate(
             $rules,
             $this->schema,
             Constraint::CHECK_MODE_TYPE_CAST | Constraint::CHECK_MODE_VALIDATE_SCHEMA
         );
 
         if (!$validator->isValid()) {
             $errors = array_map(
                 fn($e) => sprintf("[%s] %s", $e['property'], $e['message']),
                 $validator->getErrors()
             );
             throw new InvalidRuleException("Invalid rules:\n" . implode("\n", $errors));
         }
 
         // Additional semantic validation
         $this->validateDates($rules);
         $this->validateTaxonomyTerms($rules);
 
         return $rules;
     }
 
     private function validateDates(array $rules): void {
         if (isset($rules['temporal_rules'])) {
             $tr = $rules['temporal_rules'];
             
             if (!strtotime($tr['start']) || !strtotime($tr['end'])) {
                 throw new InvalidRuleException("Invalid date format");
             }
             
             if (strtotime($tr['start']) >= strtotime($tr['end'])) {
                 throw new InvalidRuleException("End date must be after start date");
             }
         }
     }
 
     private function validateTaxonomyTerms(array $rules): void {
         if (isset($rules['requirements']['product_conditions'])) {
             $pc = $rules['requirements']['product_conditions'];
             
             foreach (['categories' => 'product_cat', 'tags' => 'product_tag'] as $field => $taxonomy) {
                 if (!empty($pc[$field])) {
                     foreach ($pc[$field] as $termId) {
                         if (!term_exists((int)$termId, $taxonomy)) {
                             throw new InvalidRuleException(
                                 "Invalid $taxonomy term ID: $termId"
                             );
                         }
                     }
                 }
             }
         }
     }
 
     private function getAvailableRoles(): array {
         global $wp_roles;
         return array_keys($wp_roles->roles ?? []);
     }
 }