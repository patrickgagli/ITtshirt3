<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_when_visiting_wishlist(): void
    {
        $this->get(route('wishlist.index'))
             ->assertRedirect(route('auth.login'));
    }

    public function test_guest_is_redirected_when_adding_to_wishlist(): void
    {
        $product = Product::factory()->create();

        $this->post(route('wishlist.items.store'), ['product_id' => $product->id])
             ->assertRedirect(route('auth.login'));
    }

    // -------------------------------------------------------------------------
    // Viewing the wishlist
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_empty_wishlist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('wishlist.index'))
             ->assertOk()
             ->assertViewIs('wishlist.index')
             ->assertSee('Votre liste de souhaits est vide', false);
    }

    public function test_wishlist_shows_the_users_saved_products(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create([
            'name'        => 'Heritage Cotton Luxe',
            'slug'        => 'heritage-cotton-luxe',
            'price_cents' => 4490,
            'currency'    => 'EUR',
        ]);

        WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
             ->get(route('wishlist.index'))
             ->assertOk()
             ->assertSee('Heritage Cotton Luxe');
    }

    public function test_wishlist_does_not_show_another_users_items(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Secret Tee', 'slug' => 'secret-tee']);

        WishlistItem::create(['user_id' => $owner->id, 'product_id' => $product->id]);

        $this->actingAs($other)
             ->get(route('wishlist.index'))
             ->assertOk()
             ->assertDontSee('Secret Tee');
    }

    // -------------------------------------------------------------------------
    // Adding products
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_add_a_product_to_wishlist(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
             ->post(route('wishlist.items.store'), ['product_id' => $product->id])
             ->assertRedirect();

        $this->assertDatabaseHas('wishlist_items', [
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_adding_the_same_product_twice_does_not_duplicate_it(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
             ->post(route('wishlist.items.store'), ['product_id' => $product->id]);

        $this->assertDatabaseCount('wishlist_items', 1);
    }

    public function test_adding_a_nonexistent_product_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('wishlist.items.store'), ['product_id' => 99999])
             ->assertSessionHasErrors('product_id');

        $this->assertDatabaseCount('wishlist_items', 0);
    }

    // -------------------------------------------------------------------------
    // Removing products
    // -------------------------------------------------------------------------

    public function test_user_can_remove_their_own_wishlist_item(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();
        $item    = WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
             ->delete(route('wishlist.items.destroy', $item))
             ->assertRedirect();

        $this->assertDatabaseMissing('wishlist_items', ['id' => $item->id]);
    }

    public function test_user_cannot_remove_another_users_wishlist_item(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $product = Product::factory()->create();
        $item    = WishlistItem::create(['user_id' => $owner->id, 'product_id' => $product->id]);

        $this->actingAs($other)
             ->delete(route('wishlist.items.destroy', $item))
             ->assertForbidden();

        $this->assertDatabaseHas('wishlist_items', ['id' => $item->id]);
    }

    // -------------------------------------------------------------------------
    // Moving to cart
    // -------------------------------------------------------------------------

    public function test_user_can_move_a_wishlist_item_to_the_cart(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();
        $item    = WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
             ->post(route('wishlist.items.move-to-cart', $item))
             ->assertRedirect(route('cart.index'));

        // Removed from wishlist
        $this->assertDatabaseMissing('wishlist_items', ['id' => $item->id]);

        // Added to cart
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);
    }

    public function test_moving_to_cart_increments_quantity_if_product_already_in_cart(): void
    {
        $user      = User::factory()->create();
        $product   = Product::factory()->create();
        $sessionId = 'test-session-move';

        CartItem::create([
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $item = WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
             ->withSession(['cart_session_id' => $sessionId])
             ->post(route('wishlist.items.move-to-cart', $item));

        $this->assertDatabaseHas('cart_items', [
            'session_id' => $sessionId,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_user_cannot_move_another_users_wishlist_item_to_cart(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $product = Product::factory()->create();
        $item    = WishlistItem::create(['user_id' => $owner->id, 'product_id' => $product->id]);

        $this->actingAs($other)
             ->post(route('wishlist.items.move-to-cart', $item))
             ->assertForbidden();

        $this->assertDatabaseHas('wishlist_items', ['id' => $item->id]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    // -------------------------------------------------------------------------
    // Product show page integration
    // -------------------------------------------------------------------------

    public function test_product_show_displays_add_to_wishlist_button_for_authenticated_user(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'test-tee', 'name' => 'Test Tee']);

        $this->actingAs($user)
             ->get(route('products.show', $product->slug))
             ->assertOk()
             ->assertSee('data-vue="wishlist-toggle"', false)
             ->assertSee('"inWishlist":false');
    }

    public function test_product_show_displays_remove_from_wishlist_when_already_saved(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'test-tee', 'name' => 'Test Tee']);

        WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
             ->get(route('products.show', $product->slug))
             ->assertOk()
             ->assertSee('data-vue="wishlist-toggle"', false)
             ->assertSee('"inWishlist":true');
    }

    public function test_product_show_displays_login_link_for_wishlist_as_guest(): void
    {
        $product = Product::factory()->create(['slug' => 'test-tee', 'name' => 'Test Tee']);

        $this->get(route('products.show', $product->slug))
             ->assertOk()
             ->assertSee('data-vue="wishlist-toggle"', false)
             ->assertSee('"authenticated":false');
    }

    // -------------------------------------------------------------------------
    // JSON responses for Vue AJAX calls
    // -------------------------------------------------------------------------

    public function test_adding_to_wishlist_via_ajax_returns_json(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('wishlist.items.store'), ['product_id' => $product->id]);

        $response
            ->assertOk()
            ->assertJsonStructure(['message', 'wishlist_count', 'wishlist_item_id'])
            ->assertJsonFragment(['wishlist_count' => 1]);
    }

    public function test_removing_from_wishlist_via_ajax_returns_json(): void
    {
        $user    = User::factory()->create();
        $product = Product::factory()->create();
        $item    = WishlistItem::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $response = $this->actingAs($user)
            ->deleteJson(route('wishlist.items.destroy', $item));

        $response
            ->assertOk()
            ->assertJsonStructure(['message', 'wishlist_count'])
            ->assertJsonFragment(['wishlist_count' => 0]);

        $this->assertDatabaseMissing('wishlist_items', ['id' => $item->id]);
    }
}
