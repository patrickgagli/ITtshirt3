<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    // ── Subscribe ────────────────────────────────────────────────────────────

    public function test_new_email_is_subscribed_and_stored(): void
    {
        $this->post(route('newsletter.store'), ['email' => 'alice@example.com'])
            ->assertRedirect();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email'            => 'alice@example.com',
            'unsubscribed_at'  => null,
        ]);
    }

    public function test_subscription_generates_a_unique_token(): void
    {
        $this->post(route('newsletter.store'), ['email' => 'alice@example.com']);

        $subscriber = NewsletterSubscriber::where('email', 'alice@example.com')->firstOrFail();
        $this->assertNotEmpty($subscriber->token);
        $this->assertSame(64, strlen($subscriber->token));
    }

    public function test_optional_name_is_stored_when_provided(): void
    {
        $this->post(route('newsletter.store'), [
            'email' => 'alice@example.com',
            'name'  => 'Alice Martin',
        ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'alice@example.com',
            'name'  => 'Alice Martin',
        ]);
    }

    public function test_subscription_flashes_success_message(): void
    {
        $this->post(route('newsletter.store'), ['email' => 'alice@example.com'])
            ->assertSessionHas('newsletter_success');
    }

    public function test_subscribing_already_active_email_flashes_info_and_does_not_duplicate(): void
    {
        NewsletterSubscriber::factory()->create(['email' => 'alice@example.com']);

        $this->post(route('newsletter.store'), ['email' => 'alice@example.com'])
            ->assertSessionHas('newsletter_info');

        $this->assertSame(1, NewsletterSubscriber::where('email', 'alice@example.com')->count());
    }

    public function test_resubscribing_a_previously_unsubscribed_email_reactivates_it(): void
    {
        $subscriber = NewsletterSubscriber::factory()->unsubscribed()->create([
            'email' => 'alice@example.com',
        ]);

        $this->post(route('newsletter.store'), ['email' => 'alice@example.com'])
            ->assertSessionHas('newsletter_success');

        $subscriber->refresh();
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertSame(1, NewsletterSubscriber::where('email', 'alice@example.com')->count());
    }

    public function test_resubscription_refreshes_token(): void
    {
        $oldToken = NewsletterSubscriber::generateToken();
        $subscriber = NewsletterSubscriber::factory()->unsubscribed()->create([
            'email' => 'alice@example.com',
            'token' => $oldToken,
        ]);

        $this->post(route('newsletter.store'), ['email' => 'alice@example.com']);

        $subscriber->refresh();
        $this->assertNotSame($oldToken, $subscriber->token);
    }

    // ── Subscribe validation ─────────────────────────────────────────────────

    public function test_subscription_fails_without_email(): void
    {
        $this->post(route('newsletter.store'), [])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseEmpty('newsletter_subscribers');
    }

    public function test_subscription_fails_with_invalid_email(): void
    {
        $this->post(route('newsletter.store'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseEmpty('newsletter_subscribers');
    }

    public function test_subscription_fails_when_name_exceeds_max_length(): void
    {
        $this->post(route('newsletter.store'), [
            'email' => 'alice@example.com',
            'name'  => str_repeat('x', 101),
        ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseEmpty('newsletter_subscribers');
    }

    // ── Unsubscribe form ─────────────────────────────────────────────────────

    public function test_unsubscribe_form_renders_for_active_subscriber(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create();

        $this->get(route('newsletter.unsubscribe', $subscriber->token))
            ->assertOk()
            ->assertViewIs('newsletter.unsubscribe')
            ->assertSee($subscriber->email);
    }

    public function test_unsubscribe_form_shows_already_unsubscribed_message(): void
    {
        $subscriber = NewsletterSubscriber::factory()->unsubscribed()->create();

        $this->get(route('newsletter.unsubscribe', $subscriber->token))
            ->assertOk()
            ->assertSee($subscriber->email)
            ->assertSee('déjà désinscrite', false);
    }

    public function test_unsubscribe_form_returns_404_for_unknown_token(): void
    {
        $this->get(route('newsletter.unsubscribe', 'invalid-token-that-does-not-exist'))
            ->assertNotFound();
    }

    // ── Confirm unsubscribe ──────────────────────────────────────────────────

    public function test_confirming_unsubscribe_marks_subscriber_as_unsubscribed(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create();

        $this->post(route('newsletter.unsubscribe.confirm', $subscriber->token))
            ->assertRedirect(route('newsletter.unsubscribed'));

        $subscriber->refresh();
        $this->assertNotNull($subscriber->unsubscribed_at);
    }

    public function test_confirming_unsubscribe_on_already_unsubscribed_is_idempotent(): void
    {
        $subscriber = NewsletterSubscriber::factory()->unsubscribed()->create();
        $original   = $subscriber->unsubscribed_at;

        $this->post(route('newsletter.unsubscribe.confirm', $subscriber->token))
            ->assertRedirect(route('newsletter.unsubscribed'));

        // Timestamp should not have changed (already unsubscribed)
        $subscriber->refresh();
        $this->assertEquals($original->timestamp, $subscriber->unsubscribed_at->timestamp);
    }

    public function test_confirm_unsubscribe_returns_404_for_unknown_token(): void
    {
        $this->post(route('newsletter.unsubscribe.confirm', 'bad-token'))
            ->assertNotFound();
    }

    // ── Unsubscribed confirmation page ───────────────────────────────────────

    public function test_unsubscribed_page_is_accessible(): void
    {
        $this->get(route('newsletter.unsubscribed'))
            ->assertOk()
            ->assertViewIs('newsletter.unsubscribed');
    }

    // ── Home footer form integration ─────────────────────────────────────────

    public function test_home_page_shows_newsletter_form(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('newsletter.store'), false);
    }

    public function test_home_page_shows_success_flash_after_subscription(): void
    {
        $response = $this->post(route('newsletter.store'), ['email' => 'bob@example.com']);
        $response->assertRedirect();

        $this->followRedirects($response)
            ->assertSee(session('newsletter_success') ?? 'inscrit', false);
    }

    // ── Model helpers ────────────────────────────────────────────────────────

    public function test_is_active_returns_true_when_not_unsubscribed(): void
    {
        $subscriber = NewsletterSubscriber::factory()->create();
        $this->assertTrue($subscriber->isActive());
    }

    public function test_is_active_returns_false_when_unsubscribed(): void
    {
        $subscriber = NewsletterSubscriber::factory()->unsubscribed()->create();
        $this->assertFalse($subscriber->isActive());
    }

    public function test_generate_token_returns_64_character_string(): void
    {
        $token = NewsletterSubscriber::generateToken();
        $this->assertSame(64, strlen($token));
    }
}
