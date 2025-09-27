<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\CommissionSetting;
use App\Models\User;
use App\Services\CommissionService;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Process payment for an order
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request, Order $order)
    {
        // Validate payment method
        $request->validate([
            'payment_method' => 'required|in:hesab_pay,momo,bank_transfer,cod',
        ]);

        // Create payment transaction using the service
        $paymentTransaction = $this->commissionService->createPaymentTransaction($order, $request->payment_method);

        // Update order with commission data
        $this->commissionService->updateOrderWithCommissionData($order, $paymentTransaction);

        // Return payment instructions based on method
        $paymentInstructions = $this->getPaymentInstructions($request->payment_method, $order, $paymentTransaction);

        return response()->json([
            'message' => 'Payment processing initiated',
            'order' => $order,
            'payment_transaction' => $paymentTransaction,
            'payment_instructions' => $paymentInstructions,
        ]);
    }

    /**
     * Handle payment webhook/callback
     *
     * @param Request $request
     * @param string $gateway
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request, string $gateway)
    {
        // Validate webhook data
        // This would typically involve verifying signatures, etc.

        // For demonstration, we'll just simulate a successful payment
        $orderId = $request->input('order_id');
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Find the payment transaction
        $paymentTransaction = PaymentTransaction::where('order_id', $order->id)->first();

        if (!$paymentTransaction) {
            return response()->json(['error' => 'Payment transaction not found'], 404);
        }

        // Update payment transaction status
        $paymentTransaction->update([
            'status' => 'completed',
            'reference' => $request->input('reference', 'WEBHOOK-' . time()),
        ]);

        // Update order status
        $order->update([
            'status' => 'paid',
            'payment_status' => 'completed',
        ]);

        // Credit seller wallet and admin commission using the service
        $this->commissionService->creditWallets($order, $paymentTransaction);

        // Send notifications
        $this->sendPaymentNotifications($order, $paymentTransaction);

        return response()->json([
            'message' => 'Payment confirmed successfully',
            'order' => $order,
            'payment_transaction' => $paymentTransaction,
        ]);
    }

    /**
     * Confirm bank transfer payment
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmBankTransfer(Request $request, Order $order)
    {
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Store receipt file
        $receiptPath = $request->file('receipt')->store('bank_transfers', 'public');

        // Update payment transaction
        $paymentTransaction = PaymentTransaction::where('order_id', $order->id)->first();

        if ($paymentTransaction) {
            $paymentTransaction->update([
                'status' => 'waiting_verification',
                'reference' => $receiptPath,
            ]);
        }

        // Update order status
        $order->update([
            'payment_status' => 'waiting_verification',
        ]);

        return response()->json([
            'message' => 'Bank transfer receipt uploaded successfully. Waiting for admin verification.',
            'order' => $order,
            'receipt_path' => $receiptPath,
        ]);
    }

    /**
     * Get payment instructions based on payment method
     *
     * @param string $method
     * @param Order $order
     * @param PaymentTransaction $transaction
     * @return array
     */
    private function getPaymentInstructions(string $method, Order $order, PaymentTransaction $transaction): array
    {
        switch ($method) {
            case 'hesab_pay':
                return [
                    'instructions' => 'You will be redirected to Hesab Pay to complete your payment.',
                    'redirect_url' => 'https://hesabpay.example.com/pay/' . $transaction->id,
                    'test_mode' => true,
                ];

            case 'momo':
                return [
                    'instructions' => 'You will be redirected to MoMo to complete your payment.',
                    'redirect_url' => 'https://momo.example.com/pay/' . $transaction->id,
                    'test_mode' => true,
                ];

            case 'bank_transfer':
                return [
                    'instructions' => 'Please transfer the amount to the following bank account:',
                    'bank_details' => [
                        'account_name' => 'Luxury Marketplace',
                        'account_number' => '1234567890',
                        'bank_name' => 'Sample Bank',
                        'branch' => 'Main Branch',
                    ],
                    'reference' => 'ORDER-' . $order->id,
                ];

            case 'cod':
                return [
                    'instructions' => 'You have selected Cash on Delivery. Please pay when you receive your order.',
                ];

            default:
                return [
                    'instructions' => 'Payment method not supported.',
                ];
        }
    }

    /**
     * Credit wallets after successful payment
     *
     * @param Order $order
     * @param PaymentTransaction $transaction
     * @return void
     */
    private function creditWallets(Order $order, PaymentTransaction $transaction): void
    {
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
    }

    /**
     * Send payment notifications
     *
     * @param Order $order
     * @param PaymentTransaction $transaction
     * @return void
     */
    private function sendPaymentNotifications(Order $order, PaymentTransaction $transaction): void
    {
        // In a real application, you would send emails or push notifications here
        // For now, we'll just log the notifications

        \Log::info('Payment confirmed for order #' . $order->id);
        \Log::info('Admin commission: ' . $transaction->admin_share);
        \Log::info('Seller amount: ' . $transaction->seller_share);
    }
}
