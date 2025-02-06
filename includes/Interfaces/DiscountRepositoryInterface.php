<?php

interface DiscountRepositoryInterface {
    
    public function find(int $id): ?Discount;
    
    public function findAll(): Array;
    
    public function findBy(array $meta_query): array;
    
    public function delete(Discount $discount): bool;
    
    public function findByExpiration(bool $expired): array;
    
    public function findActiveDiscounts(): array;
    
    public function save(Discount $discount): bool;

}
