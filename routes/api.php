<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SubCategoryController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/* amit project */





//categories
Route::apiResource('categories', CategoryController::class);

//subcategories
Route::apiResource('sub-categories', SubCategoryController::class);




//products
Route::apiResource('products', ProductController::class);


