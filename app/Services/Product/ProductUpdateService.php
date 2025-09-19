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
        $product->update($validatedData);

        if ($images !== null) {
            // Delete all old images
            $product->media()->each(function ($media) {
                HelperMethods::deleteImage($media->file_path);
                $media->delete();
            });

            // Upload new images
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

        return $product->load('media');
    }
}