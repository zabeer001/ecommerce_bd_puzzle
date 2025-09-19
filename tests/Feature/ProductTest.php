<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting a list of all products.
     */
    public function test_can_get_all_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'slug', 'description', 'price', 'category_id', 'sub_category_id', 'media']
                    ]
                ]
            ])
            ->assertJsonCount(5, 'data.data');
    }

    /**
     * Test creating a new product with images.
     */
    public function test_can_create_a_product_with_images(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $subCategory = SubCategory::factory()->create();
        $file = UploadedFile::fake()->image('test-product.jpg');

        $response = $this->postJson('/api/products', [
            'name' => 'New Test Product',
            'slug' => 'new-test-product',
            'description' => 'A description for the new product.',
            'category_id' => $category->id,
            'sub_category_id' => $subCategory->id,
            'price' => 99.99,
            'images' => [$file]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'slug', 'media']
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => ['name' => 'New Test Product']
            ]);

       
        
        // Assert that the database record was created
        $this->assertDatabaseHas('products', ['slug' => 'new-test-product']);
        $this->assertDatabaseCount('media', 1);
    }

    /**
     * Test updating an existing product and its images.
     */
    public function test_can_update_a_product_with_new_images(): void
    {
        Storage::fake('public');

        // Create a product with an initial image
        $product = Product::factory()->create();
        $oldFile = UploadedFile::fake()->image('old-image.jpg');
        $oldFilePath = 'uploads/' . $oldFile->hashName();
        $product->media()->create(['file_path' => $oldFilePath]);
      

        // Upload a new image for the update
        $newFile = UploadedFile::fake()->image('new-image.png');

        $response = $this->postJson('/api/products/' . $product->id, [
            '_method' => 'PUT',
            'name' => 'Updated Product',
            'slug' => 'updated-product',
            'description' => 'Updated description.',
            'category_id' => $product->category_id,
            'sub_category_id' => $product->sub_category_id,
            'price' => 129.99,
            'images' => [$newFile],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'slug', 'media']
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => ['name' => 'Updated Product']
            ]);

       

        // Assert the database state
        $this->assertDatabaseHas('products', ['slug' => 'updated-product']);
        $this->assertDatabaseCount('media', 1);
    }

    /**
     * Test deleting a product and its associated media.
     */
    public function test_can_delete_a_product(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $file = UploadedFile::fake()->image('image-to-delete.jpg');
        $filePath = 'uploads/' . $file->hashName();
        $product->media()->create(['file_path' => $filePath]);
  

        $response = $this->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);

        // Assert the database records are gone
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseCount('media', 0);
      
       
    }
}