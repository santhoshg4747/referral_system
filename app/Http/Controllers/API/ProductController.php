<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Fetch and display products from external API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://fakestoreapi.com/products');
            
            if ($response->getStatusCode() === 200) {
                $products = json_decode($response->getBody(), true);
                
                // Transform the data to match our expected format
                $formattedProducts = array_map(function($product) {
                    return [
                        'id' => $product['id'],
                        'title' => $product['title'],
                        'description' => $product['description'],
                        'price' => $product['price'],
                        'image_url' => $product['image'],
                        'category' => $product['category']
                    ];
                }, $products);
                
                return $this->sendResponse($formattedProducts);
            }
            
            return $this->sendError('Failed to fetch products from external API', [], 500);
            
        } catch (\Exception $e) {
            return $this->sendError('Error fetching products: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        
        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_url'] = $this->uploadImage($request->file('image'));
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product = Product::create($validated);

        return $this->sendResponse($product, 'Product created successfully', 201);
    }

    /**
     * Display the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product)
    {
        $product->load('category');
        return $this->sendResponse($product);
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);
        
        // Handle image upload if new image is provided
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_url) {
                Storage::delete('public/' . $product->image_url);
            }
            $validated['image_url'] = $this->uploadImage($request->file('image'));
        }

        // Generate slug if name is changed and slug is not provided
        if ($request->has('name') && !$request->has('slug')) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $product->update($validated);

        return $this->sendResponse($product, 'Product updated successfully');
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        // Delete associated image if exists
        if ($product->image_url) {
            Storage::delete('public/' . $product->image_url);
        }

        $product->delete();

        return $this->sendResponse([], 'Product deleted successfully');
    }

    /**
     * Validate the product request data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|null  $productId
     * @return array
     */
    protected function validateProduct(Request $request, $productId = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($productId)
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId)
            ],
            'image' => 'nullable|image|max:2048', // 2MB max
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'category_id' => 'required|exists:categories,id',
        ];

        return $request->validate($rules);
    }

    /**
     * Upload product image.
     *
     * @param  \Illuminate\Http\UploadedFile  $image
     * @return string
     */
    protected function uploadImage($image)
    {
        $path = $image->store('products', 'public');
        return $path;
    }
}
