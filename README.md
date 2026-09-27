# ITtshirt3

Ce projet est un TRAVAIL DE FIN DE MODULE DE PROJET D'INTEGRATION ET DE DEVELOPPEMENT.

ITtshirt3 est un projet Laravel de boutique de t-shirts mene dans un cadre Scrum. Le depot reste un projet e-commerce en construction, mais il ne se limite plus a une simple vitrine catalogue: il expose un slice storefront transactionnel avec catalogue, panier, wishlist, checkout Stripe, historique de commandes, surfaces support et administration.

## Pile technique

- PHP 8.3 minimum, PHP 8.4 observe en runtime
- Laravel 13.6
- SQLite pour le developpement local
- Laravel UI
- Vite 8
- Vue 3
- Bootstrap 5
- Tailwind CSS 4
- Sass
- Axios
- stripe/stripe-php 20.1

## Demarrage local

Prerequis:

- PHP 8.3+
- Composer
- Node.js et npm

Installation:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

npm install
npm run build
```

Variables Stripe minimales en local:

```bash
STRIPE_KEY=pk_test_placeholder
STRIPE_SECRET=sk_test_placeholder
STRIPE_WEBHOOK_SECRET=whsec_placeholder
```

Lancement en developpement:

### Serveur Laravel uniquement

```bash
php artisan serve
```

### Serveur complet avec assets front-end

```bash
composer run dev
```

`composer run dev` desactive `XDEBUG_MODE` pour les processus PHP CLI longs afin d'eviter le bruit de step-debug dans le conteneur de dev.

Tests:

```bash
composer test
```

## Creer un administrateur

Apres `php artisan migrate --seed`, un compte admin de demonstration existe deja: `admin@example.com` / `password`.

Pour creer un nouvel administrateur ou promouvoir un utilisateur existant, executer:

```bash
php artisan tinker --execute="$user = \App\Models\User::firstOrNew(['email' => 'new-admin@example.com']); $user->name = 'New Admin'; $user->password = 'change-me-now'; $user->role = 'admin'; $user->save();"
```

Cette commande cree l'utilisateur s'il n'existe pas encore, ou met simplement son role a `admin` s'il existe deja.

## 13. Licence & Auteur
Projet académique ITtshirt3 (usage pédagogique).
Privé — pas ouvert aux contributions externes. Propriété de l'auteur.  
Conçu avec ❤️ par [PG](https://patrickgagli.github.io/pgdigital/)