<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 * schema="Product",
 * title="Product",
 * )
 */

class Product extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'name',
        'slug',
        'description',
        'category_id',
        'sub_category_id', // <-- add this
        'price',
        'old_price',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }
}
