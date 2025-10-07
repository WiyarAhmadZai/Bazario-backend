<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\CommissionSetting;
use App\Models\PaymentTransaction;
use App\Http\Requests\RejectProductRequest;
use App\Http\Requests\RejectBankTransferRequest;
use App\Http\Requests\UpdateCommissionSettingsRequest;
use App\Http\Requests\ManageUserRequest;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Admin dashboard data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard()
    {
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::count();
        $totalRevenue = PaymentTransaction::where('status', 'completed')->sum('amount');

        // Get recent orders
        $recentOrders = Order::with('user')->orderBy('created_at', 'desc')->limit(5)->get();

        // Get pending orders
        $pendingOrders = Order::where('status', 'pending')->count();

        // Get products by status
        $productsByStatus = Product::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        return response()->json([
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_users' => $totalUsers,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'recent_orders' => $recentOrders,
            'products_by_status' => $productsByStatus,
        ]);
    }

    /**
     * Get pending products for approval
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingProducts()
    {
        $products = Product::where('status', 'pending')
            ->with('seller', 'category')
            ->paginate(20);

        return response()->json($products);
    }

    /**
     * Approve a product
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveProduct(Request $request, Product $product)
    {
        $product->update([
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Product approved successfully',
            'product' => $product,
        ]);
    }

    /**
     * Reject a product
     *
     * @param RejectProductRequest $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectProduct(RejectProductRequest $request, Product $product)
    {
        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Product rejected successfully',
            'product' => $product,
        ]);
    }

    /**
     * Get bank transfer receipts waiting for verification
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingBankTransfers()
    {
        $transactions = PaymentTransaction::where('status', 'waiting_verification')
            ->with('order', 'order.user')
            ->paginate(20);

        return response()->json($transactions);
    }

    /**
     * Approve a bank transfer
     *
     * @param Request $request
     * @param PaymentTransaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveBankTransfer(Request $request, PaymentTransaction $transaction)
    {
        // Update transaction status
        $transaction->update([
            'status' => 'completed',
        ]);

        // Update order status
        $order = $transaction->order;
        $order->update([
            'status' => 'paid',
            'payment_status' => 'completed',
        ]);

        // Credit wallets
        // This would typically be handled by a service class
        // For now, we'll do it directly
        $admin = User::role('admin')->first();
        if ($admin) {
            $admin->increment('wallet_balance', $transaction->admin_share);
        }

        $seller = $order->seller;
        if ($seller) {
            $seller->increment('wallet_balance', $transaction->seller_share);
        }

        return response()->json([
            'message' => 'Bank transfer approved successfully',
            'transaction' => $transaction,
        ]);
    }

    /**
     * Reject a bank transfer
     *
     * @param RejectBankTransferRequest $request
     * @param PaymentTransaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectBankTransfer(RejectBankTransferRequest $request, PaymentTransaction $transaction)
    {
        // Update transaction status
        $transaction->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        // Update order status
        $order = $transaction->order;
        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'Bank transfer rejected successfully',
            'transaction' => $transaction,
        ]);
    }

    /**
     * Get commission settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommissionSettings()
    {
        $settings = CommissionSetting::first();

        return response()->json($settings);
    }

    /**
     * Update commission settings
     *
     * @param UpdateCommissionSettingsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCommissionSettings(UpdateCommissionSettingsRequest $request)
    {
        $settings = CommissionSetting::first();
        if (!$settings) {
            $settings = new CommissionSetting();
        }

        $settings->percentage = $request->percentage;
        $settings->updated_by = $request->user() ? $request->user()->id : null;
        $settings->save();

        return response()->json([
            'message' => 'Commission settings updated successfully',
            'settings' => $settings,
        ]);
    }

    /**
     * Get sales reports
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesReport(Request $request)
    {
        $request->validate([
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = PaymentTransaction::where('status', 'completed');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        $totalSales = $transactions->sum('amount');
        $totalCommission = $transactions->sum('admin_share');
        $totalToSellers = $transactions->sum('seller_share');

        return response()->json([
            'total_sales' => $totalSales,
            'total_commission' => $totalCommission,
            'total_to_sellers' => $totalToSellers,
            'transaction_count' => $transactions->count(),
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get top sellers report
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopSellers()
    {
        $topSellers = User::role('seller')
            ->withCount('products')
            ->withSum('products as total_sales', 'price')
            ->orderBy('total_sales_sum', 'desc')
            ->limit(10)
            ->get();

        return response()->json($topSellers);
    }

    /**
     * Manage users (block/unblock, change roles)
     *
     * @param ManageUserRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function manageUser(ManageUserRequest $request, User $user)
    {
        switch ($request->action) {
            case 'block':
                $user->update(['blocked' => true]);
                break;

            case 'unblock':
                $user->update(['blocked' => false]);
                break;

            case 'assign_role':
                $user->assignRole($request->role);
                break;

            case 'remove_role':
                $user->removeRole($request->role);
                break;
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Get all users with pagination
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsers()
    {
        $users = User::with('roles')
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Get all products for admin
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Request $request)
    {
        $query = Product::with('seller', 'category');

        // Filter by status if provided
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by search term
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        $products = $query->paginate(20);

        return response()->json($products);
    }

    /**
     * Create a product (admin)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->stock = $request->stock;
        $product->seller_id = $request->user() ? $request->user()->id : null; // Admin creating product
        $product->status = 'approved'; // Auto-approve for admin

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $product->image = 'storage/products/' . $imageName;
        }

        $product->save();

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('seller', 'category'),
        ], 201);
    }

    /**
     * Update a product (admin)
     *
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProduct(Request $request, $id)
    {
        // Find the product manually since model binding might not be working
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
                'error' => 'Product with ID ' . $id . ' not found'
            ], 404);
        }

        // Debug logging
        \Log::info('AdminController::updateProduct called', [
            'product_id' => $product->id,
            'product_title' => $product->title,
            'product_status' => $product->status,
            'request_data' => $request->all(),
            'user_id' => $request->user() ? $request->user()->id : 'NOT_AUTHENTICATED'
        ]);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255', // Support both title and name
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'stock' => 'sometimes|integer|min:0',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'images' => 'sometimes|array',
            'status' => 'sometimes|in:pending,approved,rejected',
        ]);

        $updateData = $request->only([
            'title',
            'name',
            'description',
            'price',
            'category_id',
            'stock',
            'status'
        ]);

        // Handle both title and name fields
        if ($request->has('title') && !$request->has('name')) {
            $updateData['title'] = $request->title;
        } elseif ($request->has('name') && !$request->has('title')) {
            $updateData['title'] = $request->name;
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::exists(str_replace('storage/', 'public/', $product->image))) {
                Storage::delete(str_replace('storage/', 'public/', $product->image));
            }

            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/products', $imageName);
            $updateData['image'] = 'storage/products/' . $imageName;
        }

        // Handle images array if provided
        if ($request->has('images') && is_array($request->images)) {
            $updateData['images'] = json_encode($request->images);
        }

        $product->update($updateData);

        // Reload the product to get the updated data
        $product->refresh();

        // Load the product with relationships
        $productWithRelations = $product->load('seller', 'category');

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $productWithRelations,
            'status' => $product->status, // Explicitly return the status
            'id' => $product->id,
            'title' => $product->title,
        ]);
    }

    /**
     * Delete a product (admin)
     *
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteProduct(Product $product)
    {
        // Delete image if exists
        if ($product->image && Storage::exists(str_replace('storage/', 'public/', $product->image))) {
            Storage::delete(str_replace('storage/', 'public/', $product->image));
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
