<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    // ── Access control ───────────────────────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('account.show'))->assertRedirect(route('auth.login'));
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->patch(route('account.profile.update'), [])->assertRedirect(route('auth.login'));
    }

    public function test_guest_cannot_update_password(): void
    {
        $this->patch(route('account.password.update'), [])->assertRedirect(route('auth.login'));
    }

    // ── Profile display ──────────────────────────────────────────────────────

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create(['name' => 'Alice Martin']);

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertViewIs('account.show')
            ->assertSee('Alice Martin');
    }

    public function test_profile_page_shows_email_and_role(): void
    {
        $user = User::factory()->create([
            'email' => 'alice@example.com',
            'role'  => 'customer',
        ]);

        $this->actingAs($user)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('alice@example.com')
            ->assertSee('Client');
    }

    public function test_admin_profile_shows_admin_role_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('account.show'))
            ->assertOk()
            ->assertSee('Administrateur');
    }

    // ── Profile update ───────────────────────────────────────────────────────

    public function test_user_can_update_name_and_email_with_correct_password(): void
    {
        $user = User::factory()->create([
            'name'     => 'Old Name',
            'email'    => 'old@example.com',
            'password' => Hash::make('correct-pass'),
        ]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name'             => 'New Name',
                'email'            => 'new@example.com',
                'current_password' => 'correct-pass',
            ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('success_profile');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_profile_update_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct')]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name'             => 'Name',
                'email'            => $user->email,
                'current_password' => 'wrong-password',
            ])
            ->assertSessionHasErrorsIn('profile', ['current_password']);
    }

    public function test_profile_update_rejects_missing_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [])
            ->assertSessionHasErrorsIn('profile', ['name', 'email', 'current_password']);
    }

    public function test_profile_update_rejects_email_already_taken_by_another_user(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['password' => Hash::make('pass')]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name'             => $user->name,
                'email'            => 'taken@example.com',
                'current_password' => 'pass',
            ])
            ->assertSessionHasErrorsIn('profile', ['email']);
    }

    public function test_profile_update_allows_keeping_same_email(): void
    {
        $user = User::factory()->create([
            'email'    => 'mine@example.com',
            'password' => Hash::make('pass'),
        ]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name'             => $user->name,
                'email'            => 'mine@example.com',
                'current_password' => 'pass',
            ])
            ->assertRedirect(route('account.show'));
    }

    // ── Password update ──────────────────────────────────────────────────────

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($user)
            ->patch(route('account.password.update'), [
                'current_password'      => 'old-password',
                'password'              => 'new-secure-pass',
                'password_confirmation' => 'new-secure-pass',
            ])
            ->assertRedirect(route('account.show'))
            ->assertSessionHas('success_password');

        $this->assertTrue(Hash::check('new-secure-pass', $user->fresh()->password));
    }

    public function test_password_update_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct')]);

        $this->actingAs($user)
            ->patch(route('account.password.update'), [
                'current_password'      => 'wrong',
                'password'              => 'new-secure-pass',
                'password_confirmation' => 'new-secure-pass',
            ])
            ->assertSessionHasErrorsIn('password', ['current_password']);
    }

    public function test_password_update_rejects_new_password_too_short(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct')]);

        $this->actingAs($user)
            ->patch(route('account.password.update'), [
                'current_password'      => 'correct',
                'password'              => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrorsIn('password', ['password']);
    }

    public function test_password_update_rejects_unconfirmed_new_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct')]);

        $this->actingAs($user)
            ->patch(route('account.password.update'), [
                'current_password'      => 'correct',
                'password'              => 'new-secure-pass',
                'password_confirmation' => 'different-pass',
            ])
            ->assertSessionHasErrorsIn('password', ['password']);
    }

    public function test_password_update_rejects_missing_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.password.update'), [])
            ->assertSessionHasErrorsIn('password', ['current_password', 'password']);
    }
}
