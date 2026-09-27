<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/catalogue', [ProductController::class, 'index'])->name('products.index');
Route::get('/catalogue/{slug}', [ProductController::class, 'show'])->name('products.show');

// Shopping cart (public – guests and authenticated users; no auth required to browse or add items)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',                           [CartController::class, 'index'])->name('index');
    Route::post('/items',                     [CartController::class, 'store'])->name('items.store');
    Route::patch('/items/{cartItem}',         [CartController::class, 'update'])->name('items.update');
    Route::delete('/items/{cartItem}',        [CartController::class, 'destroy'])->name('items.destroy');
    Route::get('/checkout',                   [CartController::class, 'checkout'])->name('checkout');
});

// Checkout (authenticated users only, multi-step)
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/address',         [CheckoutController::class, 'showAddress'])->name('address');
    Route::post('/address',        [CheckoutController::class, 'storeAddress'])->name('address.store');
    Route::get('/payment',         [CheckoutController::class, 'showPayment'])->name('payment');
    Route::post('/payment',        [CheckoutController::class, 'processPayment'])->name('payment.process');
    Route::get('/confirmation/{order:uuid}', [CheckoutController::class, 'showConfirmation'])->name('confirmation');
});

// Stripe webhook (CSRF-exempt – see bootstrap/app.php)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Cookie consent (public – no auth required)
Route::get('/cookies', [CookieConsentController::class, 'show'])->name('cookies.show');
Route::post('/cookies/consent', [CookieConsentController::class, 'store'])->name('cookies.consent.store');
Route::delete('/cookies/consent', [CookieConsentController::class, 'withdraw'])->name('cookies.consent.withdraw');

// Contact form (public – no auth required)
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Newsletter subscription (public – no auth required)
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribeForm'])->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe.confirm');
Route::get('/newsletter/unsubscribed', [NewsletterController::class, 'unsubscribed'])->name('newsletter.unsubscribed');

// Guest-only auth routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('auth.register');
    Route::post('/register', [RegisterController::class, 'store'])->name('auth.register.store');
    Route::get('/login', [LoginController::class, 'showForm'])->name('auth.login');
    Route::post('/login', [LoginController::class, 'login'])->name('auth.login.store')->middleware('throttle:6,1');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated-only routes
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout')->middleware('auth');

// Account / profile routes
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'show'])->name('show');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order:uuid}', [AccountController::class, 'showOrder'])->name('orders.show');
});

// Wishlist (authenticated users only)
Route::middleware('auth')->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/',                           [WishlistController::class, 'index'])->name('index');
    Route::post('/items',                     [WishlistController::class, 'store'])->name('items.store');
    Route::delete('/items/{wishlistItem}',    [WishlistController::class, 'destroy'])->name('items.destroy');
    Route::post('/items/{wishlistItem}/cart', [WishlistController::class, 'moveToCart'])->name('items.move-to-cart');
});

// Admin routes
Route::middleware(['auth', 'isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/settings',  [SiteSettingController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SiteSettingController::class, 'update'])->name('settings.update');
});
