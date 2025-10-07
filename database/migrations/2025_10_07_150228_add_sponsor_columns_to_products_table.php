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
            $table->boolean('sponsor')->default(false)->after('status');
            $table->timestamp('sponsor_start_time')->nullable()->after('sponsor');
            $table->timestamp('sponsor_end_time')->nullable()->after('sponsor_start_time');

            $table->index(['sponsor', 'sponsor_end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['sponsor', 'sponsor_end_time']);
            $table->dropColumn(['sponsor', 'sponsor_start_time', 'sponsor_end_time']);
        });
    }
};
