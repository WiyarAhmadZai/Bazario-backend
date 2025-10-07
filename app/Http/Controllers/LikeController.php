<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    /**
     * Like a product
     */
    public function like(Request $request, $productId)
    {
        try {
            $user = Auth::user();
            $product = Product::findOrFail($productId);

            // Check if user already liked this product
            $existingLike = Like::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($existingLike) {
                return response()->json([
                    'message' => 'Product already liked',
                    'liked' => true,
                    'like_count' => $product->likes()->count()
                ], 200);
            }

            // Create new like
            Like::create([
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            $likeCount = $product->likes()->count();

            return response()->json([
                'message' => 'Product liked successfully',
                'liked' => true,
                'like_count' => $likeCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Like product failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to like product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlike a product
     */
    public function unlike(Request $request, $productId)
    {
        try {
            $user = Auth::user();
            $product = Product::findOrFail($productId);

            // Find and delete the like
            $like = Like::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if (!$like) {
                return response()->json([
                    'message' => 'Product not liked',
                    'liked' => false,
                    'like_count' => $product->likes()->count()
                ], 200);
            }

            $like->delete();
            $likeCount = $product->likes()->count();

            return response()->json([
                'message' => 'Product unliked successfully',
                'liked' => false,
                'like_count' => $likeCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Unlike product failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to unlike product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get like status for a product
     */
    public function getLikeStatus(Request $request, $productId)
    {
        try {
            $user = Auth::user();
            $product = Product::findOrFail($productId);

            $liked = Like::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            $likeCount = $product->likes()->count();

            return response()->json([
                'liked' => $liked,
                'like_count' => $likeCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get like status failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to get like status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get like count for a product (public)
     */
    public function getLikeCount($productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $likeCount = $product->likes()->count();

            return response()->json([
                'like_count' => $likeCount
            ], 200);
        } catch (\Exception $e) {
            Log::error('Get like count failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to get like count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
