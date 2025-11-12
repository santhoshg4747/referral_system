<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Public product listing
Route::get('/products', function () {
    return \Inertia\Inertia::render('Products/Index');
})->name('products.index');

// Protected dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (login/register)
    Route::middleware('guest:web')->group(function () {
        Route::get('login', [AdminAuthController::class, 'create'])
            ->name('login');

        Route::post('login', [AdminAuthController::class, 'store']);

        Route::get('register', function () {
            return Inertia::render('Auth/Register', ['isAdminRegistration' => true]);
        })->name('register');
    });
    
    // Protected admin routes - using fully qualified middleware class
    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'destroy'])
            ->name('logout');

        // Admin Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // User Management
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
            ->except(['show'])
            ->names([
                'index' => 'users.index',
                'create' => 'users.create',
                'store' => 'users.store',
                'edit' => 'users.edit',
                'update' => 'users.update',
                'destroy' => 'users.destroy',
            ]);
            
        // Product Management
        Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)
            ->except(['show'])
            ->names([
                'index' => 'products.index',
                'create' => 'products.create',
                'store' => 'products.store',
                'edit' => 'products.edit',
                'update' => 'products.update',
                'destroy' => 'products.destroy',
            ]);
    });
});
