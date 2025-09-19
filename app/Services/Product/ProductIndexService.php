<?php

namespace App\Services\Product;

use App\Models\Product;

class ProductIndexService
{
    /**
     * Retrieves and paginates products with filters.
     *
     * @param  array $filters
     * @param  int   $paginateCount
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getProducts(array $filters, int $paginateCount = 10)
    {
        $query = Product::with([
            'media',
            'category:id,name',
            'subCategory:id,name'
        ])
            ->orderBy('updated_at', 'desc');

        // Apply filters based on the array
        if (isset($filters['arrival_status'])) {
            $query->where('arrival_status', $filters['arrival_status']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', $filters['search'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->paginate($paginateCount);
    }
}