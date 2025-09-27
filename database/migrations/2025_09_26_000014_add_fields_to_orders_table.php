<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade')->after('user_id');
            $table->decimal('total_amount', 10, 2)->after('seller_id');
            $table->decimal('commission_amount', 10, 2)->after('total_amount');
            $table->decimal('seller_amount', 10, 2)->after('commission_amount');
            $table->enum('payment_method', ['hesab_pay', 'momo', 'bank_transfer', 'cod'])->after('seller_amount');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->json('shipping_info')->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['seller_id', 'total_amount', 'commission_amount', 'seller_amount', 'payment_method', 'payment_status', 'shipping_info']);
        });
    }
};
