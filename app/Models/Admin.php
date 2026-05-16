<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'locale',
        'ui_theme',
        'is_active',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'current_session_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'password'                => 'hashed',
            'is_active'               => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(AdminLoginHistory::class)->latest('created_at');
    }

    /**
     * Override to send reset link to admin.password.reset route.
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = route('admin.password.reset', [
            'token' => $token,
            'email' => $this->email,
        ]);

        $this->notify(new \App\Notifications\AdminResetPasswordNotification($url));
    }
}
