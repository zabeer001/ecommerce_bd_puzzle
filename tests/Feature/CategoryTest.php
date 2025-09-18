<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test the index method to display a listing of categories.
     *
     * @return void
     */

    

    //     Assertion 1: It asserts that the top-level key success exists.

    // Assertion 2: It asserts that the top-level key user exists.

    // Assertion 3: It asserts that the key id exists inside the user object.

    // Assertion 4: It asserts that the key name exists inside the user object.

    // Assertion 5: It asserts that the key email exists inside the user object.
    public function test_can_retrieve_all_categories()
    {
        // This test will now simply retrieve all categories that already exist in the database.
        // It does not create new ones to avoid conflicts.
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);

        // Assert that the response has a 'data' key which is an array
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'type',
                        'image',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => [
                        'url',
                        'label',
                        'active',
                    ]
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ],
            'current_page',
            'total_pages',
            'per_page',
            'total',
        ]);
    }


    /**
     * Test the store method to create a new category.
     *
     * @return void
     */
    public function test_can_create_a_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'This is a test description.',
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test Category',
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
        ]);
    }

    /**
     * Test validation for creating a new category.
     *
     * @return void
     */
    public function test_create_category_validation_fails_without_required_fields()
    {
        $response = $this->postJson('/api/categories', [
            'description' => 'some description'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test the show method to display a single category.
     *
     * @return void
     */
    public function test_can_retrieve_a_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => $category->name,
                'description' => $category->description,
            ]);
    }

    /**
     * Test the update method to modify an existing category.
     *
     * @return void
     */
    public function test_can_update_a_category()
    {
        $category = Category::factory()->create();

        $updatedData = [
            'name' => 'Updated Category',
            'description' => 'Updated description.',
        ];

        $response = $this->putJson('/api/categories/' . $category->id, $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Category',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    /**
     * Test validation for updating a category.
     *
     * @return void
     */
    public function test_update_category_validation_fails_without_required_fields()
    {
        $category = Category::factory()->create();

        $response = $this->putJson('/api/categories/' . $category->id, [
            'name' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test the destroy method to delete a category.
     *
     * @return void
     */
    public function test_can_delete_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson('/api/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Category deleted successfully.']);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }
}
