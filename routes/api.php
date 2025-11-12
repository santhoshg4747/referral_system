<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

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

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/categories/{category}/products', [ProductController::class, 'productsByCategory']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User routes
    Route::get('/user', [UserController::class, 'profile']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Referral routes
    Route::get('/referrals', [UserController::class, 'referrals']);
    Route::get('/referral-stats', [UserController::class, 'referralStats']);
    
    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // User management
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{user}', [AdminController::class, 'showUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        
        // Referral management
        Route::get('/referrals', [AdminController::class, 'allReferrals']);
        Route::get('/stats', [AdminController::class, 'stats']);
        
        // Product management
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        Route::post('/products/{product}/upload-gallery', [ProductController::class, 'uploadGallery']);
        Route::delete('/products/{product}/gallery/{image}', [ProductController::class, 'deleteGalleryImage']);
        
        // Category management
        Route::apiResource('categories', CategoryController::class);
    });
});
