<?php

namespace App\Http\Concerns;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ResolvesCartItems
{
    /**
     * Return (and lazily create) the session ID used to scope cart items.
     */
    private function cartSessionId(Request $request): string
    {
        if (! $request->session()->has('cart_session_id')) {
            $request->session()->put('cart_session_id', Str::uuid()->toString());
        }

        return $request->session()->get('cart_session_id');
    }

    /**
     * Fetch the current visitor's cart items, eager-loading their products.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, CartItem>
     */
    private function cartItems(Request $request): \Illuminate\Database\Eloquent\Collection
    {
        return CartItem::with('product')
            ->where('session_id', $this->cartSessionId($request))
            ->get();
    }
}
