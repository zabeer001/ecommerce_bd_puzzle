<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubCategoryController extends Controller
{
   

    /**
     * Validate the request data for subcategory creation or update.
     *
     * @param Request $request
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:sub_categories,slug',
            'category_id' => 'required|exists:categories,id',
        ]);
    }

    /**
     * @OA\Get(
     * path="/api/sub-categories",
     * operationId="getSubCategories",
     * tags={"SubCategories"},
     * summary="Get a paginated list of subcategories",
     * @OA\Parameter(
     * name="paginate_count",
     * in="query",
     * description="Number of items per page",
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Search subcategories by name",
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="category_id",
     * in="query",
     * description="Filter subcategories by category ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(
     * property="data",
     * type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategory")),
     * @OA\Property(property="first_page_url", type="string", example="http://localhost:8961/api/sub-categories?page=1"),
     * @OA\Property(property="last_page", type="integer", example=3),
     * @OA\Property(property="last_page_url", type="string", example="http://localhost:8961/api/sub-categories?page=3"),
     * @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost:8961/api/sub-categories?page=2"),
     * @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="total", type="integer", example=25)
     * ),
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="total_pages", type="integer", example=3),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="total", type="integer", example=25)
     * )
     * ),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer|exists:categories,id',
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

    /**
     * @OA\Post(
     * path="/api/sub-categories",
     * operationId="storeSubCategory",
     * tags={"SubCategories"},
     * summary="Create a new subcategory",
     * description="Creates a new subcategory with name, slug, description, and category_id.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "slug", "category_id"},
     * @OA\Property(property="name", type="string", example="Smartphones"),
     * @OA\Property(property="slug", type="string", example="smartphones"),
     * @OA\Property(property="description", type="string", example="Mobile devices and accessories"),
     * @OA\Property(property="category_id", type="integer", example=1)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="SubCategory created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="SubCategory created successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/SubCategory")
     * )
     * ),
     * @OA\Response(response=422, description="Validation Error"),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);
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

    /**
     * @OA\Get(
     * path="/api/sub-categories/{identifier}",
     * operationId="showSubCategory",
     * tags={"SubCategories"},
     * summary="Get a single subcategory by ID or slug",
     * description="Fetch a subcategory by its numeric ID or slug string.",
     * @OA\Parameter(
     * name="identifier",
     * in="path",
     * required=true,
     * description="ID (integer) or slug (string) of the subcategory",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="SubCategory fetched successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/SubCategory")
     * )
     * ),
     * @OA\Response(response=404, description="SubCategory not found"),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show($identifier)
    {
        try {
            $subCategory = SubCategory::with('category')
                ->where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $subCategory
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch subcategory.');
        }
    }

    /**
     * @OA\Put(
     * path="/api/sub-categories/{id}",
     * operationId="updateSubCategory",
     * tags={"SubCategories"},
     * summary="Update a subcategory",
     * description="Updates an existing subcategory.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the subcategory",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Updated Name"),
     * @OA\Property(property="slug", type="string", example="updated-name"),
     * @OA\Property(property="description", type="string", example="Updated description"),
     * @OA\Property(property="category_id", type="integer", example=1)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="SubCategory updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="SubCategory updated successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/SubCategory")
     * )
     * ),
     * @OA\Response(response=404, description="SubCategory not found"),
     * @OA\Response(response=422, description="Validation Error"),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $subCategory = SubCategory::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'slug' => 'required|string|max:255|unique:sub_categories,slug,' . $subCategory->id,
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

    /**
     * @OA\Delete(
     * path="/api/sub-categories/{id}",
     * operationId="deleteSubCategory",
     * tags={"SubCategories"},
     * summary="Delete a subcategory",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the subcategory",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="SubCategory deleted successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="SubCategory deleted successfully.")
     * )
     * ),
     * @OA\Response(response=404, description="SubCategory not found"),
     * @OA\Response(response=500, description="Internal Server Error")
     * )
     */
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