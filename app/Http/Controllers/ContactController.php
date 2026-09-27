<?php

namespace App\Http\Controllers;

use App\Mail\ContactInquiry;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(Request $request): View
    {
        $prefill = [
            'name'  => $request->user()?->name ?? old('name', ''),
            'email' => $request->user()?->email ?? old('email', ''),
        ];

        return view('contact.show', ['prefill' => $prefill]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        Mail::to(config('mail.contact_address', config('mail.from.address', 'contact@example.com')))
            ->send(new ContactInquiry($validated));

        ContactSubmission::create($validated);

        return redirect()->route('contact.show')
            ->with('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.');
    }
}
