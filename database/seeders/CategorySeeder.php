<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create 5 fake categories using the factory
        Category::factory()->count(5)->create();
    }
}