<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::with('category', 'seller')
            ->where('status', 'approved'); // Only show approved products in the shop

        // Filter by search term
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by category ID
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by seller
        if ($request->has('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Exclude specific product
        if ($request->has('exclude')) {
            $query->where('id', '!=', $request->exclude);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        // Filter by price range
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sort products
        switch ($request->sort_by) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('title', 'asc');
                break;
        }

        // Check if we want all products
        if ($request->has('all') && $request->all == true) {
            $products = $query->get(); // Get all products without pagination
            return response()->json($products);
        } else {
            // Paginate by default
            $perPage = $request->get('per_page', 12); // Default to 12, but allow dynamic values
            $perPage = in_array($perPage, [10, 25, 50, 100, 150]) ? $perPage : 12; // Validate allowed values
            $products = $query->paginate($perPage);
            return response()->json($products);
        }
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($request->all());

        return response()->json($product, 201);
    }

    /**
     * Display the specified product
     */
    public function show($id)
    {
        $product = Product::with('category', 'reviews.user', 'seller')->findOrFail($id);

        // Increment view count
        $product->increment('view_count');

        // Reload the product to get the updated view_count
        $product->refresh();

        return response()->json($product);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $product->update($request->all());

        return response()->json($product);
    }

    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * Add product to favorites
     */
    public function addToFavorites($id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // Check if already favorited
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingFavorite) {
            return response()->json([
                'message' => 'Product already in favorites',
                'is_favorited' => true,
                'favorites_count' => $product->favorites()->count()
            ]);
        }

        // Add to favorites
        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        return response()->json([
            'message' => 'Product added to favorites',
            'is_favorited' => true,
            'favorites_count' => $product->favorites()->count()
        ]);
    }

    /**
     * Remove product from favorites
     */
    public function removeFromFavorites($id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // Remove from favorites
        $deleted = Favorite::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Product removed from favorites',
                'is_favorited' => false,
                'favorites_count' => $product->favorites()->count()
            ]);
        }

        return response()->json([
            'message' => 'Product was not in favorites',
            'is_favorited' => false,
            'favorites_count' => $product->favorites()->count()
        ]);
    }

    /**
     * Get user's favorite products
     */
    public function getFavorites()
    {
        $user = Auth::user();

        $favorites = $user->favoriteProducts()
            ->with(['category', 'seller'])
            ->paginate(12);

        return response()->json($favorites);
    }
}
