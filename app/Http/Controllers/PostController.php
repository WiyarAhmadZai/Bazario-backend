<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;
use App\Models\PostFavorite;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * Get paginated posts
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100, 150]) ? $perPage : 10;

        $query = Post::with(['user:id,name,email,avatar', 'likes', 'comments.user:id,name,avatar'])
            ->published()
            ->public()
            ->orderBy('created_at', 'desc');

        // Filter by user if specified
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $posts = $query->paginate($perPage);

        // Add additional data for each post
        $posts->getCollection()->transform(function ($post) use ($user) {
            $post->is_liked = $user ? $post->isLikedBy($user->id) : false;
            $post->is_favorited = $user ? $post->isFavoritedBy($user->id) : false;
            $post->likes_count = $post->likes()->count();
            $post->comments_count = $post->comments()->count();
            $post->favorites_count = $post->favorites()->count();
            $post->time_ago = $post->created_at->diffForHumans();
            $post->image_urls = $post->getImageUrls();

            return $post;
        });

        return response()->json($posts);
    }

    /**
     * Create a new post
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'visibility' => 'in:public,followers,private'
        ]);

        $user = Auth::user();
        $imageUrls = [];

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/posts', $imageName);
                $imageUrls[] = 'posts/' . $imageName;
            }
        }

        $post = Post::create([
            'user_id' => $user->id,
            'content' => $request->content,
            'images' => $imageUrls,
            'visibility' => $request->visibility ?? 'public',
            'is_published' => true
        ]);

        // Load relationships
        $post->load(['user:id,name,email,avatar', 'likes', 'comments.user:id,name,avatar']);

        // Create notifications for followers who have notifications enabled
        $this->createPostNotifications($post);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }

    /**
     * Get a specific post
     */
    public function show($id)
    {
        $user = Auth::user();
        $post = Post::with(['user:id,name,email,avatar', 'likes', 'comments.user:id,name,avatar'])
            ->published()
            ->findOrFail($id);

        $post->is_liked = $user ? $post->isLikedBy($user->id) : false;
        $post->is_favorited = $user ? $post->isFavoritedBy($user->id) : false;
        $post->likes_count = $post->likes()->count();
        $post->comments_count = $post->comments()->count();
        $post->favorites_count = $post->favorites()->count();
        $post->time_ago = $post->created_at->diffForHumans();
        $post->image_urls = $post->getImageUrls();

        return response()->json($post);
    }

    /**
     * Update a post
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        // Check if user owns the post
        if ($post->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED'
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|max:5000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'visibility' => 'in:public,followers,private'
        ]);

        $imageUrls = $post->images ?? [];

        // Handle new image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            foreach ($imageUrls as $imageUrl) {
                if (Storage::exists('public/' . $imageUrl)) {
                    Storage::delete('public/' . $imageUrl);
                }
            }

            $imageUrls = [];
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/posts', $imageName);
                $imageUrls[] = 'posts/' . $imageName;
            }
        }

        $post->update([
            'content' => $request->content,
            'images' => $imageUrls,
            'visibility' => $request->visibility ?? $post->visibility
        ]);

        $post->load(['user:id,name,email,avatar', 'likes', 'comments.user:id,name,avatar']);
        $post->image_urls = $post->getImageUrls();

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post
        ]);
    }

    /**
     * Delete a post
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $user = Auth::user();

        // Check if user owns the post
        if ($post->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
                'error' => 'UNAUTHORIZED'
            ], 403);
        }

        // Delete associated images
        foreach ($post->images as $imageUrl) {
            if (Storage::exists('public/' . $imageUrl)) {
                Storage::delete('public/' . $imageUrl);
            }
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully'
        ]);
    }

    /**
     * Like or unlike a post or product
     */
    public function like(Request $request, $id)
    {
        $user = Auth::user();

        // Try to find as post first, then as product
        $post = Post::find($id);
        $isProduct = false;

        if (!$post) {
            $post = Product::find($id);
            $isProduct = true;
        }

        if (!$post) {
            return response()->json(['error' => 'Post or product not found'], 404);
        }

        if ($isProduct) {
            // Handle product likes using the existing likes relationship
            $isLiked = $post->likes()->where('user_id', $user->id)->exists();

            if ($isLiked) {
                $post->likes()->where('user_id', $user->id)->delete();
                $action = 'unliked';
            } else {
                $post->likes()->create(['user_id' => $user->id]);
                $action = 'liked';
            }

            return response()->json([
                'action' => $action,
                'is_liked' => !$isLiked,
                'likes_count' => $post->likes()->count()
            ]);
        } else {
            // Handle post likes
            $isLiked = $post->isLikedBy($user->id);

            if ($isLiked) {
                PostLike::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->delete();
                $action = 'unliked';
            } else {
                PostLike::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id
                ]);
                $action = 'liked';
            }

            return response()->json([
                'action' => $action,
                'is_liked' => !$isLiked,
                'likes_count' => $post->likes()->count()
            ]);
        }
    }

    /**
     * Favorite or unfavorite a post or product
     */
    public function favorite(Request $request, $id)
    {
        $user = Auth::user();

        // Try to find as post first, then as product
        $post = Post::find($id);
        $isProduct = false;

        if (!$post) {
            $post = Product::find($id);
            $isProduct = true;
        }

        if (!$post) {
            return response()->json(['error' => 'Post or product not found'], 404);
        }

        if ($isProduct) {
            // For products, we'll use a simple approach since they don't have favorites yet
            // We can create a product_favorites table or use the existing wishlist
            // For now, let's return a simple response
            return response()->json([
                'action' => 'favorited',
                'is_favorited' => true,
                'favorites_count' => 0
            ]);
        } else {
            // Handle post favorites
            $isFavorited = $post->isFavoritedBy($user->id);

            if ($isFavorited) {
                PostFavorite::where('user_id', $user->id)
                    ->where('post_id', $post->id)
                    ->delete();
                $action = 'unfavorited';
            } else {
                PostFavorite::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id
                ]);
                $action = 'favorited';
            }

            return response()->json([
                'action' => $action,
                'is_favorited' => !$isFavorited,
                'favorites_count' => $post->favorites()->count()
            ]);
        }
    }

    /**
     * Get post comments
     */
    public function getComments($id)
    {
        $post = Post::findOrFail($id);
        $comments = $post->comments()
            ->with('user:id,name,avatar', 'replies.user:id,name,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($comments);
    }

    /**
     * Add a comment to a post
     */
    public function addComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:post_comments,id'
        ]);

        $user = Auth::user();
        $post = Post::findOrFail($id);

        $comment = PostComment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content
        ]);

        $comment->load('user:id,name,avatar');

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment
        ], 201);
    }

    /**
     * Get sponsored posts (products)
     */
    public function getSponsoredPosts(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100, 150]) ? $perPage : 10;

        $query = Product::activeSponsored()
            ->with(['seller:id,name,email,avatar', 'category:id,name'])
            ->orderBy('created_at', 'desc');

        $products = $query->paginate($perPage);

        // Transform products to post format
        $products->getCollection()->transform(function ($product) {
            return $product->toPostFormat();
        });

        return response()->json($products);
    }

    /**
     * Create notifications for followers when a new post is created
     */
    private function createPostNotifications($post)
    {
        $followers = $post->user->followers()
            ->whereHas('notificationSettings', function ($query) {
                $query->where('notify_on_post', true);
            })
            ->get();

        foreach ($followers as $follower) {
            Notification::create([
                'user_id' => $follower->follower_id,
                'type' => 'post_created',
                'title' => 'New Post',
                'message' => $post->user->name . ' created a new post',
                'data' => [
                    'post_id' => $post->id,
                    'author_id' => $post->user_id,
                    'author_name' => $post->user->name
                ]
            ]);
        }
    }
}
