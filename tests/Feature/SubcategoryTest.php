<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SubcategoryTest extends TestCase
{
    use RefreshDatabase; // Use this to reset the database for each test

    /**
     * Test the index method to get a paginated list of subcategories.
     *
     * @return void
     */
    public function test_can_get_subcategories()
    {
        // Create a category and some subcategories to test
        $category = Category::factory()->create();
        SubCategory::factory()->count(15)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/sub-categories');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'slug', 'description', 'category_id']
                    ],
                ],
                'current_page',
                'total_pages',
                'per_page',
                'total',
            ])
            ->assertJsonCount(10, 'data.data'); // Default pagination is 10
    }

    // ----------------------------------------------------

    /**
     * Test the store method to create a new subcategory.
     *
     * @return void
     */
    public function test_can_create_a_subcategory()
    {
        $category = Category::factory()->create();
        $subCategoryData = [
            'category_id' => $category->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Mobile phones and accessories.',
        ];

        $response = $this->postJson('/api/sub-categories', $subCategoryData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'success' => true,
                'message' => 'SubCategory created successfully.',
            ]);

        $this->assertDatabaseHas('sub_categories', [
            'name' => 'Smartphones',
            'category_id' => $category->id,
        ]);
    }

    // ----------------------------------------------------

    /**
     * Test the store method with invalid data.
     *
     * @return void
     */
    public function test_cannot_create_a_subcategory_with_invalid_data()
    {
        $category = Category::factory()->create();
        $invalidData = [
            'category_id' => $category->id,
            'name' => '', // Fails validation
            'slug' => 'invalid-slug',
        ];

        $response = $this->postJson('/api/sub-categories', $invalidData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name']);
    }

    // ----------------------------------------------------

    /**
     * Test the show method to get a single subcategory by ID.
     *
     * @return void
     */
    public function test_can_get_a_single_subcategory_by_id()
    {
        $category = Category::factory()->create();
        $subCategory = SubCategory::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/sub-categories/' . $subCategory->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $subCategory->id,
                    'name' => $subCategory->name,
                ],
            ]);
    }

    // ----------------------------------------------------

    /**
     * Test the update method to update a subcategory.
     *
     * @return void
     */
    public function test_can_update_a_subcategory()
    {
        $category = Category::factory()->create();
        $subCategory = SubCategory::factory()->create(['category_id' => $category->id]);

        $updatedData = [
            'category_id' => $category->id,
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
            'description' => 'Updated description.',
        ];

        $response = $this->putJson('/api/sub-categories/' . $subCategory->id, $updatedData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'SubCategory updated successfully.',
            ]);

        $this->assertDatabaseHas('sub_categories', [
            'id' => $subCategory->id,
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
        ]);
    }

    // ----------------------------------------------------

    /**
     * Test the destroy method to delete a subcategory.
     *
     * @return void
     */
    public function test_can_delete_a_subcategory()
    {
        $category = Category::factory()->create();
        $subCategory = SubCategory::factory()->create(['category_id' => $category->id]);

        $response = $this->deleteJson('/api/sub-categories/' . $subCategory->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'success' => true,
                'message' => 'SubCategory deleted successfully.',
            ]);

        $this->assertDatabaseMissing('sub_categories', [
            'id' => $subCategory->id,
        ]);
    }
}