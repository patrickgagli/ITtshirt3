<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ── Registration ────────────────────────────────────────────────────────

    public function test_registration_form_is_accessible_to_guests(): void
    {
        $this->get(route('auth.register'))->assertOk()->assertViewIs('auth.register');
    }

    public function test_authenticated_user_is_redirected_away_from_register_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('auth.register'))
            ->assertRedirect(route('home'));
    }

    public function test_a_guest_can_register_with_valid_credentials(): void
    {
        $response = $this->post(route('auth.register.store'), [
            'name'                  => 'Marie Dupont',
            'email'                 => 'marie@example.com',
            'password'              => 'securepass1',
            'password_confirmation' => 'securepass1',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'marie@example.com',
            'role'  => 'customer',
        ]);
    }

    public function test_registration_requires_all_fields(): void
    {
        $this->post(route('auth.register.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post(route('auth.register.store'), [
            'name'                  => 'Jean',
            'email'                 => 'taken@example.com',
            'password'              => 'securepass1',
            'password_confirmation' => 'securepass1',
        ])->assertSessionHasErrors(['email']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->post(route('auth.register.store'), [
            'name'                  => 'Jean',
            'email'                 => 'jean@example.com',
            'password'              => 'securepass1',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['password']);
    }

    // ── Login ────────────────────────────────────────────────────────────────

    public function test_login_form_is_accessible_to_guests(): void
    {
        $this->get(route('auth.login'))->assertOk()->assertViewIs('auth.login');
    }

    public function test_authenticated_user_is_redirected_away_from_login_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('auth.login'))
            ->assertRedirect(route('home'));
    }

    public function test_a_guest_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->post(route('auth.login.store'), [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => 'correct']);

        $this->post(route('auth.login.store'), [
            'email'    => 'user@example.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    // ── Logout ───────────────────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('auth.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_guest_cannot_access_logout_route(): void
    {
        $this->post(route('auth.logout'))->assertRedirect(route('auth.login'));
    }

    // ── Role-based access control ────────────────────────────────────────────

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('admin.dashboard');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('auth.login'));
    }

    public function test_new_user_has_customer_role_by_default(): void
    {
        $this->post(route('auth.register.store'), [
            'name'                  => 'Luc',
            'email'                 => 'luc@example.com',
            'password'              => 'securepass1',
            'password_confirmation' => 'securepass1',
        ]);

        $user = User::where('email', 'luc@example.com')->sole();
        $this->assertTrue($user->isCustomer());
        $this->assertFalse($user->isAdmin());
    }
}
