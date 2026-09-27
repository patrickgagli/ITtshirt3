# ITtshirt3 Repository Instructions

## Project Reality

- Treat this repository as a mid-stage Laravel 13 application with a working storefront slice, not as a completed e-commerce platform.
- The product goal documented under `docs/` is a t-shirt e-commerce platform. The implemented code covers: a home page, a read-only product catalogue (with category filtering and detail pages), user authentication (register/login/logout), a user account/profile page with order history, a session-based shopping cart, a multi-step checkout flow (address → Stripe payment → confirmation), a wishlist (authenticated users), admin CRUD surfaces for products, categories, and orders, a contact form (with mailable), cookie consent (GDPR preferences + banner), and a newsletter subscription/unsubscription flow.
- Do not describe stock/inventory management features as implemented unless you add the code in the same change.

## Current Technical Baseline

- Backend: PHP 8.3 (requirement), PHP 8.4 runtime, Laravel 13.6.
- Database: SQLite for local development (configured in `.env`).
- Frontend tooling: Laravel UI is installed; Vite 8, Vue 3, Bootstrap 5, Tailwind CSS 4, Sass, and Axios are available. Vue 3 is actively used via an island-mounting pattern (`[data-vue]` elements with `data-props` JSON) for cart and wishlist interactions on the product detail and cart pages, plus the home-page hero and featured-product showcase. Vite inputs are `resources/css/app.css` (Tailwind v4 entry), `resources/sass/app.scss`, and `resources/js/app.js` — all three must be present in the build manifest, so run `npm run build` after changing inputs. The dev server is configured with `server.host = '127.0.0.1'` in `vite.config.js` so the `public/hot` URL is reachable from the browser inside the dev container.
- Payments: `stripe/stripe-php` v20.1 installed. Stripe Elements (JS v3 CDN) used for card collection — raw card data never reaches the server. `StripePaymentService` wraps SDK calls (injectable/mockable). Stripe config in `config/services.php` reads `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` from `.env`.
- Current route surface: `routes/web.php` exposes:
  - `/` (home), `/catalogue` (product listing with optional `?category=slug` filter), `/catalogue/{slug}` (product detail)
  - `/cart` (GET show), `/cart/checkout` (GET review page), `/cart/items` (POST store), `/cart/items/{cartItem}` (PATCH update, DELETE destroy) — public routes, no auth required; guests can browse and manage their cart using a session-scoped `cart_session_id`
  - `/checkout/address` (GET show, POST store), `/checkout/payment` (GET show, POST process), `/checkout/confirmation/{order:uuid}` (GET) — all require authenticated user
  - `/wishlist` (GET index), `/wishlist/items` (POST store), `/wishlist/items/{wishlistItem}` (DELETE destroy, POST move-to-cart)
  - `/contact` (GET show, POST store)
  - `/cookies` (GET preferences), `/cookies/consent` (POST store, DELETE withdraw)
  - `/newsletter` (POST store), `/newsletter/unsubscribe/{token}` (GET form, POST confirm), `/newsletter/unsubscribed` (GET)
  - Guest-only `/register` and `/login`, authenticated POST `/logout`
  - Guest-only `/forgot-password` (GET form, POST send reset link) and `/reset-password/{token}` (GET form, POST process reset)
  - Authenticated `/account` (GET show, PATCH profile update, PATCH password update), `/account/orders` (GET index), `/account/orders/{order:uuid}` (GET show)
  - Admin-only `/admin/dashboard`, `/admin/products` (full resource except show), `/admin/categories` (full resource except show), `/admin/orders` (GET index, GET show), `/admin/orders/{order}/status` (PATCH)
  - `/stripe/webhook` (POST, CSRF-exempt) — handled by `StripeWebhookController`
- Current app surface:
  - `HomeController` — invokable; passes product/category counts, category summaries, and featured products (eager-loaded with `category`, limited to 4 featured) to the landing view. The home view maps each featured product into the `dynamic-product-showcase` props as `id`, `name`, `category_name`, `formatted_price`, and `image_url` (`asset('storage/'.image_path)` when set, otherwise `null`).
  - `ProductController@index` — lists products with optional filters: `q` (full-text search on name and description), `category` (filter by slug; returns 404 on unknown slug), `availability` (`all` / `in_stock` / `low_stock` / `out_of_stock`), and `sort` (`featured` / `name_asc` / `name_desc` / `price_asc` / `price_desc` / `stock`); paginates at 12 per page via `paginate(12)->withQueryString()`; unknown values for `sort` and `availability` silently fall back to defaults (`featured`, `all`).
  - `ProductController@show` — product detail by slug with 404 on miss; shows wishlist status for authenticated users.
  - `CartController` — guest-and-authenticated cart CRUD (index, store, update, destroy, checkout). Uses `ResolvesCartItems` trait. `store`, `update`, and `destroy` return JSON (`message`, `cart_count`, and optionally `quantity`) when `wantsJson()` is true, enabling Vue AJAX calls. `checkout` renders `cart.checkout` view with order summary; redirects to `cart.index` when empty. Guests see a login/register prompt on `cart.checkout` — the "Se connecter" button links to `checkout.address`, the `auth` middleware stores the intended URL and redirects to login; after login, `redirect()->intended()` sends the user to `checkout.address`. **Stock enforcement**: when the target product has `track_stock = true`, `store` rejects when out of stock and caps requested quantity to `stock_quantity - existingCartQty`; `update` rejects when the new quantity exceeds `stock_quantity`. Rejections flash `withErrors(['quantity' => ...])` for HTML and return HTTP 422 `{message}` for JSON. Untracked products are unlimited (still capped at 99 by request validation).
  - `CheckoutController` — multi-step checkout. `showAddress`/`storeAddress` (step 1, saves to session), `showPayment` (step 2, creates Stripe PaymentIntent and stores its ID as `checkout_payment_intent_id` in session), `processPayment` (validates `payment_intent_id`, verifies it matches the session-stored PI ID to prevent PaymentIntent substitution attacks, retrieves PI from Stripe, verifies `status === succeeded`, creates Order + OrderItems, decrements `stock_quantity` for tracked products, clears cart — all inside a single `DB::transaction()` with `lockForUpdate()` on the affected tracked products to prevent overselling under concurrency), `confirmation` (shows order by UUID). Requires authenticated user; uses `ResolvesCartItems` trait. If a tracked product no longer has enough stock at confirmation time, the transaction aborts with a `RuntimeException` and the user is redirected back with a `payment` flash error; no order is recorded (Stripe charge will need manual refund).
  - `StripeWebhookController@handle` — verifies Stripe signature; handles `payment_intent.succeeded` (marks order paid) and `payment_intent.payment_failed` (marks order cancelled).
  - `WishlistController` — authenticated CRUD for wishlist items; `moveToCart` moves item to session cart (increments if already present). `store` and `destroy` return JSON (`message`, `wishlist_count`, and `wishlist_item_id` for store) when `wantsJson()` is true.
  - `AccountController@show` — authenticated profile page.
  - `AccountController@orders` — paginated order history for authenticated user.
  - `AccountController@showOrder` — order detail; 404 on ownership mismatch.
  - `AccountController@updateProfile` — PATCH; validates name, email, and current password; redirects to `account.show` on success.
  - `AccountController@updatePassword` — PATCH; validates current password and new password; redirects to `account.show` on success.
  - `Auth\RegisterController` — guest-only registration form and store action.
  - `Auth\LoginController` — guest-only login form, store action (throttled), and logout.
  - `Auth\ForgotPasswordController` — guest-only form (`GET /forgot-password`) and `sendResetLink` action (`POST`); uses `Password::sendResetLink()`; always returns the same `status` flash regardless of whether the email exists.
  - `Auth\ResetPasswordController` — guest-only form (`GET /reset-password/{token}`) and `reset` action (`POST`); calls `Password::reset()`, hashes the new password via `forceFill`, emits `PasswordReset` event, redirects to login on success.
  - `ContactController@show` — contact form view (pre-fills name/email for authenticated users).
  - `ContactController@store` — validates and dispatches `ContactInquiry` mailable; redirects back with flash.
  - `CookieConsentController@show` — GDPR preferences page; reads `cookie_consent` cookie.
  - `CookieConsentController@store` — persists consent choices in a 1-year cookie; accepts `_redirect` field (validated to same-origin with trailing-slash guard to prevent open-redirect and subdomain-prefix bypass).
  - `CookieConsentController@withdraw` — expires the consent cookie and redirects to preferences.
  - `NewsletterController@store` — subscribes or reactivates an email; stores token; flashes session message.
  - `NewsletterController@unsubscribeForm` / `@unsubscribe` — token-based unsubscription flow.
  - `NewsletterController@unsubscribed` — confirmation view.
  - `Admin\DashboardController@index` — admin-only dashboard view; passes `userCount`, `productCount`, `categoryCount`, `adminCount`, `orderCount`, `pendingCount` (orders in `paid` or `processing` status), `revenueTotal` (sum of `total_cents` for `paid/processing/shipped/delivered` orders), `recentOrders` (5 most recent, eager-loaded with `user`), `outOfStockCount` (tracked products with `stock_quantity = 0`), `lowStockCount` (tracked products at or below `LOW_STOCK_THRESHOLD`), and `analytics` (data payload for Vue/Chart.js islands: `sales` = rolling 30-day daily revenue + order count, `inventory` = bucketed product stock counts {inStock, lowStock, outOfStock, untracked}, `topCategories` = top 5 categories by realized revenue, `customers` = rolling 30-day daily new-customer registrations + totalBuyers/repeatBuyers from realized orders) to the view.
  - `Admin\ProductController` — admin CRUD for products (index, create, store, edit, update, destroy).
  - `Admin\CategoryController` — admin CRUD for categories (index, create, store, edit, update, destroy).
  - `Admin\OrderController` — admin order listing (index), order detail (show), and status update (PATCH status).
  - `Admin\SiteSettingController` — admin singleton settings (edit GET, update PATCH) for SEO and social media fields.
  - `App\Services\StripePaymentService` — injectable wrapper: `createPaymentIntent()`, `retrievePaymentIntent()`, `constructWebhookEvent()`.
  - `App\Http\Concerns\ResolvesCartItems` trait — `cartSessionId(Request)` and `cartItems(Request)` shared by `CartController` and `CheckoutController`.
  - `IsAdmin` middleware — aborts with 403 unless `$user->isAdmin()` is true.
  - `Product` model — `category()` BelongsTo relation; `catalogued()` scope (featured-first then by name); `inStock()`, `lowStock()`, and `outOfStock()` scopes for filtering by stock status; `isInStock()`, `isLowStock()`, `isOutOfStock()` helpers; `stockLabel()` and `stockBadgeClass()` for display; `LOW_STOCK_THRESHOLD = 5` constant; `formattedPrice()`; `track_stock` (bool, default false) and `stock_quantity` (unsigned int, default 0) fields.
  - `ProductCategory` model — `products()` HasMany relation.
  - `CartItem` model — `product()` BelongsTo; scoped by `session_id` (UUID from session).
  - `Order` model — `user()` BelongsTo, `items()` HasMany; fields include `uuid`, `order_number` (ORD-YYYYMMDD-XXXXXX, assigned on payment confirmation), `status` (pending/paid/processing/shipped/delivered/cancelled), `stripe_payment_intent_id`, `paid_at`, `shipping_*` address fields, `subtotal_cents`, `total_cents`, `currency`; static `generateOrderNumber(Order): string`; `isPaid(): bool`; `statusLabel(): string` (French label); `statusBadgeClass(): string` (Bootstrap 5 class); static `allowedStatuses(): array`; `formattedTotal(): string`.
  - `OrderItem` model — `order()` BelongsTo, `product()` BelongsTo; records name, price, and quantity at purchase time.
  - `WishlistItem` model — `user()` BelongsTo, `product()` BelongsTo; unique `(user_id, product_id)`.
  - `User` model — `role` field (default `'customer'`, **not** in `$fillable`; assigned only in controlled code paths), `isAdmin()`, `isCustomer()` helpers; `password` cast to `hashed`; `orders()` HasMany relation.
  - `NewsletterSubscriber` model — `email` (unique), `name` (nullable), `token` (64-char, unique), `subscribed_at`, `unsubscribed_at`; `isActive(): bool`; `generateToken(): string` (static).
  - `ContactInquiry` mailable — receives `array $inquiry`; sets `replyTo` to sender email; subject `[Contact] {subject}`; uses view `emails.contact-inquiry`.
  - `cookie-banner` Blade component — shown on all public pages when no `cookie_consent` cookie is present.
  - Vue components (island-mounted via `resources/js/app.js`): `AddToCartButton` (`[data-vue="add-to-cart"]`, product detail page), `WishlistToggle` (`[data-vue="wishlist-toggle"]`, product detail page; updates badges with the server-returned `wishlist_count`, not a local delta), `CartItemControl` (`[data-vue="cart-item-control"]`, cart page with non-JS Blade fallback), `CreativeHero` (`[data-vue="creative-hero"]`, home page hero section with animations and glassmorphism), `DynamicProductShowcase` (`[data-vue="dynamic-product-showcase"]`, home page featured products display; renders the product image from `image_url` when present, otherwise a t-shirt SVG placeholder), `SalesChart` (`[data-vue="admin-sales-chart"]`, admin dashboard rolling 30-day revenue + orders line chart), `InventoryChart` (`[data-vue="admin-inventory-chart"]`, admin dashboard stock-bucket doughnut), `TopCategoriesChart` (`[data-vue="admin-top-categories-chart"]`, admin dashboard horizontal bar of top 5 categories by realized revenue), `CustomerInsightsChart` (`[data-vue="admin-customer-insights-chart"]`, admin dashboard new-customer bar + repeat-buyer ratio), `ContactRequestsChart` (`[data-vue="admin-contact-requests-chart"]`, admin dashboard rolling 30-day contact-form submission bar chart). Chart islands live under `resources/js/components/admin/` and use Chart.js v4 with the components-only register pattern (no auto-register). Props injected as JSON via `data-props`; cart/wishlist badge counts updated via `[data-cart-count]` / `[data-wishlist-count]`.
  - `AmbientBackground` Vue component (`resources/js/components/ui/AmbientBackground.vue`) — programmatically mounted into `#ambient-background-container` prepended to `<body>` on every page load (not in the island registry). Adds `page-entering` class to `<body>` on mount to trigger a CSS `@keyframes page-enter` page-load fade-in animation. Renders three blurred radial-gradient blobs (`position: fixed; z-index: -1; pointer-events: none`) that drift via `@keyframes float`.
  - Global UI CSS utilities in `resources/css/app.css` (`@layer utilities`): `.reveal-on-scroll` / `.reveal-on-scroll.is-revealed` (IntersectionObserver-driven scroll reveal), `.reveal-delay-{100,200,300,400}` (stagger helpers), `.hover-micro-lift`, `.hover-micro-scale`, `.hover-glow` (hover microinteraction utilities). A delegated `IntersectionObserver` in `app.js` observes `.reveal-on-scroll` elements 100 ms after page load.
  - All standalone Blade views (those with their own `<!DOCTYPE html>`) now include `@vite(['resources/css/app.css', 'resources/js/app.js'])` so the global UI layer is available everywhere. `admin/layout.blade.php` loads Vite at the bottom of `<body>` before `@stack('scripts')` (original placement preserved).
  - Storefront navbars on `products/show`, `products/index`, `cart/index`, `cart/checkout`, `wishlist/index`, `account/orders`, and `account/order` render a standalone wishlist heart icon with a `[data-wishlist-count]` red badge alongside the cart icon (`[data-cart-count]`). `home.blade.php` and `contact/show.blade.php` navbars intentionally render neither icon.
  - `AppServiceProvider` — view composer on `*` provides `$cartCount` (session-scoped), `$wishlistCount` (user-scoped), and `$siteSetting` (SiteSetting singleton) to all views automatically.
  - `SiteSetting` model — singleton (always id=1); `instance()` static helper; fields: `site_name`, `tagline`, `meta_description`, `meta_keywords`, `og_image_url`, `social_facebook`, `social_instagram`, `social_twitter`, `social_tiktok`, `twitter_handle`.
  - Storefront pages (`home`, `products/index`, `products/show`) inject `<meta name="description">`, Open Graph, and Twitter Card tags from `$siteSetting`; product detail falls back to product-specific data first.
  - Migrations: users, cache, jobs, products, product_categories, add image_path + product_category_id to products, add description to product_categories, add role to users, create newsletter_subscribers, create cart_items, create orders, create order_items, create wishlist_items, add stripe_payment_intent_id to orders, add order_number to orders, create site_settings, add track_stock + stock_quantity to products.
  - Factories: `ProductFactory` (with `featured()`, `inStock(int $quantity = 20)`, `lowStock(int $quantity = 3)`, and `outOfStock()` states), `ProductCategoryFactory`, `UserFactory`, `NewsletterSubscriberFactory`, `OrderFactory` (with `forUser()`, `status()`, `pending()`, `paid()`, `processing()`, `shipped()`, `delivered()`, `cancelled()` states; `configure()` auto-generates `order_number` after creation).
  - Seeder: 2 seed users (test@example.com customer, admin@example.com admin), 4 categories, 20 products.
- Test suite: 361 tests, all green — 3 unit, 358 feature (AccountTest, AccountOrderTest, AdminCategoryTest, AdminDashboardTest, AdminOrderTest, AdminProductTest, AdminSiteSettingTest, AuthTest, CartTest, CheckoutTest, ContactTest, CookieConsentTest, ExampleTest, HomeTest, NewsletterTest, PasswordResetTest, ProductCatalogueTest, StripeWebhookTest, WishlistTest).
- Dead code: `Admin\ProductController::suggestSlug()` and `Admin\CategoryController::suggestSlug()` are public static methods defined but never called; reserved for future AJAX slug suggestion.
- `docs/` exists and mixes product-vision documents with implementation-status documents; keep that distinction explicit when updating it.
- `php artisan pail` is excluded from `composer run dev` because the `pcntl` extension is unavailable in the dev container.
- `composer run dev` prefixes long-running PHP CLI processes with `XDEBUG_MODE=off` to avoid repeated step-debug connection noise in the dev container.

## Expected Change Style

- Prefer small, vertical slices that add a route, controller/action, view or API resource, persistence, and at least one focused test together.
- Use the existing Laravel conventions before introducing new architectural layers.
- Keep README aligned with the real implementation state when features are added.
- When a feature comes from the project brief, connect it to the corresponding source document in `docs/` instead of inventing new requirements.
- When writing test assertions against strings that contain apostrophes in raw Blade HTML (not `{{ }}`-escaped output), use `assertSee($string, false)` to avoid HTML-encoding mismatches.
- When mocking `StripePaymentService` in tests, use `$this->mock(StripePaymentService::class, ...)` with `PaymentIntent::constructFrom(['id' => ..., 'status' => ..., 'object' => 'payment_intent'])` — do not use `new PaymentIntent()`.

## Audit Priorities For Future Work

- Add transactional emails for order status changes.
- Implement promotions/discount codes.
- Add customer product reviews.
- Build advanced admin reporting/analytics (charts, exports).
- Expand tests as soon as new behavior is introduced.

## Documentation Guardrails

- README should clearly separate intended scope from implemented scope.
- `documentation_technique.md` should remain the source of truth for verified implementation details.
- Product-vision documents such as `cahier_des_charges.md` may describe broader scope, but they must explicitly label that scope as target state.
- If the repository is still scaffold-level, say so directly.
- Record important repository-specific decisions in documentation when they are verified in code.
