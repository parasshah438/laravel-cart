<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->id();

            // Admin who performed the impersonation
            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('admins')
                  ->nullOnDelete();

            // User being impersonated
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Stored for display even after deletion
            $table->string('admin_name', 150)->nullable();
            $table->string('admin_email')->nullable();
            $table->string('user_name', 150)->nullable();
            $table->string('user_email')->nullable();

            // Context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('reason', 500)->nullable(); // optional reason entered before impersonating

            // Timestamps
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index(['admin_id', 'started_at']);
            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_logs');
    }
};
