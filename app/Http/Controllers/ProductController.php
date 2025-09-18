<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Services\Product\ProductIndexService;
use App\Services\Product\ProductUpdateService;
use App\Helpers\HelperMethods;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Services\Product\ProductCreateService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productCreationService;
    protected $productUpdateService;

    // I made dependecy injection here



    //     public function __construct(
    //     ProductCreateService $productCreationService,
    //     ProductUpdateService $productUpdateService
    // ) {
    //     $this->productCreationService = $productCreationService;
    //     $this->productUpdateService = $productUpdateService;
    // }



    public function __construct(
        ProductCreateService $productCreationService,
        ProductUpdateService $productUpdateService
    ) {
        $this->productCreationService = $productCreationService;
        $this->productUpdateService = $productUpdateService;
    }


     /**
     * @OA\Get(
     * path="/api/products",
     * tags={"Products"},
     * summary="Retrieve a list of all products",
     * description="This endpoint retrieves all product records from the database.",
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Product")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */

    public function index(Request $request)
    {
        // return 0;
        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
                'arrival_status' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer'
            ]);

            $filters = collect($validated)->except('paginate_count')->toArray();
            $paginate_count = $validated['paginate_count'] ?? 10;

            // Call the static method directly on the class
            $data = ProductIndexService::getProducts($filters, $paginate_count);

            return response()->json([
                'success' => true,
                'data' => $data,
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch data.');
        }
    }



    public function show($id)
    {
        try {
            $data = Product::with(['category', 'media', 'reviews.user'])->find($id);
            return response()->json([
                'success' => true,
                'message' => 'Data retrived successfully.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update data.');
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $images = $request->file('images', []);

            $product = $this->productCreationService->createProduct($validatedData, $images);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to create product.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $validatedData = $request->validated();
            $images = $request->file('images', []);

            $updatedProduct = $this->productUpdateService->updateProduct($product, $validatedData, $images);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $updatedProduct,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update product.');
        }
    }


    public function destroy($id)
    {
        try {
            $data = Product::findOrFail($id);
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'data deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to delete data.');
        }
    }
}
