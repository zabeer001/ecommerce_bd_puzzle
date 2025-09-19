<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // This single line uses your ProductFactory to create 10 fake products.
        // The factory automatically handles generating the fake data,
        // including assigning an existing or new Category and SubCategory.
        Product::factory()->count(10)->create();
    }
}