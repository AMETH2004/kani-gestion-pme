<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Kani — plateforme de gestion PME
|--------------------------------------------------------------------------
| Rôles : Administrateur · Gestionnaire (ventes/facturation) ·
|         Magasinier (produits/stock) · Comptable (lecture + exports).
*/

// --- Public ---
Route::post('/login', [AuthController::class, 'login']);

// --- Authentifié ---
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // ---- Lecture : accessible à tous les rôles connectés ----
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::get('/categories', [CategorieController::class, 'index']);
    Route::get('/categories/{categorie}', [CategorieController::class, 'show']);
    Route::get('/produits', [ProduitController::class, 'index']);
    Route::get('/produits/alertes-stock', [ProduitController::class, 'alertesStock']);
    Route::get('/produits/{produit}', [ProduitController::class, 'show']);
    Route::get('/stock/mouvements', [StockController::class, 'index']);
    Route::get('/devis', [DevisController::class, 'index']);
    Route::get('/devis/{devis}', [DevisController::class, 'show']);
    Route::get('/factures', [FactureController::class, 'index']);
    Route::get('/factures/{facture}', [FactureController::class, 'show']);
    Route::get('/paiements', [PaiementController::class, 'index']);
    Route::get('/entreprise', [EntrepriseController::class, 'show']);

    // ---- Exports PDF (lecture) ----
    Route::get('/factures/{facture}/pdf', [PdfController::class, 'facture']);
    Route::get('/devis/{devis}/pdf', [PdfController::class, 'devis']);

    // ---- Ventes & facturation : Administrateur + Gestionnaire ----
    Route::middleware('role:Administrateur|Gestionnaire')->group(function () {
        Route::post('/clients', [ClientController::class, 'store']);
        Route::put('/clients/{client}', [ClientController::class, 'update']);
        Route::delete('/clients/{client}', [ClientController::class, 'destroy']);

        Route::post('/devis', [DevisController::class, 'store']);
        Route::put('/devis/{devis}', [DevisController::class, 'update']);
        Route::delete('/devis/{devis}', [DevisController::class, 'destroy']);
        Route::post('/devis/{devis}/convertir', [DevisController::class, 'convertir']);

        Route::post('/factures', [FactureController::class, 'store']);
        Route::put('/factures/{facture}', [FactureController::class, 'update']);
        Route::delete('/factures/{facture}', [FactureController::class, 'destroy']);
        Route::post('/factures/{facture}/valider', [FactureController::class, 'valider']);
        Route::post('/factures/{facture}/annuler', [FactureController::class, 'annuler']);

        Route::post('/factures/{facture}/paiements', [PaiementController::class, 'store']);
        Route::delete('/factures/{facture}/paiements/{paiement}', [PaiementController::class, 'destroy']);
    });

    // ---- Produits & stock : Administrateur + Magasinier ----
    Route::middleware('role:Administrateur|Magasinier')->group(function () {
        Route::post('/categories', [CategorieController::class, 'store']);
        Route::put('/categories/{categorie}', [CategorieController::class, 'update']);
        Route::delete('/categories/{categorie}', [CategorieController::class, 'destroy']);

        Route::post('/produits', [ProduitController::class, 'store']);
        Route::put('/produits/{produit}', [ProduitController::class, 'update']);
        Route::delete('/produits/{produit}', [ProduitController::class, 'destroy']);

        Route::post('/produits/{produit}/mouvements', [StockController::class, 'store']);
    });

    // ---- Administration : Administrateur uniquement ----
    Route::middleware('role:Administrateur')->group(function () {
        Route::put('/entreprise', [EntrepriseController::class, 'update']);

        Route::get('/roles', [UserController::class, 'roles']);
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });
});
