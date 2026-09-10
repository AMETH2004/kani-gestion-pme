<?php

namespace App\Services\Payment;

use App\Models\Facture;

/**
 * Contrat commun à tous les moyens de paiement (encaissement manuel aujourd'hui,
 * Wave / Orange Money plus tard). Un nouveau fournisseur = une nouvelle classe
 * qui implémente cette interface + une entrée dans config/payment.php.
 */
interface PaymentProvider
{
    /**
     * Initie un encaissement pour une facture.
     *
     * @param  array<string, mixed>  $donnees  Champs saisis (mode, référence, téléphone…)
     */
    public function initier(Facture $facture, float $montant, array $donnees = []): PaymentResult;

    /**
     * Vérifie l'état d'une transaction auprès du fournisseur (polling).
     */
    public function verifier(string $transactionId): PaymentResult;

    /**
     * Traite une notification asynchrone (webhook) du fournisseur.
     *
     * @param  array<string, mixed>  $payload
     */
    public function webhook(array $payload): PaymentResult;

    /**
     * Identifiant court du fournisseur ('manual', 'wave', 'orange_money').
     */
    public function nom(): string;
}
