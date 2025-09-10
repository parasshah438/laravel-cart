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
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('title')->after('code'); // Professional coupon title
            $table->text('description')->nullable()->after('title'); // Coupon description
            $table->string('terms')->nullable()->after('description'); // Terms & conditions
            $table->decimal('max_discount', 8, 2)->nullable()->after('discount'); // Max discount for percentage coupons
            $table->integer('usage_limit')->nullable()->after('max_discount'); // Total usage limit
            $table->integer('used_count')->default(0)->after('usage_limit'); // How many times used
            $table->enum('category', ['general', 'first_order', 'festival', 'clearance', 'loyalty'])->default('general')->after('used_count');
            $table->string('banner_color', 7)->default('#007bff')->after('category'); // Hex color for coupon card
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'description', 
                'terms',
                'max_discount',
                'usage_limit',
                'used_count',
                'category',
                'banner_color'
            ]);
        });
    }
};
