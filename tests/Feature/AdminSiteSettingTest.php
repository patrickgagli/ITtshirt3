<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSiteSettingTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    public function test_guest_cannot_access_settings(): void
    {
        $this->get(route('admin.settings.edit'))->assertRedirect(route('auth.login'));
    }

    public function test_customer_cannot_access_settings(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_guest_cannot_update_settings(): void
    {
        $this->patch(route('admin.settings.update'), ['site_name' => 'X'])->assertRedirect(route('auth.login'));
    }

    public function test_customer_cannot_update_settings(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)
             ->patch(route('admin.settings.update'), ['site_name' => 'X'])
             ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function test_admin_can_view_settings_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->get(route('admin.settings.edit'))
             ->assertOk()
             ->assertViewIs('admin.settings.edit');
    }

    public function test_settings_form_shows_current_values(): void
    {
        $admin    = User::factory()->create(['role' => 'admin']);
        $settings = SiteSetting::instance();
        $settings->update([
            'site_name'        => 'Ma Boutique',
            'meta_description' => 'Les meilleurs t-shirts',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings.edit'));

        $response->assertSee('Ma Boutique')
                 ->assertSee('Les meilleurs t-shirts');
    }

    // -------------------------------------------------------------------------
    // Update – happy path
    // -------------------------------------------------------------------------

    public function test_admin_can_update_site_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->patch(route('admin.settings.update'), [
                 'site_name' => 'Nouvelle Boutique',
             ])
             ->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseHas('site_settings', ['site_name' => 'Nouvelle Boutique']);
    }

    public function test_admin_can_update_all_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'site_name'        => 'Ma Boutique',
            'tagline'          => 'La mode accessible',
            'meta_description' => 'Boutique de t-shirts.',
            'meta_keywords'    => 't-shirt, mode',
            'og_image_url'     => 'https://example.com/og.jpg',
            'social_facebook'  => 'https://facebook.com/maboutique',
            'social_instagram' => 'https://instagram.com/maboutique',
            'social_twitter'   => 'https://twitter.com/maboutique',
            'social_tiktok'    => 'https://tiktok.com/@maboutique',
            'twitter_handle'   => 'maboutique',
        ])->assertRedirect(route('admin.settings.edit'));

        $fresh = SiteSetting::instance();
        $this->assertSame('Ma Boutique', $fresh->site_name);
        $this->assertSame('La mode accessible', $fresh->tagline);
        $this->assertSame('maboutique', $fresh->twitter_handle);
        $this->assertSame('https://facebook.com/maboutique', $fresh->social_facebook);
    }

    public function test_update_flashes_success(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->patch(route('admin.settings.update'), ['site_name' => 'X'])
             ->assertSessionHas('success');
    }

    // -------------------------------------------------------------------------
    // Update – validation
    // -------------------------------------------------------------------------

    public function test_site_name_is_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->patch(route('admin.settings.update'), ['site_name' => ''])
             ->assertSessionHasErrors('site_name');
    }

    public function test_site_name_max_length(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->patch(route('admin.settings.update'), ['site_name' => str_repeat('a', 101)])
             ->assertSessionHasErrors('site_name');
    }

    public function test_og_image_url_must_be_url(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->patch(route('admin.settings.update'), [
                 'site_name'    => 'X',
                 'og_image_url' => 'not-a-url',
             ])
             ->assertSessionHasErrors('og_image_url');
    }

    public function test_social_links_must_be_urls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->patch(route('admin.settings.update'), [
                 'site_name'       => 'X',
                 'social_facebook' => 'not-a-url',
             ])
             ->assertSessionHasErrors('social_facebook');
    }

    public function test_optional_fields_can_be_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->patch(route('admin.settings.update'), [
                 'site_name'        => 'X',
                 'meta_description' => '',
                 'social_facebook'  => '',
             ])
             ->assertRedirect(route('admin.settings.edit'))
             ->assertSessionMissing('errors');
    }

    // -------------------------------------------------------------------------
    // Public views use site settings
    // -------------------------------------------------------------------------

    public function test_home_page_shows_site_name_in_title(): void
    {
        SiteSetting::instance()->update(['site_name' => 'SuperShop']);

        $this->get(route('home'))->assertSee('<title>SuperShop', false);
    }

    public function test_catalogue_page_shows_site_name_in_title(): void
    {
        SiteSetting::instance()->update(['site_name' => 'SuperShop']);

        $this->get(route('products.index'))->assertSee('<title>SuperShop', false);
    }

    public function test_home_page_shows_og_description_when_set(): void
    {
        SiteSetting::instance()->update([
            'site_name'        => 'X',
            'meta_description' => 'Boutique de t-shirts.',
        ]);

        $this->get(route('home'))->assertSee('Boutique de t-shirts.', false);
    }

    public function test_dashboard_has_settings_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->get(route('admin.dashboard'))
             ->assertSee(route('admin.settings.edit'), false);
    }
}
