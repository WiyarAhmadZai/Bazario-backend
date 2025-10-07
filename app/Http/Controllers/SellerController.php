<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Enums\CategoryEnum;

class SellerController extends Controller
{
    /**
     * Get seller's products
     */
    public function getProducts(Request $request)
    {
        $user = Auth::user();

        $query = Product::where('seller_id', $user->id)
            ->with('category');

        $products = $query->paginate(12);

        return response()->json($products);
    }

    /**
     * Get a specific product by ID
     */
    public function getProduct($id)
    {
        $user = Auth::user();

        $product = Product::where('id', $id)
            ->where('seller_id', $user->id)
            ->with('category')
            ->firstOrFail();

        return response()->json($product);
    }

    /**
     * Create a new product
     */
    public function createProduct(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'category_enum' => 'nullable|in:' . implode(',', CategoryEnum::values()),
            'is_featured' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $productData = $request->only([
            'title',
            'description',
            'price',
            'discount',
            'stock',
            'category_id',
            'category_enum',
            'is_featured'
        ]);

        // Add seller ID
        $productData['seller_id'] = $user->id;

        // Set status based on user role - admins get auto-approved products
        $productData['status'] = ($user->role === 'admin') ? 'approved' : 'pending';

        // Generate slug from title
        $productData['slug'] = Str::slug($request->title);

        // If category_enum is provided, use it; otherwise, use category_id
        if ($request->filled('category_enum')) {
            $productData['category_enum'] = $request->category_enum;
        } elseif ($request->filled('category_id')) {
            $productData['category_id'] = $request->category_id;
        }

        // Handle images if provided
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $productData['images'] = json_encode($imagePaths);
        }

        $product = Product::create($productData);

        return response()->json($product, 201);
    }

    /**
     * Update a product
     */
    public function updateProduct(Request $request, $id)
    {
        $user = Auth::user();

        $product = Product::where('id', $id)->where('seller_id', $user->id)->firstOrFail();

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:999999.99',
            'stock' => 'sometimes|required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'category_enum' => 'nullable|in:' . implode(',', CategoryEnum::values()),
            'is_featured' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $productData = $request->only([
            'title',
            'description',
            'price',
            'discount',
            'stock',
            'category_id',
            'category_enum',
            'is_featured'
        ]);

        // Generate slug from title if title is being updated
        if ($request->filled('title')) {
            $productData['slug'] = Str::slug($request->title);
        }

        // If category_enum is provided, use it; otherwise, use category_id
        if ($request->filled('category_enum')) {
            $productData['category_enum'] = $request->category_enum;
        } elseif ($request->filled('category_id')) {
            $productData['category_id'] = $request->category_id;
        }

        // Handle images if provided
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = $path;
            }
            $productData['images'] = json_encode($imagePaths);
        }

        $product->update($productData);

        return response()->json($product);
    }

    /**
     * Delete a product
     */
    public function deleteProduct($id)
    {
        $user = Auth::user();

        $product = Product::where('id', $id)->where('seller_id', $user->id)->firstOrFail();
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
