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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // order_update, review_request, promotion, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional notification data
            $table->string('icon')->default('bell'); // FontAwesome icon name
            $table->string('color')->default('primary'); // Bootstrap color theme
            $table->string('action_url')->nullable(); // URL to redirect when clicked
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_important')->default(false);
            $table->string('channel')->default('web'); // web, email, push, sms
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
