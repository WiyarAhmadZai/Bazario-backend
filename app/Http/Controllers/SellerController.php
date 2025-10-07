<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        $perPage = $request->get('per_page', 12); // Default to 12, but allow dynamic values
        $perPage = in_array($perPage, [10, 25, 50, 100, 150]) ? $perPage : 12; // Validate allowed values
        $products = $query->paginate($perPage);

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
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:10240'
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

        Log::info('=== UPDATE PRODUCT REQUEST START ===', [
            'product_id' => $id,
            'user_id' => $user->id,
            'request_method' => $request->method(),
            'request_headers' => $request->headers->all(),
            'request_data' => $request->all(),
            'request_files' => $request->allFiles()
        ]);

        $product = Product::where('id', $id)->where('seller_id', $user->id)->firstOrFail();

        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'price' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:999999.99',
                'stock' => 'nullable|integer|min:0',
                'category_id' => 'nullable|exists:categories,id',
                'category_enum' => 'nullable|in:' . implode(',', CategoryEnum::values()),
                'is_featured' => 'nullable|boolean',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:10240'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        }

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

        // For updates, we want to allow empty values to clear fields
        // Only remove null values, but keep empty strings
        $productData = array_filter($productData, function ($value) {
            return $value !== null;
        });

        Log::info('Product data after filtering', [
            'original_product' => $product->toArray(),
            'filtered_data' => $productData
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
        } else {
            // If no new images provided, keep existing images
            $productData['images'] = $product->images;
        }

        Log::info('About to update product in database', [
            'product_id' => $id,
            'data_to_update' => $productData
        ]);

        $updateResult = $product->update($productData);

        Log::info('Database update result', [
            'update_result' => $updateResult,
            'product_after_update' => $product->fresh()->toArray()
        ]);

        Log::info('=== UPDATE PRODUCT REQUEST SUCCESS ===', [
            'product_id' => $id,
            'updated_data' => $productData,
            'final_product' => $product->toArray()
        ]);

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
