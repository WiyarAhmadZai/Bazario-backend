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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade')->after('id');
            $table->string('slug')->unique()->after('title');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('category_id');
            $table->boolean('is_featured')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['seller_id', 'slug', 'status', 'is_featured']);
        });
    }
};
