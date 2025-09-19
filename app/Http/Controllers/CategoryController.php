<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\HelperMethods;
use Symfony\Component\HttpFoundation\Response;


class CategoryController extends Controller
{

    protected array $typeOfFields = ['textFields'];

    protected array $textFields = [
        'name',
        'description',
        'slug',
    ];

    /**
     * Validate the request data for category creation or update.
     *
     * @param Request $request
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string',
            'description' => 'nullable|string',
        ]);
    }

    /**
     * @OA\Get(
     * path="/api/categories",
     * operationId="getCategoriesList",
     * tags={"Categories"},
     * summary="Get a paginated list of categories",
     * description="Returns a paginated list of categories, with optional search.",
     * @OA\Parameter(
     * name="paginate_count",
     * in="query",
     * description="Number of categories per page",
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Search categories by name",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(
     * property="data",
     * type="object",
     * @OA\Property(
     * property="current_page",
     * type="integer",
     * example=1
     * ),
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(ref="#/components/schemas/Category")
     * ),
     * @OA\Property(
     * property="first_page_url",
     * type="string",
     * example="http://localhost:8961/api/categories?page=1"
     * ),
     * @OA\Property(
     * property="last_page",
     * type="integer",
     * example=3
     * ),
     * @OA\Property(
     * property="last_page_url",
     * type="string",
     * example="http://localhost:8961/api/categories?page=3"
     * ),
     * @OA\Property(
     * property="next_page_url",
     * type="string",
     * nullable=true,
     * example="http://localhost:8961/api/categories?page=2"
     * ),
     * @OA\Property(
     * property="prev_page_url",
     * type="string",
     * nullable=true,
     * example=null
     * ),
     * @OA\Property(
     * property="per_page",
     * type="integer",
     * example=10
     * ),
     * @OA\Property(
     * property="total",
     * type="integer",
     * example=25
     * )
     * ),
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="total_pages", type="integer", example=3),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="total", type="integer", example=25)
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal Server Error"
     * )
     * )
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
            ]);

            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;

            $query = Category::query();

            if ($search) {
                $query->where('name', 'like', $search . '%');
            }

            $categories = $query->paginate($paginate_count);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'current_page' => $categories->currentPage(),
                'total_pages' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch categories.');
        }
    }

    /**
     * @OA\Post(
     * path="/api/categories",
     * operationId="storeCategory",
     * tags={"Categories"},
     * summary="Create a new category",
     * description="Creates a new category with a name, slug, and description.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", description="Category name", example="Electronics"),
     * @OA\Property(property="slug", type="string", description="URL-friendly slug", example="electronics"),
     * @OA\Property(property="description", type="string", description="Category description", example="Electronic gadgets and devices.")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Category created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Category created successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/Category")
     * )
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
    public function store(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);
            $data = Category::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to create category.');
        }
    }

/**
     * @OA\Get(
     * path="/api/categories/{identifier}",
     * operationId="showCategory",
     * tags={"Categories"},
     * summary="Get details of a single category",
     * description="Fetch a single category by its ID or slug.",
     * @OA\Parameter(
     * name="identifier",
     * in="path",
     * required=true,
     * description="ID (integer) or slug (string) of the category to fetch",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Category fetched successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Category")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Category not found"
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal Server Error"
     * )
     * )
     */
    public function show($identifier)
    {
        try {
            // Find the category by either its 'id' or 'slug'
            $category = Category::where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $category
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch category.');
        }
    }

    /**
     * @OA\Put(
     * path="/api/categories/{id}",
     * operationId="updateCategory",
     * tags={"Categories"},
     * summary="Update an existing category",
     * description="Updates an existing category.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the category to update",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", description="New category name", example="Mobile Phones"),
     * @OA\Property(property="slug", type="string", description="New URL-friendly slug", example="mobile-phones"),
     * @OA\Property(property="description", type="string", description="Updated category description", example="Latest smartphones and accessories.")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Category updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Category updated successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/Category"),
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Category not found"
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
    public function update(Request $request, $id)
    {
        try {
            $validated = $this->validateRequest($request);
            $data = Category::findOrFail($id);
            $data->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update category.');
        }
    }

    /**
     * @OA\Delete(
     * path="/api/categories/{id}",
     * operationId="deleteCategory",
     * tags={"Categories"},
     * summary="Delete a category",
     * description="Deletes a category.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the category to delete",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Category deleted successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Category deleted successfully."),
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Category not found"
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal Server Error"
     * )
     * )
     */
    public function destroy($id)
    {
        try {
            $data = Category::findOrFail($id);
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to delete category.');
        }
    }
}