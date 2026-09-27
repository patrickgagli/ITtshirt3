<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $settings = SiteSetting::instance();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name'        => ['required', 'string', 'max:100'],
            'tagline'          => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords'    => ['nullable', 'string', 'max:500'],
            'og_image_url'     => ['nullable', 'url', 'max:500'],
            'social_facebook'  => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_twitter'   => ['nullable', 'url', 'max:500'],
            'social_tiktok'    => ['nullable', 'url', 'max:500'],
            'twitter_handle'   => ['nullable', 'string', 'max:100'],
        ]);

        SiteSetting::instance()->update($validated);

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Paramètres du site mis à jour.');
    }
}
