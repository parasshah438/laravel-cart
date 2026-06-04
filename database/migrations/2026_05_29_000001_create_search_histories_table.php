<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category_slug', 120);
            $table->string('query', 255);
            $table->char('query_hash', 32);   // MD5 of query — short, fixed-width
            $table->timestamp('searched_at')->useCurrent();

            // Unique on hash keeps key length tiny (8 + 120*4 max, but hash is fixed 32 bytes)
            $table->unique(['user_id', 'category_slug', 'query_hash'], 'ush_unique');
            $table->index(['user_id', 'category_slug', 'searched_at'], 'ush_listing');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
