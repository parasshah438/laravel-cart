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
        Schema::create('wishlist_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            
            $table->index(['token', 'expires_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_shares');
    }
};
