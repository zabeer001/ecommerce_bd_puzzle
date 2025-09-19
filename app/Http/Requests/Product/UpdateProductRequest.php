<?php

namespace App\Http\Requests\Product;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
       
        $productId = $this->route('product');

        return [
            'name' => 'nullable|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'status' => 'nullable|string|max:255',
            'arrival_status' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }
}