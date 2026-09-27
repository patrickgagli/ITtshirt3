@php
    $hasConsent = request()->cookie('cookie_consent') !== null;
@endphp

@unless($hasConsent)
<div id="cookie-banner" role="region" aria-label="Consentement aux cookies" aria-live="polite"
     style="position:fixed;bottom:0;left:0;right:0;z-index:9999;
            background:#1E293B;color:#F8FAFC;
            padding:1.25rem 1.5rem;
            box-shadow:0 -4px 24px rgba(0,0,0,0.18);
            border-top:3px solid #0D6EFD;">
    <div style="max-width:1200px;margin:0 auto;">
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:1rem;">
            <div style="flex:1;min-width:260px;">
                <p style="margin:0;font-size:.9375rem;line-height:1.5;">
                    <strong><i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i> Nous utilisons des cookies</strong><br>
                    Ce site utilise des cookies pour améliorer votre expérience et à des fins analytiques.
                    Vous pouvez choisir les cookies que vous acceptez.
                    <a href="{{ route('cookies.show') }}"
                       style="color:#93C5FD;text-decoration:underline;"
                       aria-label="En savoir plus sur notre politique de cookies">En savoir plus</a>
                </p>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:.625rem;align-items:center;flex-shrink:0;">
                {{-- Accept all --}}
                <form method="POST" action="{{ route('cookies.consent.store') }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="analytics"   value="1">
                    <input type="hidden" name="marketing"   value="1">
                    <input type="hidden" name="preferences" value="1">
                    <input type="hidden" name="_redirect"   value="{{ url()->current() }}">
                    <button type="submit"
                            style="background:#0D6EFD;color:#fff;border:none;padding:.5rem 1.125rem;
                                   border-radius:.5rem;font-weight:600;font-size:.875rem;cursor:pointer;
                                   white-space:nowrap;line-height:1.4;">
                        <i class="fa-solid fa-check me-1" aria-hidden="true"></i>Tout accepter
                    </button>
                </form>

                {{-- Reject non-essential --}}
                <form method="POST" action="{{ route('cookies.consent.store') }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="analytics"   value="0">
                    <input type="hidden" name="marketing"   value="0">
                    <input type="hidden" name="preferences" value="0">
                    <input type="hidden" name="_redirect"   value="{{ url()->current() }}">
                    <button type="submit"
                            style="background:transparent;color:#94A3B8;border:1px solid #475569;
                                   padding:.5rem 1.125rem;border-radius:.5rem;font-weight:600;
                                   font-size:.875rem;cursor:pointer;white-space:nowrap;line-height:1.4;">
                        Refuser
                    </button>
                </form>

                {{-- Manage preferences --}}
                <a href="{{ route('cookies.show') }}"
                   style="color:#93C5FD;font-size:.875rem;font-weight:600;
                          white-space:nowrap;text-decoration:underline;padding:.5rem 0;">
                    <i class="fa-solid fa-sliders me-1" aria-hidden="true"></i>Gérer mes préférences
                </a>
            </div>
        </div>
    </div>
</div>
@endunless
