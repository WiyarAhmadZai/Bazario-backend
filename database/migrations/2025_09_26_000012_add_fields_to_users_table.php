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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('image')->nullable()->after('bio');
            $table->date('date_of_birth')->nullable()->after('image');
            $table->text('address')->nullable()->after('date_of_birth');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->string('gender')->nullable()->after('country');
            $table->string('profession')->nullable()->after('gender');
            $table->json('social_links')->nullable()->after('profession');
            $table->boolean('verified')->default(false)->after('social_links');
            $table->decimal('wallet_balance', 10, 2)->default(0.00)->after('verified');
            $table->json('bank_account_info')->nullable()->after('wallet_balance');
            $table->boolean('is_active')->default(true)->after('bank_account_info');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'bio',
                'image',
                'date_of_birth',
                'address',
                'city',
                'country',
                'gender',
                'profession',
                'social_links',
                'verified',
                'wallet_balance',
                'bank_account_info',
                'is_active',
                'last_login_at'
            ]);
        });
    }
};
