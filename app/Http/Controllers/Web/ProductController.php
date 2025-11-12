<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the products from external API.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        try {
            // Fetch products from the external API
            $response = Http::get('https://fakestoreapi.com/products');
            
            if ($response->successful()) {
                $products = $response->json();
                
                return Inertia::render('Products/Index', [
                    'products' => $products,
                    'status' => 'success',
                ]);
            }
            
            // If the API call fails
            return Inertia::render('Products/Index', [
                'products' => [],
                'status' => 'error',
                'message' => 'Failed to load products. Please try again later.'
            ]);
            
        } catch (\Exception $e) {
            return Inertia::render('Products/Index', [
                'products' => [],
                'status' => 'error',
                'message' => 'An error occurred while fetching products.'
            ]);
        }
    }
}
