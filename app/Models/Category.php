<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description']; // adjust fields as needed


    public function products()
    {
        return $this->hasMany(Product::class);
    }

     public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
