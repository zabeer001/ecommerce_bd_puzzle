<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\SubCategory;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all existing categories and subcategories to randomly assign them.
        $categories = Category::all();
        $subcategories = SubCategory::all();

        // If either categories or subcategories don't exist, we cannot seed products.
        if ($categories->isEmpty() || $subcategories->isEmpty()) {
            return;
        }

        // Define the product data
        $products = [
            [
                'name' => 'Apple iPhone 14',
                'description' => 'Latest model with A15 chip and advanced camera.',
                'image' => 'images/products/iphone14.jpg',
                'price' => 999.99,
                'status' => 'active',
                'cost_price' => 800.00,
                'stock_quantity' => 50,
            ],
            [
                'name' => 'Samsung Galaxy S23',
                'description' => 'Flagship Android phone with stunning display.',
                'image' => 'images/products/galaxys23.jpg',
                'price' => 899.99,
                'status' => 'active',
                'cost_price' => 750.00,
                'stock_quantity' => 70,
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Industry leading noise-canceling headphones.',
                'image' => 'images/products/sonyheadphones.jpg',
                'price' => 349.99,
                'status' => 'active',
                'cost_price' => 250.00,
                'stock_quantity' => 30,
            ],
        ];
        
        // Prepare the data for insertion by adding a random category_id and subcategory_id
        $dataToInsert = [];
        foreach ($products as $product) {
            $dataToInsert[] = array_merge($product, [
                'category_id' => $categories->random()->id,
                'sub_category_id' => $subcategories->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Insert the prepared data into the database.
        DB::table('products')->insert($dataToInsert);
    }
}
