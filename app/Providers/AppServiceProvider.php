<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\SiteSetting;
use App\Models\WishlistItem;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function (\Illuminate\View\View $view) {
            $sessionId = session('cart_session_id');
            $cartCount = $sessionId
                ? CartItem::where('session_id', $sessionId)->sum('quantity')
                : 0;

            $userId = auth()->id();
            $wishlistCount = $userId
                ? WishlistItem::where('user_id', $userId)->count()
                : 0;

            $view->with('cartCount', $cartCount)
                 ->with('wishlistCount', $wishlistCount)
                 ->with('siteSetting', SiteSetting::instance());
        });
    }
}
