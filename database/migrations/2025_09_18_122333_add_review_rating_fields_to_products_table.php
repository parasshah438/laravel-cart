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
            // ✅ REVIEW RATING CACHE FIELDS
            $table->decimal('average_rating', 3, 1)->nullable()->after('price'); // 0.0 to 5.0
            $table->integer('review_count')->default(0)->after('average_rating'); // Total approved reviews
            
            // ✅ INDEXES FOR PERFORMANCE
            $table->index('average_rating'); // Sort by rating
            $table->index('review_count'); // Sort by review count
            $table->index(['average_rating', 'review_count']); // Combined sorting
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['average_rating']);
            $table->dropIndex(['review_count']);
            $table->dropIndex(['average_rating', 'review_count']);
            $table->dropColumn(['average_rating', 'review_count']);
        });
    }
};
