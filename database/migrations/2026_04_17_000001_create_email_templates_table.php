<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // machine key: welcome, password-reset …
            $table->string('name');                   // human label
            $table->string('subject');                // email subject (supports {{vars}})
            $table->longText('body');                 // HTML body  (supports {{vars}})
            $table->json('variables')->nullable();    // list of available {{var}} names
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Seed default templates ───────────────────────────────────────────
        $now = now();

        DB::table('email_templates')->insert([

            // ── Welcome ─────────────────────────────────────────────────────
            [
                'key'       => 'welcome',
                'name'      => 'Welcome Email',
                'subject'   => 'Welcome to {{app_name}}, {{user_name}}!',
                'body'      => self::welcomeBody(),
                'variables' => json_encode(['app_name', 'user_name', 'login_url']),
                'is_active' => true,
                'created_at'=> $now,
                'updated_at'=> $now,
            ],

            // ── Password Reset ───────────────────────────────────────────────
            [
                'key'       => 'password-reset',
                'name'      => 'Password Reset',
                'subject'   => 'Reset your {{app_name}} password',
                'body'      => self::passwordResetBody(),
                'variables' => json_encode(['app_name', 'user_name', 'reset_url', 'expiry_minutes']),
                'is_active' => true,
                'created_at'=> $now,
                'updated_at'=> $now,
            ],

            // ── Magic Link ───────────────────────────────────────────────────
            [
                'key'       => 'magic-link',
                'name'      => 'Magic Sign-In Link',
                'subject'   => 'Your Magic Sign-In Link — {{app_name}}',
                'body'      => self::magicLinkBody(),
                'variables' => json_encode(['app_name', 'user_name', 'magic_url']),
                'is_active' => true,
                'created_at'=> $now,
                'updated_at'=> $now,
            ],

            // ── Invoice ──────────────────────────────────────────────────────
            [
                'key'       => 'invoice',
                'name'      => 'Invoice',
                'subject'   => 'Your Invoice #{{invoice_number}} from {{app_name}}',
                'body'      => self::invoiceBody(),
                'variables' => json_encode(['app_name', 'user_name', 'invoice_number', 'amount', 'invoice_date', 'due_date']),
                'is_active' => true,
                'created_at'=> $now,
                'updated_at'=> $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }

    // ── HTML bodies ──────────────────────────────────────────────────────────

    private static function emailWrap(string $accentStart, string $accentEnd, string $inner): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;background:#f3f4f6;color:#111827;padding:40px 16px;}
.wrapper{max-width:520px;margin:0 auto;}
.card{background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);}
.header{background:$accentStart;padding:32px 40px 28px;text-align:center;}
.header .brand{font-size:1.15rem;font-weight:800;color:#fff;letter-spacing:-.02em;}
.header .icon{width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px;font-size:1.5rem;}
.body{padding:32px 40px;}
.greeting{font-size:1.15rem;font-weight:700;color:#111827;margin-bottom:10px;}
.text{font-size:.9rem;color:#4b5563;line-height:1.65;margin-bottom:20px;}
.btn{display:inline-block;padding:13px 32px;background:$accentEnd;color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:.9rem;}
.divider{border:none;border-top:1px solid #e5e7eb;margin:20px 0;}
.small{font-size:.78rem;color:#9ca3af;line-height:1.6;}
.footer{padding:20px 40px;text-align:center;font-size:.75rem;color:#9ca3af;}
</style>
</head>
<body>
<div class="wrapper">
<div class="card">
<div class="header">
<div class="brand">{{app_name}}</div>
</div>
<div class="body">
$inner
</div>
</div>
<div class="footer">© {{app_name}}. You received this email because you have an account with us.</div>
</div>
</body>
</html>
HTML;
    }

    private static function welcomeBody(): string
    {
        $inner = <<<HTML
<p class="greeting">Welcome aboard, {{user_name}}! 🎉</p>
<p class="text">We're thrilled to have you join us. Your account is ready and waiting for you.</p>
<p style="text-align:center;margin:24px 0;">
  <a href="{{login_url}}" class="btn">Get Started</a>
</p>
<hr class="divider">
<p class="small">If the button doesn't work, copy and paste this link into your browser:<br><a href="{{login_url}}" style="color:#6366f1;">{{login_url}}</a></p>
HTML;
        return self::emailWrap('linear-gradient(135deg,#10b981,#059669)', '#10b981', $inner);
    }

    private static function passwordResetBody(): string
    {
        $inner = <<<HTML
<p class="greeting">Password Reset Request</p>
<p class="text">Hi {{user_name}}, we received a request to reset the password for your account. Click the button below to choose a new password. This link expires in <strong>{{expiry_minutes}} minutes</strong>.</p>
<p style="text-align:center;margin:24px 0;">
  <a href="{{reset_url}}" class="btn">Reset Password</a>
</p>
<hr class="divider">
<p class="small">If you did not request a password reset, please ignore this email — your password will remain unchanged.<br><br>Or copy this link: <a href="{{reset_url}}" style="color:#6366f1;">{{reset_url}}</a></p>
HTML;
        return self::emailWrap('linear-gradient(135deg,#6366f1,#4f46e5)', '#6366f1', $inner);
    }

    private static function magicLinkBody(): string
    {
        $inner = <<<HTML
<p class="greeting">Your sign-in link is ready</p>
<p class="text">Hi {{user_name}}, click the button below to sign in instantly — no password needed. This link is single-use and expires shortly.</p>
<p style="text-align:center;margin:24px 0;">
  <a href="{{magic_url}}" class="btn">Sign In Now</a>
</p>
<hr class="divider">
<p class="small">If you didn't request this link, you can safely ignore this email.<br><br>Or copy this link: <a href="{{magic_url}}" style="color:#6366f1;">{{magic_url}}</a></p>
HTML;
        return self::emailWrap('linear-gradient(135deg,#4f46e5,#7c3aed)', '#4f46e5', $inner);
    }

    private static function invoiceBody(): string
    {
        $inner = <<<HTML
<p class="greeting">Invoice #{{invoice_number}}</p>
<p class="text">Hi {{user_name}}, please find your invoice details below.</p>
<table style="width:100%;border-collapse:collapse;margin:16px 0;font-size:.88rem;">
  <tr style="background:#f9fafb;">
    <td style="padding:10px 12px;border:1px solid #e5e7eb;font-weight:600;color:#374151;">Invoice #</td>
    <td style="padding:10px 12px;border:1px solid #e5e7eb;color:#111827;">{{invoice_number}}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;border:1px solid #e5e7eb;font-weight:600;color:#374151;">Amount</td>
    <td style="padding:10px 12px;border:1px solid #e5e7eb;color:#111827;font-weight:700;">{{amount}}</td>
  </tr>
  <tr style="background:#f9fafb;">
    <td style="padding:10px 12px;border:1px solid #e5e7eb;font-weight:600;color:#374151;">Invoice Date</td>
    <td style="padding:10px 12px;border:1px solid #e5e7eb;color:#111827;">{{invoice_date}}</td>
  </tr>
  <tr>
    <td style="padding:10px 12px;border:1px solid #e5e7eb;font-weight:600;color:#374151;">Due Date</td>
    <td style="padding:10px 12px;border:1px solid #e5e7eb;color:#111827;">{{due_date}}</td>
  </tr>
</table>
<hr class="divider">
<p class="small">Thank you for your business. If you have any questions about this invoice, please contact us.</p>
HTML;
        return self::emailWrap('linear-gradient(135deg,#f59e0b,#d97706)', '#f59e0b', $inner);
    }
};
