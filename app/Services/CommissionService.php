<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\CommissionSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate commission and seller share for an order
     *
     * @param Order $order
     * @return array
     */
    public function calculateCommission(Order $order): array
    {
        // Get commission setting
        $commissionSetting = CommissionSetting::first();
        $commissionPct = $commissionSetting ? $commissionSetting->percentage : 2.00;

        // Calculate amounts
        $totalAmount = $order->total_amount;
        $adminShare = round($totalAmount * $commissionPct / 100, 2);
        $sellerShare = $totalAmount - $adminShare;

        return [
            'total_amount' => $totalAmount,
            'commission_percentage' => $commissionPct,
            'admin_share' => $adminShare,
            'seller_share' => $sellerShare,
        ];
    }

    /**
     * Create payment transaction record
     *
     * @param Order $order
     * @param string $paymentMethod
     * @return PaymentTransaction
     */
    public function createPaymentTransaction(Order $order, string $paymentMethod): PaymentTransaction
    {
        $commissionData = $this->calculateCommission($order);

        return PaymentTransaction::create([
            'order_id' => $order->id,
            'amount' => $commissionData['total_amount'],
            'admin_share' => $commissionData['admin_share'],
            'seller_share' => $commissionData['seller_share'],
            'payment_method' => $paymentMethod,
            'status' => 'pending',
        ]);
    }

    /**
     * Credit wallets after successful payment
     *
     * @param Order $order
     * @param PaymentTransaction $transaction
     * @return void
     */
    public function creditWallets(Order $order, PaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($order, $transaction) {
            // Credit admin commission
            $admin = User::role('admin')->first();
            if ($admin) {
                $admin->increment('wallet_balance', $transaction->admin_share);
            }

            // Credit seller amount
            $seller = $order->seller;
            if ($seller) {
                $seller->increment('wallet_balance', $transaction->seller_share);
            }
        });
    }

    /**
     * Update order with commission data
     *
     * @param Order $order
     * @param PaymentTransaction $transaction
     * @return void
     */
    public function updateOrderWithCommissionData(Order $order, PaymentTransaction $transaction): void
    {
        $order->update([
            'commission_amount' => $transaction->admin_share,
            'seller_amount' => $transaction->seller_share,
            'payment_method' => $transaction->payment_method,
            'payment_status' => $transaction->status,
        ]);
    }
}
