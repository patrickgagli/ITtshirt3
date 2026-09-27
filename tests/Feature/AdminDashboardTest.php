<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('auth.login'));
    }

    public function test_customer_cannot_access_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
             ->get(route('admin.dashboard'))
             ->assertForbidden();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewIs('admin.dashboard');
    }

    // -------------------------------------------------------------------------
    // KPI stats
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_product_count(): void
    {
        Product::factory()->count(3)->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('productCount', 3);
    }

    public function test_dashboard_shows_order_count(): void
    {
        Order::factory()->count(4)->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('orderCount', 4);
    }

    public function test_dashboard_shows_pending_count_for_paid_and_processing_orders(): void
    {
        Order::factory()->paid()->create();
        Order::factory()->processing()->create();
        Order::factory()->shipped()->create();
        Order::factory()->cancelled()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('pendingCount', 2);
    }

    public function test_dashboard_shows_revenue_total_for_confirmed_orders(): void
    {
        Order::factory()->create(['status' => 'paid',      'total_cents' => 5000]);
        Order::factory()->create(['status' => 'processing','total_cents' => 3000]);
        Order::factory()->create(['status' => 'shipped',   'total_cents' => 2000]);
        Order::factory()->create(['status' => 'delivered', 'total_cents' => 1000]);
        // Pending and cancelled must NOT be included
        Order::factory()->create(['status' => 'pending',   'total_cents' => 9999]);
        Order::factory()->create(['status' => 'cancelled', 'total_cents' => 9999]);

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('revenueTotal', 11000);
    }

    public function test_dashboard_shows_revenue_as_zero_when_no_confirmed_orders(): void
    {
        Order::factory()->pending()->create(['total_cents' => 5000]);

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('revenueTotal', 0);
    }

    // -------------------------------------------------------------------------
    // Recent orders widget
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_recent_orders(): void
    {
        $order = Order::factory()->paid()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee($order->order_number);
    }

    public function test_dashboard_recent_orders_limited_to_five(): void
    {
        Order::factory()->count(7)->create();

        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $this->assertCount(5, $response->viewData('recentOrders'));
    }

    public function test_dashboard_shows_empty_state_when_no_recent_orders(): void
    {
        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('Aucune commande pour le moment', false);
    }

    public function test_dashboard_shows_payment_status_for_paid_order(): void
    {
        Order::factory()->paid()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('Payée le', false);
    }

    public function test_dashboard_shows_delivery_status_badge(): void
    {
        Order::factory()->shipped()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('Expédiée');
    }

    // -------------------------------------------------------------------------
    // Stock KPIs
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_out_of_stock_count(): void
    {
        Product::factory()->outOfStock()->count(2)->create();
        Product::factory()->inStock()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('outOfStockCount', 2);
    }

    public function test_dashboard_shows_low_stock_count(): void
    {
        Product::factory()->lowStock(2)->count(3)->create();
        Product::factory()->inStock()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('lowStockCount', 3);
    }

    public function test_dashboard_does_not_count_untracked_products_as_out_of_stock(): void
    {
        // Untracked product with stock_quantity=0 must not appear in out-of-stock
        Product::factory()->create(['track_stock' => false, 'stock_quantity' => 0]);

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertViewHas('outOfStockCount', 0);
    }

    public function test_dashboard_shows_stock_alert_widget_when_out_of_stock_products_exist(): void
    {
        Product::factory()->outOfStock()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('Rupture de stock', false);
    }

    public function test_dashboard_shows_stock_alert_widget_when_low_stock_products_exist(): void
    {
        Product::factory()->lowStock()->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('Stock faible', false);
    }

    public function test_dashboard_hides_stock_alert_widget_when_all_stock_is_healthy(): void
    {
        Product::factory()->inStock(20)->count(3)->create();

        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $response->assertDontSee('Rupture de stock', false);
        $response->assertDontSee('Stock faible', false);
    }

    // -------------------------------------------------------------------------
    // Analytics payload (Vue + Chart.js islands)
    // -------------------------------------------------------------------------

    public function test_dashboard_view_receives_analytics_payload(): void
    {
        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $analytics = $response->viewData('analytics');

        $this->assertIsArray($analytics);
        $this->assertArrayHasKey('sales', $analytics);
        $this->assertArrayHasKey('inventory', $analytics);
        $this->assertArrayHasKey('topCategories', $analytics);
        $this->assertArrayHasKey('customers', $analytics);
    }

    public function test_dashboard_renders_chart_island_markers(): void
    {
        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('data-vue="admin-sales-chart"', false)
             ->assertSee('data-vue="admin-inventory-chart"', false)
             ->assertSee('data-vue="admin-top-categories-chart"', false)
             ->assertSee('data-vue="admin-customer-insights-chart"', false);
    }

    public function test_sales_series_spans_thirty_days(): void
    {
        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $sales = $response->viewData('analytics')['sales'];

        $this->assertCount(30, $sales['labels']);
        $this->assertCount(30, $sales['revenue']);
        $this->assertCount(30, $sales['orders']);
    }

    public function test_sales_series_aggregates_only_realized_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->forUser($user)->paid()->create(['total_cents' => 5000, 'created_at' => now()]);
        Order::factory()->forUser($user)->cancelled()->create(['total_cents' => 9999, 'created_at' => now()]);
        Order::factory()->forUser($user)->state(['status' => 'pending'])->create(['total_cents' => 8888, 'created_at' => now()]);

        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $sales = $response->viewData('analytics')['sales'];

        // Last bucket is today.
        $this->assertSame(50.0, $sales['revenue'][29]);
        $this->assertSame(1,    $sales['orders'][29]);
    }

    public function test_inventory_snapshot_buckets_products_correctly(): void
    {
        Product::factory()->inStock(20)->count(2)->create();   // in_stock
        Product::factory()->lowStock(2)->count(3)->create();   // low_stock
        Product::factory()->outOfStock()->count(1)->create();  // out_of_stock
        Product::factory()->count(4)->create();                // untracked (default)

        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $inv = $response->viewData('analytics')['inventory'];

        $this->assertSame(2, $inv['inStock']);
        $this->assertSame(3, $inv['lowStock']);
        $this->assertSame(1, $inv['outOfStock']);
        $this->assertSame(4, $inv['untracked']);
    }

    public function test_top_categories_ranks_by_realized_revenue(): void
    {
        $catA = ProductCategory::factory()->create(['name' => 'Catégorie A']);
        $catB = ProductCategory::factory()->create(['name' => 'Catégorie B']);
        $prodA = Product::factory()->create(['product_category_id' => $catA->id]);
        $prodB = Product::factory()->create(['product_category_id' => $catB->id]);

        $user = User::factory()->create();
        $orderA = Order::factory()->forUser($user)->paid()->create(['total_cents' => 10000]);
        $orderA->items()->create([
            'product_id' => $prodA->id, 'product_name' => $prodA->name,
            'unit_price_cents' => 5000, 'quantity' => 2, 'currency' => 'EUR',
        ]);
        $orderB = Order::factory()->forUser($user)->paid()->create(['total_cents' => 3000]);
        $orderB->items()->create([
            'product_id' => $prodB->id, 'product_name' => $prodB->name,
            'unit_price_cents' => 3000, 'quantity' => 1, 'currency' => 'EUR',
        ]);

        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $top = $response->viewData('analytics')['topCategories'];

        $this->assertSame(['Catégorie A', 'Catégorie B'], $top['labels']);
        $this->assertSame([100.0, 30.0],                  $top['revenue']);
    }

    public function test_customer_insights_counts_repeat_buyers(): void
    {
        $loyal = User::factory()->create();
        Order::factory()->forUser($loyal)->paid()->count(2)->create();

        $occasional = User::factory()->create();
        Order::factory()->forUser($occasional)->paid()->create();

        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $cust = $response->viewData('analytics')['customers'];

        $this->assertSame(2, $cust['totalBuyers']);
        $this->assertSame(1, $cust['repeatBuyers']);
        $this->assertCount(30, $cust['labels']);
        $this->assertCount(30, $cust['newUsers']);
    }

    // -------------------------------------------------------------------------
    // Contact analytics
    // -------------------------------------------------------------------------

    public function test_analytics_payload_includes_contact_key(): void
    {
        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $analytics = $response->viewData('analytics');

        $this->assertArrayHasKey('contact', $analytics);
        $this->assertArrayHasKey('labels',           $analytics['contact']);
        $this->assertArrayHasKey('submissions',      $analytics['contact']);
        $this->assertArrayHasKey('totalSubmissions', $analytics['contact']);
        $this->assertArrayHasKey('topSubjects',      $analytics['contact']);
    }

    public function test_contact_insights_spans_thirty_days(): void
    {
        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $contact = $response->viewData('analytics')['contact'];

        $this->assertCount(30, $contact['labels']);
        $this->assertCount(30, $contact['submissions']);
    }

    public function test_contact_insights_counts_submissions_in_window(): void
    {
        ContactSubmission::create([
            'name'    => 'Alice',
            'email'   => 'alice@example.com',
            'subject' => 'Question produit',
            'message' => 'Test message for analytics.',
        ]);

        $response = $this->actingAs($this->admin())
                         ->get(route('admin.dashboard'))
                         ->assertOk();

        $contact = $response->viewData('analytics')['contact'];

        $this->assertSame(1, $contact['totalSubmissions']);
        $this->assertSame(1, $contact['submissions'][29]); // today is the last bucket
    }

    public function test_dashboard_renders_contact_chart_island(): void
    {
        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('data-vue="admin-contact-requests-chart"', false);
    }
}
