<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{Wishlist, WishlistShare};
use Carbon\Carbon;

class WishlistShareController extends Controller
{
    /**
     * Show the form for creating a new shared wishlist
     */
    public function create()
    {
        $user = auth()->user();
        $wishlistItems = Wishlist::where('user_id', $user->id)
            ->with(['product.media'])
            ->get();

        if ($wishlistItems->isEmpty()) {
            return redirect()->route('wishlist.index')
                ->with('error', 'Your wishlist is empty. Add some items before sharing.');
        }

        // Get existing shares
        $existingShares = WishlistShare::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('wishlist.share.create', compact('wishlistItems', 'existingShares'));
    }

    /**
     * Store a newly created shared wishlist
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:today',
            'is_public' => 'boolean'
        ]);

        $user = auth()->user();
        
        // Check if user has wishlist items
        $wishlistItems = Wishlist::where('user_id', $user->id)->get();
        
        if ($wishlistItems->isEmpty()) {
            return back()->with('error', 'Your wishlist is empty. Add some items before sharing.');
        }

        // Create shared wishlist
        $share = WishlistShare::create([
            'user_id' => $user->id,
            'token' => Str::random(32),
            'name' => $request->name,
            'description' => $request->description,
            'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
            'is_public' => $request->boolean('is_public', false),
            'view_count' => 0
        ]);

        // Copy current wishlist items to the share
        foreach ($wishlistItems as $item) {
            $share->items()->create([
                'product_id' => $item->product_id,
                'added_at' => $item->created_at
            ]);
        }

        $shareUrl = route('wishlist.shared.view', $share->token);

        return redirect()->route('wishlist.shared.index')
            ->with('success', 'Wishlist shared successfully!')
            ->with('share_url', $shareUrl);
    }

    /**
     * Display a listing of user's shared wishlists
     */
    public function index()
    {
        $user = auth()->user();
        
        $shares = WishlistShare::where('user_id', $user->id)
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('wishlist.share.index', compact('shares'));
    }

    /**
     * Display the specified shared wishlist (public view)
     */
    public function view(string $token)
    {
        $share = WishlistShare::where('token', $token)
            ->with(['user:id,name', 'items.product.media'])
            ->firstOrFail();

        // Check if expired
        if ($share->expires_at && $share->expires_at->isPast()) {
            abort(404, 'This shared wishlist has expired.');
        }

        // Increment view count
        $share->increment('view_count');

        return view('wishlist.share.view', compact('share'));
    }

    /**
     * Delete a shared wishlist
     */
    public function destroy(WishlistShare $share)
    {
        $this->authorize('delete', $share);
        
        $share->delete();

        return back()->with('success', 'Shared wishlist deleted successfully.');
    }

    /**
     * Toggle public/private status
     */
    public function toggleVisibility(WishlistShare $share)
    {
        $this->authorize('update', $share);
        
        $share->update([
            'is_public' => !$share->is_public
        ]);

        $status = $share->is_public ? 'public' : 'private';
        
        return back()->with('success', "Wishlist is now {$status}.");
    }

    /**
     * Extend expiration date
     */
    public function extend(Request $request, WishlistShare $share)
    {
        $this->authorize('update', $share);
        
        $request->validate([
            'expires_at' => 'required|date|after:today'
        ]);

        $share->update([
            'expires_at' => Carbon::parse($request->expires_at)
        ]);

        return back()->with('success', 'Expiration date updated successfully.');
    }
}