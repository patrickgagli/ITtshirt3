<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name'  => ['nullable', 'string', 'max:100'],
        ]);

        $existing = NewsletterSubscriber::where('email', $validated['email'])->first();

        if ($existing) {
            if ($existing->isActive()) {
                return redirect()->back()
                    ->with('newsletter_info', 'Cette adresse est déjà inscrite à la newsletter.');
            }

            // Re-subscribe: clear the unsubscribed_at and refresh the token
            $existing->update([
                'name'            => $validated['name'] ?? $existing->name,
                'token'           => NewsletterSubscriber::generateToken(),
                'subscribed_at'   => now(),
                'unsubscribed_at' => null,
            ]);

            return redirect()->back()
                ->with('newsletter_success', 'Vous êtes à nouveau inscrit(e) à notre newsletter !');
        }

        NewsletterSubscriber::create([
            'email'         => $validated['email'],
            'name'          => $validated['name'] ?? null,
            'token'         => NewsletterSubscriber::generateToken(),
            'subscribed_at' => now(),
        ]);

        return redirect()->back()
            ->with('newsletter_success', 'Merci ! Vous êtes maintenant inscrit(e) à notre newsletter.');
    }

    public function unsubscribeForm(string $token): View
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        return view('newsletter.unsubscribe', ['subscriber' => $subscriber]);
    }

    public function unsubscribe(Request $request, string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->firstOrFail();

        if ($subscriber->isActive()) {
            $subscriber->update(['unsubscribed_at' => now()]);
        }

        return redirect()->route('newsletter.unsubscribed');
    }

    public function unsubscribed(): View
    {
        return view('newsletter.unsubscribed');
    }
}
