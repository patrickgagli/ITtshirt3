<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Access control – guests can browse the cart; checkout requires auth
    // -------------------------------------------------------------------------

    public function test_guest_can_view_cart_page(): void
    {
        $this->get(route('cart.index'))
             ->assertOk()
             ->assertViewIs('cart.index');
    }

    public function test_guest_can_add_to_cart(): void
    {
        $product = Product::factory()->create();

        $this->post(route('cart.items.store'), ['product_id' => $product->id])
             ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_guest_can_view_cart_checkout_review(): void
    {
        $product = Product::factory()->create([
            'name'        => 'Guest Tee',
            'slug'        => 'guest-tee',
            'price_cents' => 2500,
            'currency'    => 'EUR',
        ]);
        $sessionId = 'guest-session-checkout';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->withSession(['cart_session_id' => $sessionId])
             ->get(route('cart.checkout'))
             ->assertOk()
             ->assertViewIs('cart.checkout')
             ->assertSee('Guest Tee')
             ->assertSee('Se connecter pour passer la commande', false);
    }

    public function test_guest_checkout_address_redirects_to_login(): void
    {
        $this->get(route('checkout.address'))
             ->assertRedirect(route('auth.login'));
    }

    public function test_cart_checkout_stores_intended_url_for_guests(): void
    {
        $product   = Product::factory()->create();
        $sessionId = 'guest-intended-session';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->withSession(['cart_session_id' => $sessionId])
             ->get(route('cart.checkout'));

        $this->assertEquals(route('checkout.address'), session('url.intended'));
    }

    public function test_guest_can_add_to_cart_via_ajax_returns_json(): void
    {
        $product = Product::factory()->create();

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id])
             ->assertOk()
             ->assertJsonStructure(['message', 'cart_count'])
             ->assertJsonFragment(['cart_count' => 1]);
    }

    public function test_guest_cannot_update_another_sessions_cart_item(): void
    {
        $product = Product::factory()->create();

        $item = CartItem::create([
            'session_id' => 'owner-session',
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->withSession(['cart_session_id' => 'intruder-session'])
             ->patch(route('cart.items.update', $item), ['quantity' => 10])
             ->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    // -------------------------------------------------------------------------
    // Viewing the cart
    // -------------------------------------------------------------------------

    public function test_cart_page_is_accessible_and_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertViewIs('cart.index')
            ->assertSee('Votre panier est vide', false);
    }

    public function test_cart_page_shows_items_in_session_cart(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create([
            'name'        => 'Classic White Tee',
            'slug'        => 'classic-white-tee',
            'price_cents' => 1990,
            'currency'    => 'EUR',
        ]);

        $sessionId = 'test-session-abc123';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Classic White Tee')
            ->assertSee('2');
    }

    // -------------------------------------------------------------------------
    // Adding products
    // -------------------------------------------------------------------------

    public function test_adding_a_product_creates_a_cart_item(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity'   => 1,
            ])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);
    }

    public function test_adding_the_same_product_increments_quantity(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-increment';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
                'quantity'   => 2,
            ]);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);
    }

    public function test_adding_a_nonexistent_product_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('cart.items.store'), ['product_id' => 99999])
            ->assertSessionHasErrors('product_id');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_quantity_defaults_to_one_when_omitted(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('cart.items.store'), ['product_id' => $product->id]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // Updating quantities
    // -------------------------------------------------------------------------

    public function test_quantity_can_be_updated(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-update';

        $item = CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->patch(route('cart.items.update', $item), ['quantity' => 5])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 5]);
    }

    public function test_quantity_update_requires_a_positive_integer(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-validate';

        $item = CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->patch(route('cart.items.update', $item), ['quantity' => 0])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    public function test_a_visitor_cannot_update_another_visitors_cart_item(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $item = CartItem::create([
            'session_id' => 'owner-session',
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        // Different session – the authenticated user still can't touch another session's item
        $this->actingAs($user)
            ->withSession(['cart_session_id' => 'intruder-session'])
            ->patch(route('cart.items.update', $item), ['quantity' => 10])
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    // -------------------------------------------------------------------------
    // Removing items
    // -------------------------------------------------------------------------

    public function test_a_cart_item_can_be_removed(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-remove';

        $item = CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->delete(route('cart.items.destroy', $item))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_a_visitor_cannot_remove_another_visitors_cart_item(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $item = CartItem::create([
            'session_id' => 'owner-session',
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => 'intruder-session'])
            ->delete(route('cart.items.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', ['id' => $item->id]);
    }

    // -------------------------------------------------------------------------
    // Checkout
    // -------------------------------------------------------------------------

    public function test_checkout_page_is_accessible_when_cart_has_items(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create([
            'name'        => 'Studio Sable',
            'slug'        => 'studio-sable',
            'price_cents' => 3400,
            'currency'    => 'EUR',
        ]);
        $sessionId = 'test-session-checkout';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->get(route('cart.checkout'))
            ->assertOk()
            ->assertViewIs('cart.checkout')
            ->assertSee('Studio Sable');
    }

    public function test_checkout_redirects_to_cart_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('cart.checkout'));

        $response->assertRedirect(route('cart.index'));
    }

    // -------------------------------------------------------------------------
    // Cart count in views
    // -------------------------------------------------------------------------

    public function test_cart_count_badge_reflects_items_in_session(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-count';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->get(route('cart.index'));

        $response->assertSee('>3<', false);
    }

    // -------------------------------------------------------------------------
    // JSON responses for Vue AJAX calls
    // -------------------------------------------------------------------------

    public function test_adding_a_product_via_ajax_returns_json_with_cart_count(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('cart.items.store'), ['product_id' => $product->id]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message', 'cart_count'])
            ->assertJsonFragment(['cart_count' => 1]);
    }

    public function test_updating_quantity_via_ajax_returns_json_with_new_count(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-json-update';

        $item = CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->patchJson(route('cart.items.update', $item), ['quantity' => 4]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message', 'cart_count', 'quantity'])
            ->assertJsonFragment(['quantity' => 4, 'cart_count' => 4]);
    }

    public function test_removing_a_product_via_ajax_returns_json_with_updated_count(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-json-remove';

        $item = CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->deleteJson(route('cart.items.destroy', $item));

        $response
            ->assertOk()
            ->assertJsonStructure(['message', 'cart_count'])
            ->assertJsonFragment(['cart_count' => 0]);

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    // -------------------------------------------------------------------------
    // Stock enforcement
    // -------------------------------------------------------------------------

    public function test_adding_an_out_of_stock_product_is_rejected(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->outOfStock()->create();

        $this->actingAs($user)
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_adding_more_than_available_stock_is_rejected(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->inStock(3)->create();

        $this->actingAs($user)
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 5])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_adding_respects_remaining_stock_when_item_already_in_cart(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->inStock(3)->create();
        $sessionId = 'test-session-stock-incr';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        // Only 1 unit remaining; requesting 2 more must be rejected.
        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_untracked_products_have_unlimited_stock(): void
    {
        $user    = User::factory()->create();
        // Default factory: track_stock = false.
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 99])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 99]);
    }

    public function test_updating_quantity_above_available_stock_is_rejected(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->inStock(4)->create();
        $sessionId = 'test-session-stock-update';

        $item = CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->actingAs($user)
            ->withSession(['cart_session_id' => $sessionId])
            ->patch(route('cart.items.update', $item), ['quantity' => 10])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    public function test_adding_out_of_stock_via_ajax_returns_422(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->outOfStock()->create();

        $this->actingAs($user)
            ->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }
}
