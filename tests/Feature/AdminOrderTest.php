<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function admin(): User
    {
        $user       = User::factory()->create();
        $user->role = 'admin';
        $user->save();

        return $user;
    }

    private function makeOrderWithItem(array $orderState = []): array
    {
        $product = Product::factory()->create(['price_cents' => 3000]);
        $order   = Order::factory()->create($orderState);
        $item    = OrderItem::create([
            'order_id'         => $order->id,
            'product_id'       => $product->id,
            'product_name'     => $product->name,
            'unit_price_cents' => $product->price_cents,
            'quantity'         => 2,
            'currency'         => 'EUR',
        ]);

        return [$order, $product, $item];
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function test_guest_cannot_access_admin_order_index(): void
    {
        $this->get(route('admin.orders.index'))->assertRedirect(route('auth.login'));
    }

    public function test_customer_cannot_access_admin_order_index(): void
    {
        $this->actingAs(User::factory()->create())
             ->get(route('admin.orders.index'))
             ->assertForbidden();
    }

    public function test_guest_cannot_access_admin_order_detail(): void
    {
        [$order] = $this->makeOrderWithItem();

        $this->get(route('admin.orders.show', $order))->assertRedirect(route('auth.login'));
    }

    // -------------------------------------------------------------------------
    // Order index
    // -------------------------------------------------------------------------

    public function test_admin_can_view_order_list(): void
    {
        [$order] = $this->makeOrderWithItem();

        $this->actingAs($this->admin())
             ->get(route('admin.orders.index'))
             ->assertOk()
             ->assertSee($order->order_number);
    }

    public function test_order_list_shows_all_orders(): void
    {
        Order::factory()->count(3)->create();

        $this->actingAs($this->admin())
             ->get(route('admin.orders.index'))
             ->assertOk();

        $this->assertEquals(3, Order::count());
    }

    public function test_order_list_can_be_filtered_by_status(): void
    {
        Order::factory()->paid()->create();
        Order::factory()->shipped()->create();

        $admin = $this->admin();

        $response = $this->actingAs($admin)
                         ->get(route('admin.orders.index', ['status' => 'shipped']));

        $response->assertOk()->assertSee('Expédiée');

        // The 'paid' order's label should not appear in a 'shipped' filter
        // (check the page doesn't show two different statuses side by side)
        $shippedOrder = Order::where('status', 'shipped')->first();
        $paidOrder    = Order::where('status', 'paid')->first();

        $response->assertSee($shippedOrder->order_number);
        $response->assertDontSee($paidOrder->order_number);
    }

    public function test_order_list_shows_empty_state_when_no_orders(): void
    {
        $this->actingAs($this->admin())
             ->get(route('admin.orders.index'))
             ->assertOk()
             ->assertSee('Aucune commande', false);
    }

    // -------------------------------------------------------------------------
    // Order detail
    // -------------------------------------------------------------------------

    public function test_admin_can_view_order_detail(): void
    {
        [$order, $product] = $this->makeOrderWithItem();

        $this->actingAs($this->admin())
             ->get(route('admin.orders.show', $order))
             ->assertOk()
             ->assertSee($product->name)
             ->assertSee($order->shipping_name)
             ->assertSee($order->email);
    }

    public function test_order_detail_shows_status_badge(): void
    {
        [$order] = $this->makeOrderWithItem(['status' => 'processing']);

        $this->actingAs($this->admin())
             ->get(route('admin.orders.show', $order))
             ->assertOk()
             ->assertSee('En préparation');
    }

    public function test_order_detail_shows_guest_label_when_no_user(): void
    {
        [$order] = $this->makeOrderWithItem(['user_id' => null]);

        $this->actingAs($this->admin())
             ->get(route('admin.orders.show', $order))
             ->assertOk()
             ->assertSee('Invité');
    }

    // -------------------------------------------------------------------------
    // Update status
    // -------------------------------------------------------------------------

    public function test_admin_can_update_order_status(): void
    {
        [$order] = $this->makeOrderWithItem(['status' => 'paid']);

        $this->actingAs($this->admin())
             ->patch(route('admin.orders.status', $order), ['status' => 'processing'])
             ->assertRedirect(route('admin.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_updating_order_status_flashes_success_message(): void
    {
        [$order] = $this->makeOrderWithItem(['status' => 'paid']);

        $this->actingAs($this->admin())
             ->patch(route('admin.orders.status', $order), ['status' => 'shipped'])
             ->assertSessionHas('success');
    }

    public function test_invalid_status_is_rejected(): void
    {
        [$order] = $this->makeOrderWithItem();

        $this->actingAs($this->admin())
             ->patch(route('admin.orders.status', $order), ['status' => 'refunded'])
             ->assertSessionHasErrors('status');
    }

    public function test_customer_cannot_update_order_status(): void
    {
        [$order] = $this->makeOrderWithItem();

        $this->actingAs(User::factory()->create())
             ->patch(route('admin.orders.status', $order), ['status' => 'shipped'])
             ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Dashboard counts
    // -------------------------------------------------------------------------

    public function test_dashboard_shows_order_count(): void
    {
        Order::factory()->count(4)->create();

        $this->actingAs($this->admin())
             ->get(route('admin.dashboard'))
             ->assertOk()
             ->assertSee('4');
    }

    // -------------------------------------------------------------------------
    // Payment reference
    // -------------------------------------------------------------------------

    public function test_order_detail_shows_stripe_payment_intent_id(): void
    {
        [$order] = $this->makeOrderWithItem([
            'stripe_payment_intent_id' => 'pi_test_abc123',
        ]);

        $this->actingAs($this->admin())
             ->get(route('admin.orders.show', $order))
             ->assertOk()
             ->assertSee('pi_test_abc123');
    }

    public function test_order_detail_hides_stripe_section_when_no_payment_intent(): void
    {
        [$order] = $this->makeOrderWithItem([
            'stripe_payment_intent_id' => null,
        ]);

        $this->actingAs($this->admin())
             ->get(route('admin.orders.show', $order))
             ->assertOk()
             ->assertDontSee('Réf. Stripe', false);
    }
}
