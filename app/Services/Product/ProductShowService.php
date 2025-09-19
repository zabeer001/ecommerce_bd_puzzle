<?php

namespace App\Services\Product;

use App\Models\Media;
use App\Models\Product;
use App\Helpers\HelperMethods; // Assuming you'll keep using your helper
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductShowService
{
    /**
     * Creates a new product and handles associated media.
     *
     * @param array $validatedData
     * @param array $images
     * @return Product
     */
     public static function findProduct($identifier)
    {
        $product = Product::with(['category', 'subCategory', 'media'])
            ->when(is_numeric($identifier), function ($query) use ($identifier) {
                return $query->where('id', $identifier);
            }, function ($query) use ($identifier) {
                return $query->where('slug', $identifier);
            })
            ->first();

        if (!$product) {
            throw new ModelNotFoundException('Product not found.');
        }

        return $product;
    }
}