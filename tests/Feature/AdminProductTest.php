<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    // ── Access control ───────────────────────────────────────────────────────

    public function test_guest_cannot_access_product_index(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('auth.login'));
    }

    public function test_customer_cannot_access_product_index(): void
    {
        $this->actingAs($this->customer())
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    // ── Index ────────────────────────────────────────────────────────────────

    public function test_admin_sees_product_list(): void
    {
        $product = Product::factory()->create(['name' => 'Ciel Ardoise']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertViewIs('admin.products.index')
            ->assertSee('Ciel Ardoise');
    }

    public function test_admin_sees_empty_state_when_no_products(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Aucun produit', false);
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function test_admin_can_access_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertViewIs('admin.products.create');
    }

    public function test_admin_can_create_a_product(): void
    {
        $category = ProductCategory::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name'                => 'Toile Soleil',
                'slug'                => 'toile-soleil',
                'description'         => 'Un t-shirt lumineux.',
                'price'               => '29.99',
                'currency'            => 'EUR',
                'is_featured'         => '1',
                'product_category_id' => $category->id,
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'slug'        => 'toile-soleil',
            'price_cents' => 2999,
            'is_featured' => true,
        ]);
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [])
            ->assertSessionHasErrors(['name', 'slug', 'description', 'price', 'currency']);
    }

    public function test_store_rejects_duplicate_slug(): void
    {
        Product::factory()->create(['slug' => 'existing-slug']);

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name'        => 'Another',
                'slug'        => 'existing-slug',
                'description' => 'desc',
                'price'       => '10.00',
                'currency'    => 'EUR',
            ])
            ->assertSessionHasErrors(['slug']);
    }

    public function test_store_rejects_invalid_slug_format(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name'        => 'Bad Slug',
                'slug'        => 'Bad Slug!!',
                'description' => 'desc',
                'price'       => '10.00',
                'currency'    => 'EUR',
            ])
            ->assertSessionHasErrors(['slug']);
    }

    // ── Edit / Update ────────────────────────────────────────────────────────

    public function test_admin_can_access_edit_form(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertViewIs('admin.products.edit')
            ->assertSee($product->name);
    }

    public function test_admin_can_update_a_product(): void
    {
        $product = Product::factory()->create([
            'name'        => 'Old Name',
            'slug'        => 'old-name',
            'price_cents' => 1000,
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.products.update', $product), [
                'name'        => 'New Name',
                'slug'        => 'new-name',
                'description' => 'Updated desc.',
                'price'       => '45.00',
                'currency'    => 'EUR',
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id'          => $product->id,
            'name'        => 'New Name',
            'slug'        => 'new-name',
            'price_cents' => 4500,
        ]);
    }

    public function test_update_allows_same_slug_on_self(): void
    {
        $product = Product::factory()->create(['slug' => 'my-slug']);

        $this->actingAs($this->admin())
            ->patch(route('admin.products.update', $product), [
                'name'        => 'My Product',
                'slug'        => 'my-slug',
                'description' => 'desc',
                'price'       => '10.00',
                'currency'    => 'EUR',
            ])
            ->assertRedirect(route('admin.products.index'));
    }

    public function test_update_rejects_slug_already_used_by_another_product(): void
    {
        Product::factory()->create(['slug' => 'taken']);
        $product = Product::factory()->create(['slug' => 'mine']);

        $this->actingAs($this->admin())
            ->patch(route('admin.products.update', $product), [
                'name'        => 'Mine',
                'slug'        => 'taken',
                'description' => 'desc',
                'price'       => '10.00',
                'currency'    => 'EUR',
            ])
            ->assertSessionHasErrors(['slug']);
    }

    // ── Destroy ──────────────────────────────────────────────────────────────

    public function test_admin_can_delete_a_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_customer_cannot_delete_a_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->customer())
            ->delete(route('admin.products.destroy', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // ── Stock management ─────────────────────────────────────────────────────

    public function test_admin_can_create_product_with_stock_tracking_enabled(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name'           => 'Stock Tracker',
                'slug'           => 'stock-tracker',
                'description'    => 'Un produit dont on suit le stock.',
                'price'          => '25.00',
                'currency'       => 'EUR',
                'track_stock'    => '1',
                'stock_quantity' => '42',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'slug'           => 'stock-tracker',
            'track_stock'    => true,
            'stock_quantity' => 42,
        ]);
    }

    public function test_admin_can_create_product_without_stock_tracking(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name'        => 'Untracked Product',
                'slug'        => 'untracked-product',
                'description' => 'Stock non suivi.',
                'price'       => '20.00',
                'currency'    => 'EUR',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'slug'           => 'untracked-product',
            'track_stock'    => false,
            'stock_quantity' => 0,
        ]);
    }

    public function test_admin_can_update_product_stock_quantity(): void
    {
        $product = Product::factory()->inStock(10)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.products.update', $product), [
                'name'           => $product->name,
                'slug'           => $product->slug,
                'description'    => $product->description,
                'price'          => number_format($product->price_cents / 100, 2, '.', ''),
                'currency'       => $product->currency,
                'track_stock'    => '1',
                'stock_quantity' => '99',
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'track_stock'    => true,
            'stock_quantity' => 99,
        ]);
    }

    public function test_disabling_track_stock_resets_quantity_to_zero(): void
    {
        $product = Product::factory()->inStock(15)->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.products.update', $product), [
                'name'        => $product->name,
                'slug'        => $product->slug,
                'description' => $product->description,
                'price'       => number_format($product->price_cents / 100, 2, '.', ''),
                'currency'    => $product->currency,
                // track_stock checkbox not sent = false
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'id'             => $product->id,
            'track_stock'    => false,
            'stock_quantity' => 0,
        ]);
    }

    public function test_product_index_shows_out_of_stock_badge(): void
    {
        Product::factory()->outOfStock()->create(['name' => 'Rupture Test']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Rupture', false);
    }

    public function test_product_index_shows_low_stock_badge(): void
    {
        Product::factory()->lowStock(3)->create(['name' => 'Low Stock Test']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('3 restant(s)', false);
    }
}
