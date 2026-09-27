<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
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

    public function test_guest_cannot_access_category_index(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('auth.login'));
    }

    public function test_customer_cannot_access_category_index(): void
    {
        $this->actingAs($this->customer())
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    // ── Index ────────────────────────────────────────────────────────────────

    public function test_admin_sees_category_list(): void
    {
        ProductCategory::factory()->create(['name' => 'Univers Sport']);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertViewIs('admin.categories.index')
            ->assertSee('Univers Sport');
    }

    public function test_admin_sees_empty_state_when_no_categories(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Aucune cat', false);
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function test_admin_can_access_create_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertViewIs('admin.categories.create');
    }

    public function test_admin_can_create_a_category(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name'        => 'Enfants',
                'slug'        => 'enfants',
                'description' => 'Collection pour les enfants.',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('product_categories', [
            'slug' => 'enfants',
            'name' => 'Enfants',
        ]);
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [])
            ->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_store_rejects_duplicate_slug(): void
    {
        ProductCategory::factory()->create(['slug' => 'existing']);

        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name' => 'Another',
                'slug' => 'existing',
            ])
            ->assertSessionHasErrors(['slug']);
    }

    public function test_store_rejects_invalid_slug_format(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name' => 'Bad',
                'slug' => 'Has Spaces!!',
            ])
            ->assertSessionHasErrors(['slug']);
    }

    // ── Edit / Update ────────────────────────────────────────────────────────

    public function test_admin_can_access_edit_form(): void
    {
        $category = ProductCategory::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertViewIs('admin.categories.edit')
            ->assertSee($category->name);
    }

    public function test_admin_can_update_a_category(): void
    {
        $category = ProductCategory::factory()->create([
            'name' => 'Old',
            'slug' => 'old',
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.categories.update', $category), [
                'name'        => 'Nouveau Nom',
                'slug'        => 'nouveau-nom',
                'description' => 'Description mise à jour.',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('product_categories', [
            'id'   => $category->id,
            'name' => 'Nouveau Nom',
            'slug' => 'nouveau-nom',
        ]);
    }

    public function test_update_allows_same_slug_on_self(): void
    {
        $category = ProductCategory::factory()->create(['slug' => 'my-slug']);

        $this->actingAs($this->admin())
            ->patch(route('admin.categories.update', $category), [
                'name' => 'My Category',
                'slug' => 'my-slug',
            ])
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_update_rejects_slug_already_used_by_another_category(): void
    {
        ProductCategory::factory()->create(['slug' => 'taken']);
        $category = ProductCategory::factory()->create(['slug' => 'mine']);

        $this->actingAs($this->admin())
            ->patch(route('admin.categories.update', $category), [
                'name' => 'Mine',
                'slug' => 'taken',
            ])
            ->assertSessionHasErrors(['slug']);
    }

    // ── Destroy ──────────────────────────────────────────────────────────────

    public function test_admin_can_delete_a_category(): void
    {
        $category = ProductCategory::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
    }

    public function test_deleting_category_nullifies_linked_products(): void
    {
        $category = ProductCategory::factory()->create();
        $product  = Product::factory()->create(['product_category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', [
            'id'                  => $product->id,
            'product_category_id' => null,
        ]);
    }

    public function test_customer_cannot_delete_a_category(): void
    {
        $category = ProductCategory::factory()->create();

        $this->actingAs($this->customer())
            ->delete(route('admin.categories.destroy', $category))
            ->assertForbidden();

        $this->assertDatabaseHas('product_categories', ['id' => $category->id]);
    }
}
