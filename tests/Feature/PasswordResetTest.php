<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    // ── Forgot-password form ─────────────────────────────────────────────────

    public function test_guest_can_view_forgot_password_form(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertViewIs('auth.forgot-password');
    }

    public function test_authenticated_user_is_redirected_from_forgot_password_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('password.request'))
            ->assertRedirect(route('home'));
    }

    // ── Send reset link ──────────────────────────────────────────────────────

    public function test_reset_link_is_sent_for_existing_email(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'alice@example.com']);

        $this->post(route('password.email'), ['email' => 'alice@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_reset_link_request_for_unknown_email_shows_same_status(): void
    {
        // Deliberately does not reveal whether the email exists (security best practice)
        $this->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_reset_link_request_validates_email_format(): void
    {
        $this->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors(['email']);
    }

    public function test_reset_link_request_requires_email(): void
    {
        $this->post(route('password.email'), [])
            ->assertSessionHasErrors(['email']);
    }

    // ── Reset-password form ──────────────────────────────────────────────────

    public function test_guest_can_view_reset_password_form(): void
    {
        $this->get(route('password.reset', ['token' => 'some-token']) . '?email=alice@example.com')
            ->assertOk()
            ->assertViewIs('auth.reset-password');
    }

    public function test_authenticated_user_is_redirected_from_reset_password_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('password.reset', ['token' => 'some-token']))
            ->assertRedirect(route('home'));
    }

    // ── Reset password ───────────────────────────────────────────────────────

    public function test_user_can_reset_password_with_valid_token(): void
    {
        Event::fake();

        $user = User::factory()->create(['email' => 'alice@example.com']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'alice@example.com',
            'password'              => 'new-secure-pass',
            'password_confirmation' => 'new-secure-pass',
        ])
        ->assertRedirect(route('auth.login'))
        ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-secure-pass', $user->fresh()->password));
        Event::assertDispatched(PasswordReset::class);
    }

    public function test_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.com']);

        $this->post(route('password.update'), [
            'token'                 => 'invalid-token',
            'email'                 => 'alice@example.com',
            'password'              => 'new-secure-pass',
            'password_confirmation' => 'new-secure-pass',
        ])
        ->assertSessionHasErrors(['email']);
    }

    public function test_reset_fails_when_password_too_short(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.com']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'alice@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors(['password']);
    }

    public function test_reset_fails_when_passwords_do_not_match(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.com']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => 'alice@example.com',
            'password'              => 'new-secure-pass',
            'password_confirmation' => 'different-pass',
        ])
        ->assertSessionHasErrors(['password']);
    }

    public function test_reset_requires_all_fields(): void
    {
        $this->post(route('password.update'), [])
            ->assertSessionHasErrors(['token', 'email', 'password']);
    }
}
