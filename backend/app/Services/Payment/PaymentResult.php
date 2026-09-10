<?php

namespace App\Services\Payment;

/**
 * Résultat normalisé renvoyé par tous les fournisseurs de paiement.
 */
class PaymentResult
{
    public function __construct(
        public readonly string $statut,          // 'reussi' | 'en_attente' | 'echoue'
        public readonly ?string $transactionId = null,
        public readonly ?string $urlRedirection = null, // pour Wave/OM : page de paiement à ouvrir
        public readonly array $donnees = [],
    ) {}

    public static function reussi(?string $transactionId = null, array $donnees = []): self
    {
        return new self('reussi', $transactionId, null, $donnees);
    }

    public static function enAttente(?string $transactionId = null, ?string $urlRedirection = null): self
    {
        return new self('en_attente', $transactionId, $urlRedirection);
    }
}
