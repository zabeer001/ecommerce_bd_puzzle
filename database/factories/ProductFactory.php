<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->words(3, true);

        // Ensure a category and sub-category exist for the product
        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();
        $subCategory = SubCategory::inRandomOrder()->where('category_id', $category->id)->first() ?? SubCategory::factory()->create(['category_id' => $category->id]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'old_price' => $this->faker->optional(0.5)->randomFloat(2, 10, 1000),
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
        ];
    }
}