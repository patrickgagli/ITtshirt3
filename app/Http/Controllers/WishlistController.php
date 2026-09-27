<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $items = WishlistItem::with(['product', 'product.category'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('wishlist.index', compact('items'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $item = WishlistItem::firstOrCreate([
            'user_id'    => $request->user()->id,
            'product_id' => $validated['product_id'],
        ]);

        if ($request->wantsJson()) {
            $wishlistCount = WishlistItem::where('user_id', $request->user()->id)->count();
            return response()->json([
                'message'          => 'Produit ajouté à votre liste de souhaits.',
                'wishlist_count'   => $wishlistCount,
                'wishlist_item_id' => $item->id,
            ]);
        }

        return back()->with('success', 'Produit ajouté à votre liste de souhaits.');
    }

    public function destroy(Request $request, WishlistItem $wishlistItem): RedirectResponse|JsonResponse
    {
        abort_unless($wishlistItem->user_id === $request->user()->id, 403);

        $wishlistItem->delete();

        if ($request->wantsJson()) {
            $wishlistCount = WishlistItem::where('user_id', $request->user()->id)->count();
            return response()->json([
                'message'        => 'Produit retiré de votre liste de souhaits.',
                'wishlist_count' => $wishlistCount,
            ]);
        }

        return back()->with('success', 'Produit retiré de votre liste de souhaits.');
    }

    public function moveToCart(Request $request, WishlistItem $wishlistItem): RedirectResponse
    {
        abort_unless($wishlistItem->user_id === $request->user()->id, 403);

        // Resolve (or lazily create) the session cart ID.
        if (! $request->session()->has('cart_session_id')) {
            $request->session()->put('cart_session_id', Str::uuid()->toString());
        }
        $sessionId = $request->session()->get('cart_session_id');

        $cartItem = CartItem::where('session_id', $sessionId)
            ->where('product_id', $wishlistItem->product_id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
        } else {
            CartItem::create([
                'session_id' => $sessionId,
                'user_id'    => $request->user()->id,
                'product_id' => $wishlistItem->product_id,
                'quantity'   => 1,
            ]);
        }

        $wishlistItem->delete();

        return redirect()->route('cart.index')
            ->with('success', 'Produit déplacé vers votre panier.');
    }
}
