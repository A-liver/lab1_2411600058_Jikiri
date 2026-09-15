<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Chicken Breast', 'sku' => 'INV-001', 'description' => null, 'category' => 'Meat & Poultry', 'quantity' => 45, 'reorder_level' => 20, 'unit_price' => 3.20, 'supplier' => 'FreshFarms Co.'],
            ['name' => 'Beef Tenderloin', 'sku' => 'INV-002', 'description' => null, 'category' => 'Meat & Poultry', 'quantity' => 12, 'reorder_level' => 15, 'unit_price' => 14.50, 'supplier' => 'Prime Meats Ltd.'],
            ['name' => 'Lamb Chops', 'sku' => 'INV-003', 'description' => null, 'category' => 'Meat & Poultry', 'quantity' => 0, 'reorder_level' => 10, 'unit_price' => 6.80, 'supplier' => 'FreshFarms Co.'],
            ['name' => 'Ground Beef', 'sku' => 'INV-004', 'description' => null, 'category' => 'Meat & Poultry', 'quantity' => 30, 'reorder_level' => 12, 'unit_price' => 5.10, 'supplier' => 'Prime Meats Ltd.'],
            ['name' => 'Salmon Fillet', 'sku' => 'INV-005', 'description' => null, 'category' => 'Seafood', 'quantity' => 18, 'reorder_level' => 15, 'unit_price' => 11.90, 'supplier' => 'Ocean Catch'],
            ['name' => 'Shrimp', 'sku' => 'INV-006', 'description' => null, 'category' => 'Seafood', 'quantity' => 8, 'reorder_level' => 10, 'unit_price' => 9.40, 'supplier' => 'Ocean Catch'],
            ['name' => 'Tuna Steak', 'sku' => 'INV-007', 'description' => null, 'category' => 'Seafood', 'quantity' => 22, 'reorder_level' => 10, 'unit_price' => 13.20, 'supplier' => 'Ocean Catch'],
            ['name' => 'Tomatoes', 'sku' => 'INV-008', 'description' => null, 'category' => 'Vegetables & Produce', 'quantity' => 60, 'reorder_level' => 25, 'unit_price' => 0.90, 'supplier' => 'GreenLeaf Produce'],
            ['name' => 'Lettuce', 'sku' => 'INV-009', 'description' => null, 'category' => 'Vegetables & Produce', 'quantity' => 15, 'reorder_level' => 20, 'unit_price' => 0.70, 'supplier' => 'GreenLeaf Produce'],
            ['name' => 'Onions', 'sku' => 'INV-010', 'description' => null, 'category' => 'Vegetables & Produce', 'quantity' => 50, 'reorder_level' => 20, 'unit_price' => 0.60, 'supplier' => 'GreenLeaf Produce'],
            ['name' => 'Bell Peppers', 'sku' => 'INV-011', 'description' => null, 'category' => 'Vegetables & Produce', 'quantity' => 0, 'reorder_level' => 15, 'unit_price' => 1.10, 'supplier' => 'GreenLeaf Produce'],
            ['name' => 'Milk', 'sku' => 'INV-012', 'description' => null, 'category' => 'Dairy & Eggs', 'quantity' => 40, 'reorder_level' => 15, 'unit_price' => 1.30, 'supplier' => 'Dairy Best'],
            ['name' => 'Mozzarella Cheese', 'sku' => 'INV-013', 'description' => null, 'category' => 'Dairy & Eggs', 'quantity' => 10, 'reorder_level' => 12, 'unit_price' => 4.50, 'supplier' => 'Dairy Best'],
            ['name' => 'Eggs (dozen)', 'sku' => 'INV-014', 'description' => null, 'category' => 'Dairy & Eggs', 'quantity' => 55, 'reorder_level' => 20, 'unit_price' => 2.20, 'supplier' => 'Dairy Best'],
            ['name' => 'Butter', 'sku' => 'INV-015', 'description' => null, 'category' => 'Dairy & Eggs', 'quantity' => 25, 'reorder_level' => 10, 'unit_price' => 3.00, 'supplier' => 'Dairy Best'],
            ['name' => 'Soft Drinks (case)', 'sku' => 'INV-016', 'description' => null, 'category' => 'Beverages', 'quantity' => 35, 'reorder_level' => 12, 'unit_price' => 8.00, 'supplier' => 'BevCo Distributors'],
            ['name' => 'Bottled Water (case)', 'sku' => 'INV-017', 'description' => null, 'category' => 'Beverages', 'quantity' => 48, 'reorder_level' => 15, 'unit_price' => 5.50, 'supplier' => 'BevCo Distributors'],
            ['name' => 'Coffee Beans (kg)', 'sku' => 'INV-018', 'description' => null, 'category' => 'Beverages', 'quantity' => 6, 'reorder_level' => 8, 'unit_price' => 12.00, 'supplier' => 'BevCo Distributors'],
            ['name' => 'Flour', 'sku' => 'INV-019', 'description' => null, 'category' => 'Dry Goods & Spices', 'quantity' => 70, 'reorder_level' => 25, 'unit_price' => 1.20, 'supplier' => "Baker's Supply"],
            ['name' => 'Rice', 'sku' => 'INV-020', 'description' => null, 'category' => 'Dry Goods & Spices', 'quantity' => 33, 'reorder_level' => 20, 'unit_price' => 1.50, 'supplier' => "Baker's Supply"],
            ['name' => 'Black Pepper (kg)', 'sku' => 'INV-021', 'description' => null, 'category' => 'Dry Goods & Spices', 'quantity' => 3, 'reorder_level' => 5, 'unit_price' => 15.00, 'supplier' => "Baker's Supply"],
            ['name' => 'Bread Rolls (dozen)', 'sku' => 'INV-022', 'description' => null, 'category' => 'Bakery & Bread', 'quantity' => 20, 'reorder_level' => 15, 'unit_price' => 3.50, 'supplier' => 'Sunrise Bakery'],
            ['name' => 'Baguette', 'sku' => 'INV-023', 'description' => null, 'category' => 'Bakery & Bread', 'quantity' => 9, 'reorder_level' => 10, 'unit_price' => 2.80, 'supplier' => 'Sunrise Bakery'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }
    }
}