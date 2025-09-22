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
        Schema::create('wishlist_share_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_share_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamp('added_at');
            $table->timestamps();
            
            $table->unique(['wishlist_share_id', 'product_id']);
            $table->index(['wishlist_share_id', 'added_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_share_items');
    }
};
