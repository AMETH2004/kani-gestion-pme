<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Quatre rôles métier. Le contrôle d'accès fin est porté par les
     * middlewares `role:` dans routes/api.php ; ces rôles suffisent à la v1.
     */
    public const ROLES = [
        'Administrateur' => 'Accès total : paramètres, utilisateurs, tous les modules.',
        'Gestionnaire' => 'Ventes et facturation : clients, devis, factures, encaissements.',
        'Magasinier' => 'Catalogue et stock : produits, catégories, mouvements de stock.',
        'Comptable' => 'Consultation et exports PDF, sans modification.',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::ROLES) as $nom) {
            Role::firstOrCreate(['name' => $nom, 'guard_name' => 'web']);
        }
    }
}
