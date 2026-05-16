<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function showForm(Request $request, string $token)
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) {
                $admin->forceFill(['password' => Hash::make($password)])
                      ->setRememberToken(Str::random(60));
                $admin->save();
                event(new PasswordReset($admin));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')
                ->with('status', 'Your password has been reset successfully. Please sign in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
