<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\HelperMethods;
use Symfony\Component\HttpFoundation\Response;



class CategoryController extends Controller
{

    protected array $typeOfFields = ['textFields', 'imageFields'];

    protected array $textFields = [
        'name',
        'description',
        'slug',
        'type',
    ];

    protected array $imageFields = [
        'image',
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
            'type' => 'nullable|string',
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @OA\Post(
     * path="/api/categories",
     * operationId="storeCategory",
     * tags={"Categories"},
     * summary="Create a new category",
     * description="Creates a new category with a name, description, and optional image.",
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="name", type="string", description="Category name", example="Electronics"),
     * @OA\Property(property="slug", type="string", description="URL-friendly slug", example="electronics"),
     * @OA\Property(property="description", type="string", description="Category description", example="Electronic gadgets and devices."),
     * @OA\Property(property="type", type="string", description="Category type", example="product"),
     * @OA\Property(property="image", type="string", format="binary", description="Image file to upload."),
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Category created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Category created successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/Category"),
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

            $data = new Category();

            HelperMethods::populateModelFields(
                $data,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'textFields' => $this->textFields,
                    'imageFields' => $this->imageFields,
                ]
            );

            $data->save();

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
     * Display the specified category.
     *
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     operationId="showCategory",
     *     tags={"Categories"},
     *     summary="Get details of a single category",
     *     description="Fetch a single category by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category to fetch",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show(Category $category)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $category
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch category.');
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Category $category
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * @OA\Post(
     * path="/api/categories/{id}?_method=PUT",
     * operationId="updateCategory",
     * tags={"Categories"},
     * summary="Update an existing category",
     * description="Updates an existing category and its image.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID of the category to update",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="_method",
     * in="query",
     * required=true,
     * description="Use 'PUT' to override POST method for L5-Swagger.",
     * @OA\Schema(type="string", default="PUT")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="name", type="string", description="New category name", example="Mobile Phones"),
     * @OA\Property(property="slug", type="string", description="New URL-friendly slug", example="mobile-phones"),
     * @OA\Property(property="description", type="string", description="Updated category description", example="Latest smartphones and accessories."),
     * @OA\Property(property="type", type="string", description="Updated category type", example="product"),
     * @OA\Property(property="image", type="string", format="binary", description="New image file to upload."),
     * )
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
            // Validate request
            $validated = $this->validateRequest($request);

            // Retrieve the category by ID
            $data = Category::findOrFail($id);

            // Populate model fields using helper method
            HelperMethods::populateModelFields(
                $data,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'textFields' => $this->textFields,
                    'imageFields' => $this->imageFields,
                ]
            );

            // Save updated model
            $data->save();

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
     * description="Deletes a category and its associated media.",
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
            // Attempt to delete the category
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
