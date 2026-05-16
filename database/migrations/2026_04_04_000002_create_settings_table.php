<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->string('label', 100);
            $table->string('type', 20)->default('text'); // text|email|number|boolean|textarea|select
            $table->json('options')->nullable();          // for select type
            $table->timestamps();
        });

        // Seed default settings
        $now = now();
        DB::table('settings')->insert([
            // ── General ──────────────────────────────────────
            ['key' => 'app_name',      'value' => config('app.name'), 'group' => 'general', 'label' => 'Application Name',  'type' => 'text',    'options' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'app_tagline',   'value' => '',                 'group' => 'general', 'label' => 'App Tagline',        'type' => 'text',    'options' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'contact_email', 'value' => '',                 'group' => 'general', 'label' => 'Contact Email',      'type' => 'email',   'options' => null, 'created_at' => $now, 'updated_at' => $now],

            // ── System ────────────────────────────────────────
            ['key' => 'maintenance_mode',  'value' => '0',  'group' => 'system', 'label' => 'Maintenance Mode',         'type' => 'boolean', 'options' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'user_registration', 'value' => '1',  'group' => 'system', 'label' => 'Allow User Registration',  'type' => 'boolean', 'options' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'items_per_page',    'value' => '15', 'group' => 'system', 'label' => 'Default Items Per Page',   'type' => 'number',  'options' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'session_timeout',   'value' => '120','group' => 'system', 'label' => 'Session Timeout (minutes)','type' => 'number',  'options' => null, 'created_at' => $now, 'updated_at' => $now],

            // ── Mail ──────────────────────────────────────────
            ['key' => 'mail_from_name',      'value' => config('app.name'), 'group' => 'mail', 'label' => 'Mail From Name',         'type' => 'text',    'options' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mail_reply_to',       'value' => '',                 'group' => 'mail', 'label' => 'Reply-To Email',          'type' => 'email',   'options' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'email_notifications', 'value' => '1',               'group' => 'mail', 'label' => 'Enable Email Notifications','type' => 'boolean', 'options' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
