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
        // Drop the existing unique constraint
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
        });

        // Add a new unique constraint that only applies to main reviews (not replies)
        // This will be handled at the application level since SQLite doesn't support conditional unique constraints
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original unique constraint
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
        });
    }
};
