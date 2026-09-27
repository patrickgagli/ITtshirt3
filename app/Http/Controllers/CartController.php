<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ResolvesCartItems;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    use ResolvesCartItems;

    /**
     * Abort with 403 if the given cart item does not belong to the current visitor.
     */
    private function authorizeItem(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->session_id === $this->cartSessionId($request), 403);
    }

    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $items = $this->cartItems($request);
        $totalCents = $items->sum(fn (CartItem $item) => $item->product->price_cents * $item->quantity);

        return view('cart.index', compact('items', 'totalCents'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity'   => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);

        $sessionId = $this->cartSessionId($request);
        $quantity  = $validated['quantity'] ?? 1;
        $product   = Product::findOrFail($validated['product_id']);

        $existing = CartItem::where('session_id', $sessionId)
            ->where('product_id', $validated['product_id'])
            ->first();

        // Stock enforcement: cap the requested quantity to what remains in stock
        // (untracked products are treated as unlimited).
        if ($product->track_stock) {
            $alreadyInCart = $existing?->quantity ?? 0;
            $available     = max(0, $product->stock_quantity - $alreadyInCart);

            if ($available <= 0) {
                return $this->stockError($request, 'Ce produit est en rupture de stock.');
            }

            if ($quantity > $available) {
                return $this->stockError(
                    $request,
                    "Stock insuffisant : seulement {$available} unité(s) disponible(s).",
                );
            }
        }

        if ($existing) {
            $existing->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'session_id' => $sessionId,
                'user_id'    => $request->user()?->id,
                'product_id' => $validated['product_id'],
                'quantity'   => $quantity,
            ]);
        }

        if ($request->wantsJson()) {
            $cartCount = CartItem::where('session_id', $sessionId)->sum('quantity');
            return response()->json([
                'message'    => 'Produit ajouté au panier.',
                'cart_count' => $cartCount,
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Produit ajouté au panier.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->authorizeItem($request, $cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        // Stock enforcement on update.
        $product = $cartItem->product;
        if ($product->track_stock && $validated['quantity'] > $product->stock_quantity) {
            return $this->stockError(
                $request,
                "Stock insuffisant : seulement {$product->stock_quantity} unité(s) disponible(s).",
            );
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        if ($request->wantsJson()) {
            $cartCount = CartItem::where('session_id', $this->cartSessionId($request))->sum('quantity');
            return response()->json([
                'message'    => 'Quantité mise à jour.',
                'cart_count' => $cartCount,
                'quantity'   => $cartItem->quantity,
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Quantité mise à jour.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse|JsonResponse
    {
        $this->authorizeItem($request, $cartItem);

        $sessionId = $this->cartSessionId($request);
        $cartItem->delete();

        if ($request->wantsJson()) {
            $cartCount = CartItem::where('session_id', $sessionId)->sum('quantity');
            return response()->json([
                'message'    => 'Produit retiré du panier.',
                'cart_count' => $cartCount,
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Produit retiré du panier.');
    }

    public function checkout(Request $request): View|RedirectResponse
    {
        $items = $this->cartItems($request);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('info', 'Votre panier est vide.');
        }

        $totalCents = $items->sum(fn (CartItem $item) => $item->product->price_cents * $item->quantity);

        // Pre-store the checkout URL as the intended destination so both
        // the login and register flows can redirect back here after auth.
        if (! $request->user()) {
            $request->session()->put('url.intended', route('checkout.address'));
        }

        return view('cart.checkout', compact('items', 'totalCents'));
    }

    /**
     * Return a stock-related error response that fits the request (JSON for AJAX,
     * redirect with flash for HTML form posts).
     */
    private function stockError(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['quantity' => $message]);
    }
}
