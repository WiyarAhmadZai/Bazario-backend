<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for a product
     */
    public function index($productId)
    {
        // Get all reviews and replies for this product
        $allReviews = Review::with(['user'])
            ->where('product_id', $productId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Separate main reviews from replies
        $mainReviews = $allReviews->where('is_reply', false)->values();
        $replies = $allReviews->where('is_reply', true)->groupBy('parent_id');

        // Build the nested structure
        $reviews = $mainReviews->map(function ($review) use ($replies) {
            $review->replies = $this->buildNestedReplies($review->id, $replies);
            return $review;
        });

        // Calculate average rating
        $averageRating = Review::where('product_id', $productId)
            ->where('is_reply', false)
            ->avg('rating');

        $totalReviews = Review::where('product_id', $productId)
            ->where('is_reply', false)
            ->count();

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews
        ]);
    }

    /**
     * Build nested replies structure recursively
     */
    private function buildNestedReplies($parentId, $replies)
    {
        if (!isset($replies[$parentId])) {
            return collect();
        }

        return $replies[$parentId]->map(function ($reply) use ($replies) {
            $reply->replies = $this->buildNestedReplies($reply->id, $replies);
            return $reply;
        });
    }

    /**
     * Store a newly created review
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $productId
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_reply' => false,
            ]
        );

        // Load the user relationship for the response
        $review->load('user');

        return response()->json($review, 201);
    }

    /**
     * Store a reply to a review
     */
    public function storeReply(Request $request, $productId, $reviewId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $parentReview = Review::findOrFail($reviewId);

        $reply = Review::create([
            'user_id' => $request->user()->id,
            'product_id' => $productId,
            'parent_id' => $reviewId, // This can be either a main review or another reply
            'comment' => $request->comment,
            'is_reply' => true,
            'rating' => 0, // Replies don't have ratings
        ]);

        // Update parent review reply count
        $parentReview->increment('reply_count');

        // Load the user relationship for the response
        $reply->load('user');

        return response()->json($reply, 201);
    }

    /**
     * Update the specified review
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        $user = $request->user();

        // Check permissions - user can edit their own reviews, admin can edit any, product owner can edit any
        $canEdit = false;

        if ($review->user_id === $user->id) {
            $canEdit = true;
        } elseif ($user->role === 'admin') {
            $canEdit = true;
        } elseif ($review->product->seller_id === $user->id) {
            $canEdit = true;
        }

        if (!$canEdit) {
            return response()->json(['message' => 'You are not authorized to edit this review'], 403);
        }

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $review->update($request->all());
        $review->load('user');

        return response()->json($review);
    }

    /**
     * Remove the specified review
     */
    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Check permissions
        $user = $request->user();
        $canDelete = false;

        // User can delete their own reviews/replies
        if ($review->user_id === $user->id) {
            $canDelete = true;
        }
        // Admin can delete any review/reply
        elseif ($user->role === 'admin') {
            $canDelete = true;
        }
        // Product owner can delete any review/reply for their product
        elseif ($review->product->seller_id === $user->id) {
            $canDelete = true;
        }

        if (!$canDelete) {
            return response()->json(['message' => 'You are not authorized to delete this review'], 403);
        }

        // If this is a main review, delete all its replies
        if (!$review->is_reply) {
            $this->deleteRepliesRecursively($review->id);
        } else {
            // If this is a reply, update parent reply count
            if ($review->parent_id) {
                $parentReview = Review::find($review->parent_id);
                if ($parentReview) {
                    $parentReview->decrement('reply_count');
                }
            }
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    /**
     * Delete all replies recursively
     */
    private function deleteRepliesRecursively($parentId)
    {
        $replies = Review::where('parent_id', $parentId)->get();

        foreach ($replies as $reply) {
            $this->deleteRepliesRecursively($reply->id);
            $reply->delete();
        }
    }
}
