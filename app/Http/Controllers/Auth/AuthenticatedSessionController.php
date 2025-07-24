<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\CartService;
use App\Services\RecentlyViewedService;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $sessionCartId = session()->get('cart_session_id');
        
        $request->authenticate();
        $request->session()->regenerate();

        if ($sessionCartId) {
            session()->put('cart_session_id', $sessionCartId);
        }

        // Merge session cart to user cart after successful login
        app(CartService::class)->mergeSessionCartToUser();

        app(RecentlyViewedService::class)->mergeGuestToUser();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
