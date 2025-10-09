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
            // Add indexes for better query performance
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['status', 'price'], 'idx_status_price');
            $table->index(['status', 'title'], 'idx_status_title');
            $table->index(['category_id', 'status'], 'idx_category_status');
            $table->index(['seller_id', 'status'], 'idx_seller_status');
            $table->index(['price'], 'idx_price');
            $table->index(['created_at'], 'idx_created_at');
            $table->index(['title'], 'idx_title');

            // Note: Full-text search index not supported in SQLite
            // For MySQL: $table->fullText(['title', 'description'], 'idx_fulltext_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_status_created');
            $table->dropIndex('idx_status_price');
            $table->dropIndex('idx_status_title');
            $table->dropIndex('idx_category_status');
            $table->dropIndex('idx_seller_status');
            $table->dropIndex('idx_price');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_title');
            // Note: Full-text search index not supported in SQLite
        });
    }
};
