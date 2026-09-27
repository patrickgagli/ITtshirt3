<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITtshirt3 | Désinscription confirmée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0D6EFD;
            --surface-bg: #F8FAFC;
            --card-radius: 1.25rem;
            --font-family: 'Inter', sans-serif;
        }
        body {
            font-family: var(--font-family);
            background-color: var(--surface-bg);
            color: #1E293B;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--primary-color) !important;
        }
        main { flex: 1; }
        .section-card {
            background: white;
            border-radius: var(--card-radius);
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            padding: 2.5rem;
            max-width: 480px;
            width: 100%;
        }
        .icon-wrap {
            width: 72px;
            height: 72px;
            background: #F0FDF4;
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #22C55E;
            font-size: 1.75rem;
            margin: 0 auto 1.5rem;
        }
        footer {
            background: #0F172A;
            color: #94A3B8;
            padding: 24px 0;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">ITtshirt3</a>
        </div>
    </nav>

    <main class="d-flex align-items-center justify-content-center py-5">
        <div class="section-card text-center">
            <div class="icon-wrap" aria-hidden="true">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h1 class="h4 fw-bold mb-2" style="letter-spacing:-0.02em;">Désinscription confirmée</h1>
            <p class="text-muted mb-4">
                Vous avez été désinscrit(e) de notre newsletter avec succès.
                Vous ne recevrez plus de messages de notre part.
            </p>
            <p class="text-muted small mb-4">
                Vous pouvez vous réabonner à tout moment depuis notre site en utilisant
                le formulaire en bas de page.
            </p>

            <a href="{{ route('home') }}"
               class="btn btn-primary px-4"
               style="border-radius:.75rem;font-weight:600;">
                <i class="fa-solid fa-house me-2" aria-hidden="true"></i>Retour à l'accueil
            </a>
        </div>
    </main>

    <footer>
        <div class="container text-center small">
            &copy; {{ date('Y') }} ITtshirt3. Tous droits réservés.
        </div>
    </footer>

</body>
</html>
