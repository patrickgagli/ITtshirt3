@extends('admin.layout')

@section('title', 'Paramètres du site')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading fs-3 mb-0">Paramètres du site</h1>
        <p class="text-muted mb-0 small">SEO, référencement et réseaux sociaux.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" novalidate>
    @csrf
    @method('PATCH')

    <div class="row g-4">

        {{-- ----------------------------------------------------------------- --}}
        {{-- SEO                                                                --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="col-12">
            <div class="card p-4">
                <h5 class="fw-bold mb-4">
                    <i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>Référencement (SEO)
                </h5>

                <div class="mb-3">
                    <label for="site_name" class="form-label fw-semibold">Nom du site <span class="text-danger">*</span></label>
                    <input type="text" id="site_name" name="site_name" maxlength="100"
                           class="form-control @error('site_name') is-invalid @enderror"
                           value="{{ old('site_name', $settings->site_name) }}" required>
                    @error('site_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Apparaît dans le titre du navigateur et les résultats Google.</div>
                </div>

                <div class="mb-3">
                    <label for="tagline" class="form-label fw-semibold">Slogan</label>
                    <input type="text" id="tagline" name="tagline" maxlength="255"
                           class="form-control @error('tagline') is-invalid @enderror"
                           value="{{ old('tagline', $settings->tagline) }}">
                    @error('tagline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Affiché sous le nom du site sur la page d'accueil.</div>
                </div>

                <div class="mb-3">
                    <label for="meta_description" class="form-label fw-semibold">Méta description</label>
                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="500"
                              class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description', $settings->meta_description) }}</textarea>
                    @error('meta_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">150–160 caractères recommandés. Utilisée par les moteurs de recherche.</div>
                </div>

                <div class="mb-3">
                    <label for="meta_keywords" class="form-label fw-semibold">Mots-clés</label>
                    <input type="text" id="meta_keywords" name="meta_keywords" maxlength="500"
                           class="form-control @error('meta_keywords') is-invalid @enderror"
                           value="{{ old('meta_keywords', $settings->meta_keywords) }}">
                    @error('meta_keywords')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Séparés par des virgules. Impact SEO limité, mais utile pour la cohérence interne.</div>
                </div>

                <div class="mb-0">
                    <label for="og_image_url" class="form-label fw-semibold">Image Open Graph (URL)</label>
                    <input type="url" id="og_image_url" name="og_image_url" maxlength="500"
                           class="form-control @error('og_image_url') is-invalid @enderror"
                           value="{{ old('og_image_url', $settings->og_image_url) }}"
                           placeholder="https://...">
                    @error('og_image_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Image affichée lors du partage sur les réseaux sociaux. Taille recommandée : 1200×630 px.</div>
                </div>
            </div>
        </div>

        {{-- ----------------------------------------------------------------- --}}
        {{-- Social media                                                       --}}
        {{-- ----------------------------------------------------------------- --}}
        <div class="col-12">
            <div class="card p-4">
                <h5 class="fw-bold mb-4">
                    <i class="fa-solid fa-share-nodes me-2 text-primary"></i>Réseaux sociaux
                </h5>

                <div class="mb-3">
                    <label for="social_facebook" class="form-label fw-semibold">
                        <i class="fa-brands fa-facebook me-1 text-primary"></i>Facebook
                    </label>
                    <input type="url" id="social_facebook" name="social_facebook" maxlength="500"
                           class="form-control @error('social_facebook') is-invalid @enderror"
                           value="{{ old('social_facebook', $settings->social_facebook) }}"
                           placeholder="https://www.facebook.com/votrepage">
                    @error('social_facebook')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="social_instagram" class="form-label fw-semibold">
                        <i class="fa-brands fa-instagram me-1 text-danger"></i>Instagram
                    </label>
                    <input type="url" id="social_instagram" name="social_instagram" maxlength="500"
                           class="form-control @error('social_instagram') is-invalid @enderror"
                           value="{{ old('social_instagram', $settings->social_instagram) }}"
                           placeholder="https://www.instagram.com/votreprofil">
                    @error('social_instagram')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="social_twitter" class="form-label fw-semibold">
                        <i class="fa-brands fa-x-twitter me-1"></i>X (Twitter)
                    </label>
                    <input type="url" id="social_twitter" name="social_twitter" maxlength="500"
                           class="form-control @error('social_twitter') is-invalid @enderror"
                           value="{{ old('social_twitter', $settings->social_twitter) }}"
                           placeholder="https://twitter.com/votreprofil">
                    @error('social_twitter')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="twitter_handle" class="form-label fw-semibold">Identifiant Twitter / X (sans @)</label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" id="twitter_handle" name="twitter_handle" maxlength="100"
                               class="form-control @error('twitter_handle') is-invalid @enderror"
                               value="{{ old('twitter_handle', $settings->twitter_handle) }}"
                               placeholder="votreidentifiant">
                        @error('twitter_handle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">Utilisé dans la balise <code>twitter:site</code> pour les cartes Twitter.</div>
                </div>

                <div class="mb-0">
                    <label for="social_tiktok" class="form-label fw-semibold">
                        <i class="fa-brands fa-tiktok me-1"></i>TikTok
                    </label>
                    <input type="url" id="social_tiktok" name="social_tiktok" maxlength="500"
                           class="form-control @error('social_tiktok') is-invalid @enderror"
                           value="{{ old('social_tiktok', $settings->social_tiktok) }}"
                           placeholder="https://www.tiktok.com/@votreprofil">
                    @error('social_tiktok')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

    </div>{{-- /row --}}

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk me-2"></i>Enregistrer les paramètres
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>

</form>
@endsection
