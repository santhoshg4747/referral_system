<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'price',
        'stock', // This will map to the 'stock' column in the database
        'image_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];
    
    // Map the 'stock' attribute to the 'stock' column in the database
    protected $attributes = [
        'stock' => 0,
    ];

    /**
     * The "booting" method of the model.
     */
    // Boot method removed as it was only used for slug generation

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the product's image URL with full path.
     *
     * @return string|null
     */
    public function getImageUrlAttribute($value)
    {
        if (!$value) return null;
        
        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Remove any leading slashes or 'storage/' from the path
        $cleanPath = ltrim($value, '/');
        $cleanPath = preg_replace('#^storage/#', '', $cleanPath);
        
        // Return the URL with a single 'storage/' prefix
        return asset('storage/' . $cleanPath);
    }

    /**
     * Get the product's gallery images with full paths.
     *
     * @param  array|null  $value
     * @return array|null
     */
    public function getGalleryAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        $gallery = is_string($value) ? json_decode($value, true) : $value;
        
        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $gallery);
    }

    /**
     * Scope a query to only include featured products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include in-stock products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
