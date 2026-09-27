<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountOrderTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeUserWithOrder(array $orderState = []): array
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['price_cents' => 2000]);
        $order   = Order::factory()->forUser($user)->create($orderState);
        OrderItem::create([
            'order_id'         => $order->id,
            'product_id'       => $product->id,
            'product_name'     => $product->name,
            'unit_price_cents' => $product->price_cents,
            'quantity'         => 1,
            'currency'         => 'EUR',
        ]);

        return [$user, $order, $product];
    }

    // -------------------------------------------------------------------------
    // Account show — orders section
    // -------------------------------------------------------------------------

    public function test_account_show_displays_recent_orders(): void
    {
        [$user, $order] = $this->makeUserWithOrder();

        $this->actingAs($user)
             ->get(route('account.show'))
             ->assertOk()
             ->assertSee($order->order_number);
    }

    public function test_account_show_displays_empty_state_when_no_orders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('account.show'))
             ->assertOk()
             ->assertSee('Aucune commande', false);
    }

    // -------------------------------------------------------------------------
    // Order list (account.orders.index)
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_order_list(): void
    {
        $this->get(route('account.orders.index'))
             ->assertRedirect(route('auth.login'));
    }

    public function test_user_can_view_own_order_list(): void
    {
        [$user, $order] = $this->makeUserWithOrder();

        $this->actingAs($user)
             ->get(route('account.orders.index'))
             ->assertOk()
             ->assertSee($order->order_number);
    }

    public function test_user_does_not_see_other_users_orders(): void
    {
        [$userA, $orderA] = $this->makeUserWithOrder();
        $userB             = User::factory()->create();
        Order::factory()->forUser($userB)->create();

        $this->actingAs($userA)
             ->get(route('account.orders.index'))
             ->assertOk()
             ->assertSee($orderA->order_number);

        // userA's list should not show userB's order ref
        $orderB = Order::where('user_id', $userB->id)->first();
        $this->actingAs($userA)
             ->get(route('account.orders.index'))
             ->assertDontSee($orderB->order_number);
    }

    public function test_order_list_shows_empty_state_with_no_orders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('account.orders.index'))
             ->assertOk()
             ->assertSee('Aucune commande', false);
    }

    // -------------------------------------------------------------------------
    // Order detail (account.orders.show)
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_order_detail(): void
    {
        $order = Order::factory()->create();

        $this->get(route('account.orders.show', $order->uuid))
             ->assertRedirect(route('auth.login'));
    }

    public function test_user_can_view_own_order_detail(): void
    {
        [$user, $order, $product] = $this->makeUserWithOrder();

        $this->actingAs($user)
             ->get(route('account.orders.show', $order->uuid))
             ->assertOk()
             ->assertSee($product->name)
             ->assertSee($order->shipping_name);
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        [$userA]     = $this->makeUserWithOrder();
        [$userB, $orderB] = $this->makeUserWithOrder();

        $this->actingAs($userA)
             ->get(route('account.orders.show', $orderB->uuid))
             ->assertForbidden();
    }

    public function test_order_detail_shows_status_badge(): void
    {
        [$user, $order] = $this->makeUserWithOrder(['status' => 'shipped']);

        $this->actingAs($user)
             ->get(route('account.orders.show', $order->uuid))
             ->assertOk()
             ->assertSee('Expédiée');
    }

    public function test_order_detail_shows_formatted_total(): void
    {
        [$user, $order] = $this->makeUserWithOrder(['total_cents' => 4999]);

        $this->actingAs($user)
             ->get(route('account.orders.show', $order->uuid))
             ->assertOk()
             ->assertSee('49,99');
    }

    public function test_order_detail_shows_cancelled_state(): void
    {
        [$user, $order] = $this->makeUserWithOrder(['status' => 'cancelled']);

        $this->actingAs($user)
             ->get(route('account.orders.show', $order->uuid))
             ->assertOk()
             ->assertSee('Annulée');
    }
}
