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
use App\Services\Product\ProductShowService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class ProductController extends Controller
{



    // I made dependecy injection here



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




    public function show($identifier)
    {
        try {
            $data = ProductShowService::findProduct($identifier);

            return response()->json([
                'success' => true,
                'message' => 'Data retrieved successfully.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to create product.');
        }
    }



    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *   path="/api/products",
     *   tags={"Products"},
     *   summary="Create a new product",
     *   description="This endpoint allows the creation of a new product record, including image uploads.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(
     *           property="name",
     *           type="string",
     *           description="The product name."
     *         ),
     *         @OA\Property(
     *           property="slug",
     *           type="string",
     *           description="The product URL slug."
     *         ),
     *         @OA\Property(
     *           property="description",
     *           type="string",
     *           nullable=true,
     *           description="The product description."
     *         ),
     *         @OA\Property(
     *           property="category_id",
     *           type="integer",
     *           description="The ID of the product's category."
     *         ),
     *         @OA\Property(
     *           property="sub_category_id",
     *           type="integer",
     *           description="The ID of the product's sub-category."
     *         ),
     *         @OA\Property(
     *           property="price",
     *           type="number",
     *           format="float",
     *           description="The product price."
     *         ),
     *         @OA\Property(
     *           property="old_price",
     *           type="number",
     *           format="float",
     *           nullable=true,
     *           description="The product's old price."
     *         ),
     *         @OA\Property(
     *           property="images",
     *           type="array",
     *           @OA\Items(
     *             type="string",
     *             format="binary"
     *           ),
     *           description="Array of images to upload."
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Product created successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Product")
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error"
     *   )
     * )
     */

    public function store(StoreProductRequest $request)
    {
        // return 0;
        try {
            $validatedData = $request->validated();
            $images = $request->file('images', []);
            // return $images ;

            $product = ProductCreateService::createProduct($validatedData, $images);

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

    /**
     * @OA\Post(
     *   path="/api/products/{id}?_method=PUT",
     *   tags={"Products"},
     *   summary="Update an existing product",
     *   description="This endpoint updates an existing product record, including images.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer"),
     *     description="The ID of the product to update."
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(
     *           property="_method",
     *           type="string",
     *           enum={"PUT", "PATCH"},
     *           description="Override to handle PUT/PATCH requests with form-data."
     *         ),
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="slug", type="string"),
     *         @OA\Property(property="description", type="string", nullable=true),
     *         @OA\Property(property="category_id", type="integer"),
     *         @OA\Property(property="sub_category_id", type="integer"),
     *         @OA\Property(property="price", type="number", format="float"),
     *         @OA\Property(property="old_price", type="number", format="float", nullable=true),
     *         @OA\Property(
     *           property="images",
     *           type="array",
     *           @OA\Items(
     *             type="string",
     *             format="binary"
     *           ),
     *           description="Array of new images to upload."
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Product updated successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Product")
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Product not found"
     *   ),
     *   @OA\Response(
     *     response=422,
     *     description="Validation error"
     *   )
     * )
     */



    public function update(Request $request, $id)
    {
        // dd($request);

        //    return $request->file('images');
        try {
            $product = Product::find($id);

            // Check all request data
            // return response()->json($request->all());

            // Or check files
            $images = $request->file('images', []);

            // return response()->json($images); // debug only

            $data = $request->all(); // use all() since you don't have validated()
            $updatedProduct = ProductUpdateService::updateProduct($product, $data, $images);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $updatedProduct,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update product.');
        }
    }

    /**
     * @OA\Delete(
     * path="/api/products/{id}",
     * tags={"Products"},
     * summary="Delete a product",
     * description="This endpoint deletes a product record and all its associated media.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer"),
     * description="The ID of the product to delete."
     * ),
     * @OA\Response(
     * response=200,
     * description="Product deleted successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="success",
     * type="boolean",
     * example=true
     * ),
     * @OA\Property(
     * property="message",
     * type="string",
     * example="Product deleted successfully."
     * )
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Product not found"
     * )
     * )
     */

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            foreach ($product->media as $mediaItem) {
                HelperMethods::deleteImage($mediaItem->file_path);
            }
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to delete product.');
        }
    }
}
