<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all existing categories to randomly assign them to subcategories.
        $categories = Category::all();

        // If no categories exist, we cannot seed subcategories.
        if ($categories->isEmpty()) {
            return;
        }
        
        // Define the subcategory data
        $subcategories = [
            [
                'name' => 'Smartphones',
                'description' => 'Latest smartphones and accessories.',
            ],
            [
                'name' => 'Laptops & Computers',
                'description' => 'High-performance computing devices.',
            ],
            [
                'name' => 'Men\'s Fashion',
                'description' => 'Apparel for men.',
            ],
            [
                'name' => 'Women\'s Fashion',
                'description' => 'Apparel for women.',
            ],
            [
                'name' => 'Kitchen Appliances',
                'description' => 'Appliances for your kitchen.',
            ],
            [
                'name' => 'Furniture',
                'description' => 'Modern and classic furniture.',
            ],
            [
                'name' => 'Skincare',
                'description' => 'Products for all skin types.',
            ],
            [
                'name' => 'Haircare',
                'description' => 'Products for healthy hair.',
            ],
        ];

        // Prepare the data for insertion by adding a random category_id to each subcategory.
        $dataToInsert = [];
        foreach ($subcategories as $subcat) {
            $dataToInsert[] = array_merge($subcat, [
                'category_id' => $categories->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert the prepared data into the database.
        DB::table('sub_categories')->insert($dataToInsert);
    }
}
