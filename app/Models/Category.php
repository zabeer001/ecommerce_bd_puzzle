<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="Category",
 * title="Category",
 * description="Category model",
 * @OA\Property(property="id", type="integer", readOnly="true", example=1),
 * @OA\Property(property="name", type="string", example="Electronics"),
 * @OA\Property(property="description", type="string", example="Electronic gadgets and devices."),
 * @OA\Property(property="slug", type="string", example="electronics"),
 * @OA\Property(property="type", type="string", example="product"),
 * @OA\Property(property="image", type="string", example="http://localhost:8000/uploads/categories/image.jpg"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2024-05-24T10:12:12.000000Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", example="2024-05-24T10:12:12.000000Z"),
 * )
 */

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description','slug']; // adjust fields as needed


    public function products()
    {
        return $this->hasMany(Product::class);
    }

     public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
