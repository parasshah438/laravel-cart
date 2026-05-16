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
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);           // new_user | low_stock | new_order | custom
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();      // extra payload (link, subject id, etc.)
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['read_at', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
