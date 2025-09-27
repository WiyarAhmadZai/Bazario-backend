<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CommissionService;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\CommissionSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $commissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = new CommissionService();
    }

    /** @test */
    public function it_calculates_commission_correctly()
    {
        // Create commission setting
        CommissionSetting::firstOrCreate([], [
            'percentage' => 2.00,
        ]);

        // Create order
        $order = Order::firstOrCreate([
            'user_id' => 1,
            'seller_id' => 2,
            'status' => 'pending',
        ], [
            'total_amount' => 100.00,
        ]);

        // Calculate commission
        $result = $this->commissionService->calculateCommission($order);

        // Assertions
        $this->assertEquals(100.00, $result['total_amount']);
        $this->assertEquals(2.00, $result['commission_percentage']);
        $this->assertEquals(2.00, $result['admin_share']);
        $this->assertEquals(98.00, $result['seller_share']);
    }

    /** @test */
    public function it_creates_payment_transaction()
    {
        // Create commission setting
        CommissionSetting::firstOrCreate([], [
            'percentage' => 2.00,
        ]);

        // Create users
        $buyer = User::firstOrCreate([
            'email' => 'buyer@example.com',
        ], [
            'name' => 'Buyer',
            'password' => bcrypt('password'),
        ]);

        $seller = User::firstOrCreate([
            'email' => 'seller@example.com',
        ], [
            'name' => 'Seller',
            'password' => bcrypt('password'),
        ]);

        // Create order
        $order = Order::firstOrCreate([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => 'pending',
        ], [
            'total_amount' => 100.00,
        ]);

        // Create payment transaction
        $paymentTransaction = $this->commissionService->createPaymentTransaction($order, 'hesab_pay');

        // Assertions
        $this->assertInstanceOf(PaymentTransaction::class, $paymentTransaction);
        $this->assertEquals($order->id, $paymentTransaction->order_id);
        $this->assertEquals(100.00, $paymentTransaction->amount);
        $this->assertEquals(2.00, $paymentTransaction->admin_share);
        $this->assertEquals(98.00, $paymentTransaction->seller_share);
        $this->assertEquals('hesab_pay', $paymentTransaction->payment_method);
        $this->assertEquals('pending', $paymentTransaction->status);
    }

    /** @test */
    public function it_handles_default_commission_when_not_set()
    {
        // Ensure no commission setting exists
        CommissionSetting::truncate();

        // Create order
        $order = Order::firstOrCreate([
            'user_id' => 1,
            'seller_id' => 2,
            'status' => 'pending',
        ], [
            'total_amount' => 100.00,
        ]);

        // Calculate commission
        $result = $this->commissionService->calculateCommission($order);

        // Assertions - should use default 2% commission
        $this->assertEquals(2.00, $result['commission_percentage']);
        $this->assertEquals(2.00, $result['admin_share']);
        $this->assertEquals(98.00, $result['seller_share']);
    }
}