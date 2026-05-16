<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_login_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('admins')
                  ->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status', 20)->default('success'); // success | failed
            $table->string('failed_email')->nullable();        // store email when admin not found
            $table->timestamp('logged_out_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_histories');
    }
};
