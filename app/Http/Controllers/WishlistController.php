<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    /**
     * Display a listing of the user's wishlist items
     */
    public function index(Request $request)
    {
        $wishlistItems = Wishlist::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($wishlistItems);
    }

    /**
     * Store a newly created wishlist item
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlistItem = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id
        ]);

        return response()->json($wishlistItem, 201);
    }

    /**
     * Remove the specified wishlist item
     */
    public function destroy(Request $request, $id)
    {
        $wishlistItem = Wishlist::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $wishlistItem->delete();

        return response()->json(['message' => 'Item removed from wishlist']);
    }
}
