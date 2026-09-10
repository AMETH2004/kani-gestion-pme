<?php

namespace App\Services\Payment;

use InvalidArgumentException;

/**
 * Fabrique de fournisseurs de paiement, résolue depuis config/payment.php.
 * Usage : app(PaymentManager::class)->fournisseur('manual')->initier(...)
 */
class PaymentManager
{
    /** @var array<string, PaymentProvider> */
    private array $instances = [];

    public function fournisseur(?string $nom = null): PaymentProvider
    {
        $nom ??= config('payment.default');

        if (isset($this->instances[$nom])) {
            return $this->instances[$nom];
        }

        $config = config("payment.providers.{$nom}");

        if (! $config || ! isset($config['driver'])) {
            throw new InvalidArgumentException("Fournisseur de paiement inconnu : {$nom}");
        }

        $driver = $config['driver'];

        return $this->instances[$nom] = new $driver($config);
    }

    /**
     * Le mode de paiement saisi (especes, cheque, virement, wave, orange_money)
     * détermine le fournisseur qui le traite.
     */
    public function pourMode(string $mode): PaymentProvider
    {
        return match ($mode) {
            'wave' => $this->fournisseur('wave'),
            'orange_money' => $this->fournisseur('orange_money'),
            default => $this->fournisseur('manual'),
        };
    }
}
