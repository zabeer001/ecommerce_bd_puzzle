<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
   
    public function index()
    {
        $subCategories = SubCategory::with('category')->latest()->paginate(10);
        return response()->json($subCategories);
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory = SubCategory::create($request->all());

        return response()->json(['message' => 'SubCategory created successfully.', 'data' => $subCategory], 201);
    }

   
    public function show(SubCategory $subCategory)
    {
        $subCategory->load('category');
        return response()->json($subCategory);
    }

   
    public function update(Request $request, SubCategory $subCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory->update($request->all());

        return response()->json(['message' => 'SubCategory updated successfully.', 'data' => $subCategory]);
    }

    
    public function destroy(SubCategory $subCategory)
    {
        $subCategory->delete();
        return response()->json(['message' => 'SubCategory deleted successfully.']);
    }

 
    public function create() {}
    public function edit(SubCategory $subCategory) {}
}
