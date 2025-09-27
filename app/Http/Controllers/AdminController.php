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
        $settings->updated_by = auth()->id();
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
}
