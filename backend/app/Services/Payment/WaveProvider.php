<?php

namespace App\Services\Payment;

use App\Models\Facture;
use RuntimeException;

/**
 * EMPLACEMENT RÉSERVÉ — intégration Wave Business API.
 *
 * L'intégration réelle n'est pas encore faite (cf. plan : « module isolé, non
 * implémenté »). Quand elle le sera :
 *   - initier()  : POST {base_url}/v1/checkout/sessions  → renvoie une URL de paiement (PaymentResult::enAttente)
 *   - webhook()  : vérifier la signature via WAVE_WEBHOOK_SECRET puis marquer le paiement réussi
 *   - verifier() : GET {base_url}/v1/checkout/sessions/{id}
 *
 * Les clés sont déjà câblées dans config/payment.php (WAVE_API_KEY, etc.).
 */
class WaveProvider implements PaymentProvider
{
    public function __construct(private readonly array $config = []) {}

    public function initier(Facture $facture, float $montant, array $donnees = []): PaymentResult
    {
        throw new RuntimeException("Le paiement Wave n'est pas encore activé sur cette instance.");
    }

    public function verifier(string $transactionId): PaymentResult
    {
        throw new RuntimeException("Le paiement Wave n'est pas encore activé sur cette instance.");
    }

    public function webhook(array $payload): PaymentResult
    {
        throw new RuntimeException("Le paiement Wave n'est pas encore activé sur cette instance.");
    }

    public function nom(): string
    {
        return 'wave';
    }
}
