<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CookieConsentController extends Controller
{
    private const COOKIE_NAME    = 'cookie_consent';
    private const COOKIE_MINUTES = 60 * 24 * 365; // 1 year

    public function show(Request $request): View
    {
        $consent = $this->parseConsent($request->cookie(self::COOKIE_NAME));

        return view('cookies.preferences', ['consent' => $consent]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'analytics'   => ['nullable', 'boolean'],
            'marketing'   => ['nullable', 'boolean'],
            'preferences' => ['nullable', 'boolean'],
        ]);

        $consent = [
            'necessary'   => true,
            'analytics'   => (bool) $request->input('analytics', false),
            'marketing'   => (bool) $request->input('marketing', false),
            'preferences' => (bool) $request->input('preferences', false),
        ];

        $cookie = cookie(self::COOKIE_NAME, json_encode($consent), self::COOKIE_MINUTES, '/', null, null, false);

        $rawRedirect = $request->input('_redirect');
        $appBase    = rtrim(url('/'), '/') . '/';
        $redirectTo = (is_string($rawRedirect) && str_starts_with($rawRedirect, $appBase))
            ? $rawRedirect
            : url()->previous(route('home'));

        return redirect($redirectTo)
            ->with('cookie_consent_saved', true)
            ->withCookie($cookie);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        return redirect()->route('cookies.show')
            ->with('cookie_consent_withdrawn', true)
            ->withCookie(cookie()->forget(self::COOKIE_NAME));
    }

    private function parseConsent(?string $json): array
    {
        $defaults = [
            'necessary'   => true,
            'analytics'   => false,
            'marketing'   => false,
            'preferences' => false,
        ];

        if ($json === null) {
            return $defaults;
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            return $defaults;
        }

        return [
            'necessary'   => true,
            'analytics'   => (bool) ($data['analytics'] ?? false),
            'marketing'   => (bool) ($data['marketing'] ?? false),
            'preferences' => (bool) ($data['preferences'] ?? false),
        ];
    }
}
