<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    // ── Preferences page ─────────────────────────────────────────────────────

    public function test_preferences_page_is_accessible_without_authentication(): void
    {
        $this->get(route('cookies.show'))
            ->assertOk()
            ->assertViewIs('cookies.preferences');
    }

    public function test_preferences_page_shows_four_cookie_categories(): void
    {
        $this->get(route('cookies.show'))
            ->assertOk()
            ->assertSee('Cookies nécessaires', false)
            ->assertSee('Cookies analytiques', false)
            ->assertSee('Cookies marketing', false)
            ->assertSee('Cookies de préférences', false);
    }

    public function test_preferences_page_shows_gdpr_rights_section(): void
    {
        $this->get(route('cookies.show'))
            ->assertOk()
            ->assertSee('Vos droits RGPD', false)
            ->assertSee("Droit d'accès", false);
    }

    public function test_preferences_page_shows_unchecked_optional_categories_when_no_consent_cookie(): void
    {
        $response = $this->get(route('cookies.show'));

        $response->assertOk();
        // Consent object passed to view has all optionals false
        $consent = $response->viewData('consent');
        $this->assertFalse($consent['analytics']);
        $this->assertFalse($consent['marketing']);
        $this->assertFalse($consent['preferences']);
        $this->assertTrue($consent['necessary']);
    }

    public function test_preferences_page_reflects_existing_consent_cookie(): void
    {
        $consentJson = json_encode([
            'necessary'   => true,
            'analytics'   => true,
            'marketing'   => false,
            'preferences' => true,
        ]);

        $response = $this->withCookies(['cookie_consent' => $consentJson])
            ->get(route('cookies.show'));

        $response->assertOk();
        $consent = $response->viewData('consent');
        $this->assertTrue($consent['analytics']);
        $this->assertFalse($consent['marketing']);
        $this->assertTrue($consent['preferences']);
    }

    // ── Store consent ────────────────────────────────────────────────────────

    public function test_store_accepts_all_cookies_and_sets_cookie(): void
    {
        $response = $this->post(route('cookies.consent.store'), [
            'analytics'   => '1',
            'marketing'   => '1',
            'preferences' => '1',
        ]);

        $response->assertRedirect();
        $response->assertCookie('cookie_consent');

        $cookieValue = json_decode($response->getCookie('cookie_consent')->getValue(), true);
        $this->assertTrue($cookieValue['necessary']);
        $this->assertTrue($cookieValue['analytics']);
        $this->assertTrue($cookieValue['marketing']);
        $this->assertTrue($cookieValue['preferences']);
    }

    public function test_store_rejects_non_essential_cookies(): void
    {
        $response = $this->post(route('cookies.consent.store'), [
            'analytics'   => '0',
            'marketing'   => '0',
            'preferences' => '0',
        ]);

        $response->assertRedirect();
        $response->assertCookie('cookie_consent');

        $cookieValue = json_decode($response->getCookie('cookie_consent')->getValue(), true);
        $this->assertTrue($cookieValue['necessary']);
        $this->assertFalse($cookieValue['analytics']);
        $this->assertFalse($cookieValue['marketing']);
        $this->assertFalse($cookieValue['preferences']);
    }

    public function test_store_defaults_to_false_when_optional_fields_absent(): void
    {
        // No analytics/marketing/preferences sent (e.g. unchecked checkboxes)
        $response = $this->post(route('cookies.consent.store'), []);

        $response->assertRedirect();
        $response->assertCookie('cookie_consent');

        $cookieValue = json_decode($response->getCookie('cookie_consent')->getValue(), true);
        $this->assertTrue($cookieValue['necessary']);
        $this->assertFalse($cookieValue['analytics']);
        $this->assertFalse($cookieValue['marketing']);
        $this->assertFalse($cookieValue['preferences']);
    }

    public function test_store_redirects_to_custom_redirect_url(): void
    {
        $response = $this->post(route('cookies.consent.store'), [
            '_redirect' => route('home'),
        ]);

        $response->assertRedirect(route('home'));
    }

    public function test_store_rejects_cross_origin_redirect(): void
    {
        // Subdomain-prefix bypass: 'http://localhost.evil.com' starts with 'http://localhost'
        // but must NOT be accepted because it lacks the trailing slash guard.
        $response = $this->post(route('cookies.consent.store'), [
            '_redirect' => 'https://evil.com/steal',
        ]);

        $response->assertRedirect();
        $this->assertStringNotContainsString(
            'evil.com',
            $response->headers->get('Location') ?? ''
        );
    }

    public function test_store_rejects_subdomain_prefix_redirect_bypass(): void
    {
        // e.g. 'http://localhost.evil.com' starts with 'http://localhost' without trailing slash guard
        $appBase = rtrim(url('/'), '/');
        $bypass  = $appBase . '.evil.com/';

        $response = $this->post(route('cookies.consent.store'), [
            '_redirect' => $bypass,
        ]);

        $response->assertRedirect();
        $this->assertStringNotContainsString(
            'evil.com',
            $response->headers->get('Location') ?? ''
        );
    }

    public function test_store_flashes_success_session_flag(): void
    {
        $response = $this->post(route('cookies.consent.store'), [
            '_redirect' => route('cookies.show'),
        ]);

        $response->assertSessionHas('cookie_consent_saved', true);
    }

    // ── Withdraw consent ─────────────────────────────────────────────────────

    public function test_withdraw_clears_consent_cookie_and_redirects_to_preferences(): void
    {
        $response = $this->withCookies(['cookie_consent' => '{"necessary":true,"analytics":true,"marketing":true,"preferences":true}'])
            ->delete(route('cookies.consent.withdraw'));

        $response->assertRedirect(route('cookies.show'));
        $response->assertCookieExpired('cookie_consent');
    }

    public function test_withdraw_flashes_info_session_flag(): void
    {
        $response = $this->delete(route('cookies.consent.withdraw'));

        $response->assertSessionHas('cookie_consent_withdrawn', true);
    }

    // ── Cookie banner visibility ──────────────────────────────────────────────

    public function test_cookie_banner_is_shown_on_home_when_no_consent(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="cookie-banner"', false)
            ->assertSee('Tout accepter', false);
    }

    public function test_cookie_banner_is_not_shown_when_consent_cookie_is_set(): void
    {
        $this->withCookies(['cookie_consent' => '{"necessary":true,"analytics":false,"marketing":false,"preferences":false}'])
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="cookie-banner"', false);
    }

    public function test_cookie_banner_is_shown_on_catalogue_when_no_consent(): void
    {
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('id="cookie-banner"', false);
    }
}
