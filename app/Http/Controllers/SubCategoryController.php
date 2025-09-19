<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubCategoryController extends Controller
{

    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer|exists:categories,id', // filter by category if needed
            ]);

            $paginate_count = $validated['paginate_count'] ?? 10;
            $search = $validated['search'] ?? null;
            $categoryId = $validated['category_id'] ?? null;

            $query = SubCategory::with('category')->latest();

            if ($search) {
                $query->where('name', 'like', $search . '%');
            }

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            $subCategories = $query->paginate($paginate_count);

            return response()->json([
                'success' => true,
                'data' => $subCategories,
                'current_page' => $subCategories->currentPage(),
                'total_pages' => $subCategories->lastPage(),
                'per_page' => $subCategories->perPage(),
                'total' => $subCategories->total(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch subcategories.');
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
            ]);

            $subCategory = SubCategory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'SubCategory created successfully.',
                'data' => $subCategory
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to create subcategory.');
        }
    }


    public function show(SubCategory $subCategory)
    {
        try {
            $subCategory->load('category');

            return response()->json([
                'success' => true,
                'data' => $subCategory
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch subcategory.');
        }
    }


    public function update(Request $request, SubCategory $subCategory)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
            ]);

            $subCategory->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'SubCategory updated successfully.',
                'data' => $subCategory
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update subcategory.');
        }
    }


    public function destroy(SubCategory $subCategory)
    {
        try {
            $subCategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'SubCategory deleted successfully.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to delete subcategory.');
        }
    }



    public function create() {}
    public function edit(SubCategory $subCategory) {}
}
