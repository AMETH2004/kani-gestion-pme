<?php

namespace App\Services\Payment;

use App\Models\Facture;

/**
 * Encaissement saisi manuellement par un opérateur : espèces, chèque, virement.
 * Aucun appel externe — le paiement est considéré comme abouti dès sa saisie.
 */
class ManualProvider implements PaymentProvider
{
    public function __construct(private readonly array $config = []) {}

    public function initier(Facture $facture, float $montant, array $donnees = []): PaymentResult
    {
        return PaymentResult::reussi(
            transactionId: $donnees['reference'] ?? null,
            donnees: ['mode' => $donnees['mode'] ?? 'especes'],
        );
    }

    public function verifier(string $transactionId): PaymentResult
    {
        return PaymentResult::reussi($transactionId);
    }

    public function webhook(array $payload): PaymentResult
    {
        return PaymentResult::reussi($payload['reference'] ?? null);
    }

    public function nom(): string
    {
        return 'manual';
    }
}
