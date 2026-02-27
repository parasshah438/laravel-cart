<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'item_status')) {
                $table->enum('item_status', [
                    'pending',
                    'confirmed',
                    'shipped',
                    'delivered',
                    'cancelled',
                    'return_requested',
                    'returned',
                ])->default('pending')->after('total');
            }

            if (!Schema::hasColumn('order_items', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('item_status');
            }

            if (!Schema::hasColumn('order_items', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['item_status', 'cancellation_reason', 'cancelled_at']);
        });
    }
};
