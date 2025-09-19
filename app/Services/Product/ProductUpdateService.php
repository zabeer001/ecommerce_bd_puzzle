<?php

namespace App\Services\Product;

use App\Models\Media;
use App\Models\Product;
use App\Helpers\HelperMethods;

class ProductUpdateService
{
    /**
     * Updates an existing product and its media.
     *
     * @param Product $product
     * @param array $validatedData
     * @param array|null $images
     * @return Product
     */
    public static function updateProduct(Product $product, array $validatedData, ?array $images = null)
    {
        // Update the main product data first.
        $product->update($validatedData);

        // Only proceed with image updates if new images are provided.
        if ($images !== null && count($images) > 0) {

            // Delete all existing media records and their corresponding files for this product.
            foreach ($product->media as $mediaItem) {
                // Delete the physical file from storage.
                HelperMethods::deleteImage($mediaItem->file_path);
                // Delete the media record from the database.
                $mediaItem->delete();
            }

            // Upload and create new media records for the product.
            foreach ($images as $image) {
                $newImagePath = HelperMethods::uploadImage($image);
                if ($newImagePath) {
                    Media::create([
                        'product_id' => $product->id,
                        'file_path' => $newImagePath,
                    ]);
                }
            }
        }

        return $product->load('media'); // Eager load media for the response
    }
}
