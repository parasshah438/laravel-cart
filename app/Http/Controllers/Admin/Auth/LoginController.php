<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivityLog;
use App\Models\AdminLoginHistory;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Maximum login attempts before lockout.
     */
    protected int $maxAttempts = 5;

    /**
     * Minutes to lock the account after too many attempts.
     */
    protected int $decayMinutes = 1;

    public function username(): string
    {
        return 'email';
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input($this->username())).'|'.$request->ip());
    }

    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        return RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts);
    }

    protected function incrementLoginAttempts(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);
    }

    protected function clearLoginAttempts(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    protected function fireLockoutEvent(Request $request): void
    {
        event(new Lockout($request));
    }

    protected function sendLockoutResponse(Request $request): void
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ])->status(429);
    }

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Lockout check — throws ValidationException when limit exceeded
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            /** @var Admin $admin */
            $admin = Auth::guard('admin')->user();

            // If 2FA is enabled, log out again and redirect to 2FA challenge
            if ($admin->two_factor_confirmed_at !== null) {
                Auth::guard('admin')->logout();
                $request->session()->put('admin.2fa_pending_id', $admin->id);
                return redirect()->route('admin.2fa.verify');
            }

            $request->session()->regenerate();
            $request->session()->put('admin.2fa_verified', true);

            $this->clearLoginAttempts($request);

            // Single session enforcement — store current session ID
            $admin->update(['current_session_id' => $request->session()->getId()]);

            AdminLoginHistory::record($admin->id, 'success', $request);
            AdminActivityLog::log('login', 'Admin logged in');

            return redirect()->intended(route('admin.dashboard'));
        }

        // Failed attempt — increment counter then record history
        $this->incrementLoginAttempts($request);

        $failedAdmin = Admin::where('email', $request->email)->first();
        AdminLoginHistory::record($failedAdmin?->id, 'failed', $request, $failedAdmin ? null : $request->email);

        throw ValidationException::withMessages([
            'email' => __('These credentials do not match our records.'),
        ]);
    }

    public function logout(Request $request)
    {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if ($admin) {
            $admin->loginHistories()
                  ->whereNull('logged_out_at')
                  ->where('status', 'success')
                  ->latest('created_at')
                  ->first()
                  ?->update(['logged_out_at' => now()]);

            // Clear single-session token on voluntary logout
            $admin->update(['current_session_id' => null]);
        }

        AdminActivityLog::log('logout', 'Admin logged out');
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
