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
     * operationId="getProductsList",
     * tags={"Products"},
     * summary="Get a paginated list of products",
     * description="Returns a paginated list of products, with optional search and filters.",
     * @OA\Parameter(
     * name="paginate_count",
     * in="query",
     * description="Number of products per page",
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Search products by name or other attributes",
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="status",
     * in="query",
     * description="Filter products by status",
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="category_id",
     * in="query",
     * description="Filter products by category ID",
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
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(ref="#/components/schemas/Product")
     * ),
     * @OA\Property(property="first_page_url", type="string", example="http://localhost/api/products?page=1"),
     * @OA\Property(property="last_page", type="integer", example=3),
     * @OA\Property(property="last_page_url", type="string", example="http://localhost/api/products?page=3"),
     * @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost/api/products?page=2"),
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
                'status' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer'
            ]);

            $filters = collect($validated)->except('paginate_count')->toArray();
            $paginate_count = $validated['paginate_count'] ?? 10;

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

    // ----------------------------------------------------

    /**
     * @OA\Get(
     * path="/api/products/{identifier}",
     * operationId="showProduct",
     * tags={"Products"},
     * summary="Get a single product by ID or slug",
     * description="Fetch a product by its numeric ID or slug string.",
     * @OA\Parameter(
     * name="identifier",
     * in="path",
     * required=true,
     * description="ID (integer) or slug (string) of the product",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Product fetched successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Data retrieved successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/Product")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Product not found"
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

    // ----------------------------------------------------

    /**
     * @OA\Post(
     * path="/api/products",
     * tags={"Products"},
     * summary="Create a new product",
     * description="This endpoint allows the creation of a new product record, including multiple image uploads.",
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="name", type="string", description="The product name."),
     * @OA\Property(property="slug", type="string", description="The product URL slug."),
     * @OA\Property(property="description", type="string", nullable=true, description="The product description."),
     * @OA\Property(property="category_id", type="integer", description="The ID of the product's category."),
     * @OA\Property(property="sub_category_id", type="integer", description="The ID of the product's sub-category."),
     * @OA\Property(property="price", type="number", format="float", description="The product price."),
     * @OA\Property(property="old_price", type="number", format="float", nullable=true, description="The product's old price."),
     * @OA\Property(
     * property="images[]",
     * type="array",
     * @OA\Items(
     * type="string",
     * format="binary"
     * ),
     * description="Array of images to upload."
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Product created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Product created successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/Product")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error"
     * )
     * )
     */
    public function store(StoreProductRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $images = $request->file('images', []);

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

    // ----------------------------------------------------

    /**
     * @OA\Put(
     * path="/api/products/{id}",
     * tags={"Products"},
     * summary="Update an existing product",
     * description="This endpoint updates an existing product record, including new images. The request uses the PUT HTTP method.",
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer"),
     * description="The ID of the product to update."
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="name", type="string", nullable=true),
     * @OA\Property(property="slug", type="string", nullable=true),
     * @OA\Property(property="description", type="string", nullable=true),
     * @OA\Property(property="category_id", type="integer", nullable=true),
     * @OA\Property(property="sub_category_id", type="integer", nullable=true),
     * @OA\Property(property="price", type="number", format="float", nullable=true),
     * @OA\Property(property="old_price", type="number", format="float", nullable=true),
     * @OA\Property(
     * property="image",
     * type="string",
     * format="binary",
     * description="The main product image (optional)."
     * ),
     * @OA\Property(
     * property="images[]",
     * type="array",
     * @OA\Items(
     * type="string",
     * format="binary"
     * ),
     * description="An array of additional images to upload (optional)."
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Product updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Product updated successfully."),
     * @OA\Property(property="data", ref="#/components/schemas/Product")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Product not found"
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error"
     * )
     * )
     */
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $product = Product::findOrFail($id);

            $mainImage = $request->file('image');
            $images = $request->hasFile('images') ? $request->file('images') : [];

            $product = ProductUpdateService::updateProduct($product, $validatedData, $mainImage, $images);
            $product->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $product,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update product.');
        }
    }

    // ----------------------------------------------------

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
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Product deleted successfully.")
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

    // ----------------------------------------------------
    // Other unused methods from resource controller
    public function create() {}
    public function edit(Product $product) {}
}
