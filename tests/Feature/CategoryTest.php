<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CategoryTest extends TestCase
{
    use RefreshDatabase; // Resets the database after each test

    /**
     * Test the index method to fetch a list of categories.
     *
     * @return void
     */
    public function test_can_fetch_categories()
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonCount(5, 'data.data')
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'current_page',
                         'data' => [
                             '*' => ['id', 'name', 'slug', 'description']
                         ]
                     ]
                 ]);
    }

    // ----------------------------------------------------

    /**
     * Test the store method for creating a new category.
     *
     * @return void
     */
    public function test_can_create_a_category()
    {
        $categoryData = [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic gadgets and devices.',
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(Response::HTTP_CREATED)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Category created successfully.',
                     'data' => $categoryData
                 ]);

        $this->assertDatabaseHas('categories', $categoryData);
    }

    // ----------------------------------------------------

    /**
     * Test the store method with invalid data.
     *
     * @return void
     */
    public function test_cannot_create_a_category_with_invalid_data()
    {
        $invalidData = [
            'name' => '', // Empty name should fail validation
            'slug' => 'slug',
        ];

        $response = $this->postJson('/api/categories', $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                 ->assertJsonValidationErrors('name');
    }

    // ----------------------------------------------------

    /**
     * Test the show method for a single category.
     *
     * @return void
     */
    public function test_can_show_a_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'success' => true,
                     'data' => $category->toArray()
                 ]);
    }

    // ----------------------------------------------------

    /**
     * Test the update method for a category.
     *
     * @return void
     */
    public function test_can_update_a_category()
    {
        $category = Category::factory()->create();
        $updatedData = [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
            'description' => 'Updated description.',
        ];

        $response = $this->putJson('/api/categories/' . $category->id, $updatedData);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Category updated successfully.',
                     'data' => array_merge(['id' => $category->id], $updatedData)
                 ]);

        $this->assertDatabaseHas('categories', $updatedData);
    }

    // ----------------------------------------------------

    /**
     * Test the destroy method for deleting a category.
     *
     * @return void
     */
    public function test_can_delete_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson('/api/categories/' . $category->id);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Category deleted successfully'
                 ]);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}