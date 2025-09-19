<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="SubCategory",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Smartphones"),
 *     @OA\Property(property="slug", type="string", example="smartphones"),
 *     @OA\Property(property="description", type="string", example="Mobile devices and accessories"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-19T18:05:05.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-19T18:05:05.000000Z")
 * )
 */

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'description', 'slug']; // adjust fields as needed

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
