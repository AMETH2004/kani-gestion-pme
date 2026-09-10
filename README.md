# Kani — plateforme de gestion PME

Application web de gestion commerciale pour PME / commerçants (Sénégal) :
**clients, catalogue produits, stock, devis, factures, encaissements, exports PDF**,
avec gestion multi-rôles.

- **backend/** — API REST Laravel 13 + Sanctum + spatie/laravel-permission (MySQL)
- **frontend/** — SPA Angular 22 (standalone, Bootstrap 5)

## Prérequis

PHP 8.3+, Composer, MySQL 8, Node 20+ (Laragon fournit tout ça).

## Démarrage

### 1. Backend

```bash
cd backend
composer install
cp .env.example .env          # puis renseigner DB_DATABASE=kani
php artisan key:generate
php artisan migrate --seed     # crée les tables + données de démo
php artisan serve              # http://127.0.0.1:8000
```

Comptes créés par le seeder (mot de passe : `password`) :

| E-mail | Rôle |
|---|---|
| admin@kani.test | Administrateur |
| gestion@kani.test | Gestionnaire (ventes / facturation) |
| stock@kani.test | Magasinier (produits / stock) |
| compta@kani.test | Comptable (lecture + exports) |

### 2. Frontend

```bash
cd frontend
npm install
npm start                      # http://localhost:4200
```

`src/environments/environment.ts` pointe vers `http://127.0.0.1:8000/api`.

## Rôles et permissions

Le contrôle d'accès est porté par les middlewares `role:` dans
`backend/routes/api.php` et reflété dans le menu Angular (`shell.ts`).

| Module | Administrateur | Gestionnaire | Magasinier | Comptable |
|---|:---:|:---:|:---:|:---:|
| Tableau de bord, consultation | ✅ | ✅ | ✅ | ✅ |
| Clients, devis, factures, paiements | ✅ | ✅ | — | — |
| Produits, catégories, stock | ✅ | — | ✅ | — |
| Paramètres, utilisateurs | ✅ | — | — | — |
| Exports PDF | ✅ | ✅ | ✅ | ✅ |

## Paiement mobile money (Wave / Orange Money)

Une couche d'abstraction est en place (`backend/app/Services/Payment/`) :
`PaymentProvider` (interface), `ManualProvider` (actif — encaissement saisi),
`WaveProvider` / `OrangeMoneyProvider` (emplacements réservés, non implémentés).

En v1, un paiement reçu par Wave/OM se **saisit manuellement** sur la facture
(le mode n'est qu'un libellé). L'initiation d'une collecte en ligne via les API
Wave/OM est un chantier séparé ; les clés sont déjà prévues dans
`config/payment.php` et `.env`.

## Flux métier clé

Devis → conversion en facture → validation (décrémente le stock, crée les
mouvements) → encaissement(s) → statut `payée` → export PDF.
