<?php

namespace App\Services\Product;

use App\Models\Media;
use App\Models\Product;
use App\Helpers\HelperMethods; // Assuming you'll keep using your helper

class ProductCreateService
{
    /**
     * Creates a new product and handles associated media.
     *
     * @param array $validatedData
     * @param array $images
     * @return Product
     */
    public function createProduct(array $validatedData, array $images = [])
    {
        $product = Product::create($validatedData);

        // Handle multiple image uploads
        if (!empty($images)) {
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