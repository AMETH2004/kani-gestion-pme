<?php

namespace App\Services\Payment;

use App\Models\Facture;
use RuntimeException;

/**
 * EMPLACEMENT RÉSERVÉ — intégration Orange Money (API Web Payment).
 *
 * Non implémenté (cf. plan). Étapes prévues :
 *   - obtenir un token OAuth via OM_CLIENT_ID / OM_CLIENT_SECRET
 *   - initier()  : POST /orange-money-webpay/.../webpayment → URL de paiement
 *   - verifier() : GET  /orange-money-webpay/.../transactionstatus
 *   - webhook()  : notification de statut
 */
class OrangeMoneyProvider implements PaymentProvider
{
    public function __construct(private readonly array $config = []) {}

    public function initier(Facture $facture, float $montant, array $donnees = []): PaymentResult
    {
        throw new RuntimeException("Le paiement Orange Money n'est pas encore activé sur cette instance.");
    }

    public function verifier(string $transactionId): PaymentResult
    {
        throw new RuntimeException("Le paiement Orange Money n'est pas encore activé sur cette instance.");
    }

    public function webhook(array $payload): PaymentResult
    {
        throw new RuntimeException("Le paiement Orange Money n'est pas encore activé sur cette instance.");
    }

    public function nom(): string
    {
        return 'orange_money';
    }
}
