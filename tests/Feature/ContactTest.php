<?php

namespace Tests\Feature;

use App\Mail\ContactInquiry;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    // ── Contact page display ─────────────────────────────────────────────────

    public function test_contact_page_is_accessible_to_guests(): void
    {
        $this->get(route('contact.show'))
            ->assertOk()
            ->assertViewIs('contact.show');
    }

    public function test_contact_page_is_accessible_to_authenticated_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('contact.show'))
            ->assertOk()
            ->assertViewIs('contact.show');
    }

    public function test_contact_page_shows_form_fields(): void
    {
        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('contact-name',    false)
            ->assertSee('contact-email',   false)
            ->assertSee('contact-subject', false)
            ->assertSee('contact-message', false);
    }

    public function test_contact_page_shows_contact_info(): void
    {
        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('Contactez-nous', false)
            ->assertSee('Informations de contact', false);
    }

    public function test_contact_page_pre_fills_name_and_email_for_authenticated_users(): void
    {
        $user = User::factory()->create(['name' => 'Alice Martin', 'email' => 'alice@example.com']);

        $response = $this->actingAs($user)->get(route('contact.show'));

        $response->assertOk();
        $prefill = $response->viewData('prefill');
        $this->assertSame('Alice Martin', $prefill['name']);
        $this->assertSame('alice@example.com', $prefill['email']);
    }

    public function test_contact_page_prefill_is_empty_for_guests(): void
    {
        $response = $this->get(route('contact.show'));

        $response->assertOk();
        $prefill = $response->viewData('prefill');
        $this->assertSame('', $prefill['name']);
        $this->assertSame('', $prefill['email']);
    }

    // ── Form submission – valid data ─────────────────────────────────────────

    public function test_valid_submission_sends_mailable_and_redirects(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => 'Bob Dupont',
            'email'   => 'bob@example.com',
            'subject' => 'Question sur un produit',
            'message' => 'Bonjour, je voudrais en savoir plus sur vos t-shirts.',
        ])
            ->assertRedirect(route('contact.show'))
            ->assertSessionHas('success');

        Mail::assertSent(ContactInquiry::class, function (ContactInquiry $mail) {
            return $mail->inquiry['name']    === 'Bob Dupont'
                && $mail->inquiry['email']   === 'bob@example.com'
                && $mail->inquiry['subject'] === 'Question sur un produit';
        });
    }

    public function test_valid_submission_by_authenticated_user(): void
    {
        Mail::fake();

        $user = User::factory()->create(['name' => 'Claire', 'email' => 'claire@example.com']);

        $this->actingAs($user)
            ->post(route('contact.store'), [
                'name'    => 'Claire',
                'email'   => 'claire@example.com',
                'subject' => 'Partenariat',
                'message' => 'Je suis intéressée par un partenariat commercial.',
            ])
            ->assertRedirect(route('contact.show'));

        Mail::assertSentCount(1);
    }

    // ── Form submission – validation failures ────────────────────────────────

    public function test_submission_fails_when_name_is_missing(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'email'   => 'bob@example.com',
            'subject' => 'Autre',
            'message' => 'Un message de test suffisamment long.',
        ])
            ->assertSessionHasErrors('name');

        Mail::assertNothingSent();
    }

    public function test_submission_fails_when_email_is_invalid(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => 'Bob',
            'email'   => 'not-an-email',
            'subject' => 'Autre',
            'message' => 'Un message de test suffisamment long.',
        ])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_submission_fails_when_subject_is_missing(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => 'Bob',
            'email'   => 'bob@example.com',
            'message' => 'Un message de test suffisamment long.',
        ])
            ->assertSessionHasErrors('subject');

        Mail::assertNothingSent();
    }

    public function test_submission_fails_when_message_is_too_short(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => 'Bob',
            'email'   => 'bob@example.com',
            'subject' => 'Autre',
            'message' => 'Court',   // under 10 chars
        ])
            ->assertSessionHasErrors('message');

        Mail::assertNothingSent();
    }

    public function test_submission_fails_when_message_exceeds_max_length(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => 'Bob',
            'email'   => 'bob@example.com',
            'subject' => 'Autre',
            'message' => str_repeat('a', 2001),
        ])
            ->assertSessionHasErrors('message');

        Mail::assertNothingSent();
    }

    public function test_submission_fails_when_name_exceeds_max_length(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => str_repeat('x', 101),
            'email'   => 'bob@example.com',
            'subject' => 'Autre',
            'message' => 'Un message de test suffisamment long.',
        ])
            ->assertSessionHasErrors('name');

        Mail::assertNothingSent();
    }

    // ── Mailable internals ───────────────────────────────────────────────────

    public function test_mailable_subject_is_prefixed(): void
    {
        $mailable = new ContactInquiry([
            'name'    => 'Test User',
            'email'   => 'test@example.com',
            'subject' => 'Problème technique',
            'message' => 'Détails du problème.',
        ]);

        $this->assertStringContainsString('[Contact]', $mailable->envelope()->subject);
        $this->assertStringContainsString('Problème technique', $mailable->envelope()->subject);
    }

    public function test_mailable_reply_to_is_sender_email(): void
    {
        $mailable = new ContactInquiry([
            'name'    => 'Test User',
            'email'   => 'reply@example.com',
            'subject' => 'Autre',
            'message' => 'Message.',
        ]);

        $replyTo = $mailable->envelope()->replyTo;
        $this->assertNotEmpty($replyTo);
        $this->assertSame('reply@example.com', $replyTo[0]->address);
    }

    public function test_mailable_uses_correct_view(): void
    {
        $mailable = new ContactInquiry([
            'name'    => 'Test User',
            'email'   => 'test@example.com',
            'subject' => 'Autre',
            'message' => 'Message de test.',
        ]);

        $this->assertSame('emails.contact-inquiry', $mailable->content()->view);
    }

    // ── Persistence ──────────────────────────────────────────────────────────

    public function test_valid_submission_persists_contact_submission(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => 'Denis Martin',
            'email'   => 'denis@example.com',
            'subject' => 'Question stock',
            'message' => 'Avez-vous ce produit en stock ?',
        ])->assertRedirect(route('contact.show'));

        $this->assertDatabaseHas('contact_submissions', [
            'name'    => 'Denis Martin',
            'email'   => 'denis@example.com',
            'subject' => 'Question stock',
        ]);
    }

    public function test_invalid_submission_does_not_persist(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name'    => '',
            'email'   => 'invalid-email',
            'subject' => '',
            'message' => 'Court',
        ])->assertSessionHasErrors();

        $this->assertDatabaseCount('contact_submissions', 0);
    }
}
