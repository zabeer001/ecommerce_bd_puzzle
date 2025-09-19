<?php

namespace Database\Factories;

use App\Models\SubCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubCategoryFactory extends Factory
{
    protected $model = SubCategory::class;

    public function definition()
    {
        $name = $this->faker->unique()->word();
        return [
            'name' => 'Sub ' . $name,
            'slug' => Str::slug('sub ' . $name),
            'category_id' => Category::factory(), // This links it to a Category
            'description' => $this->faker->sentence(), // Added this line
        ];
    }
}