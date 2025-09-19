<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubCategoryController extends Controller
{
    /**
     * @OA\Info(
     *     title="SubCategory API",
     *     version="1.0.0",
     *     description="API endpoints for managing subcategories"
     * )
     *
     * @OA\Server(
     *     url=L5_SWAGGER_CONST_HOST,
     *     description="L5 Swagger OpenApi"
     * )
     *
     * @OA\Tag(
     *     name="SubCategories",
     *     description="API endpoints for managing subcategories"
     * )
     *
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

    /**
     * @OA\Get(
     *     path="/api/sub-categories",
     *     operationId="getSubCategories",
     *     tags={"SubCategories"},
     *     summary="Get a paginated list of subcategories",
     *     @OA\Parameter(
     *         name="paginate_count",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search subcategories by name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter subcategories by category ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubCategory")),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="total_pages", type="integer", example=3),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="total", type="integer", example=25)
     *         )
     *     )
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
     *     path="/api/sub-categories",
     *     operationId="storeSubCategory",
     *     tags={"SubCategories"},
     *     summary="Create a new subcategory",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Smartphones"),
     *                 @OA\Property(property="slug", type="string", example="smartphones"),
     *                 @OA\Property(property="description", type="string", example="Mobile devices and accessories"),
     *                 @OA\Property(property="category_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SubCategory created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SubCategory")
     *     ),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */



    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'slug' => 'required|string|max:255|unique:sub_categories,slug',
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

    /**
     * Display the specified subcategory by ID or slug.
     *
     * @OA\Get(
     *     path="/api/sub-categories/{identifier}",
     *     operationId="showSubCategory",
     *     tags={"SubCategories"},
     *     summary="Get a single subcategory by ID or slug",
     *     description="Fetch a subcategory by its numeric ID or slug string.",
     *     @OA\Parameter(
     *         name="identifier",
     *         in="path",
     *         required=true,
     *         description="ID (integer) or slug (string) of the subcategory",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SubCategory fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Smartphones"),
     *                 @OA\Property(property="slug", type="string", example="smartphones"),
     *                 @OA\Property(property="description", type="string", example="Mobile phones and accessories"),
     *                 @OA\Property(property="category_id", type="integer", example=2),
     *                 @OA\Property(property="category", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SubCategory not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
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
     * @OA\Post(
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
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="_method", type="string", format="hidden", example="PUT", description="Method spoofing for PUT request."),
     * @OA\Property(property="name", type="string", example="Updated Name"),
     * @OA\Property(property="slug", type="string", example="updated-name"),
     * @OA\Property(property="description", type="string", example="Updated description"),
     * @OA\Property(property="category_id", type="integer", example=1)
     * )
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
     * @OA\Response(
     * response=404,
     * description="SubCategory not found"
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error"
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal Server Error"
     * )
     * )
     */


    public function update(Request $request, SubCategory $subCategory)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'slug' => 'nullable|string|max:255|unique:sub_categories,slug',
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
     *     path="/api/sub-categories/{id}",
     *     operationId="deleteSubCategory",
     *     tags={"SubCategories"},
     *     summary="Delete a subcategory",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the subcategory",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SubCategory deleted successfully"
     *     )
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
