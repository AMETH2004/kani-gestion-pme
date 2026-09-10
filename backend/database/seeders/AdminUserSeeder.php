<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@kani.test'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'poste' => 'Propriétaire',
                'actif' => true,
            ],
        );
        $admin->syncRoles(['Administrateur']);

        // Comptes de démonstration pour les autres rôles (mot de passe : password)
        $demo = [
            ['name' => 'Awa Gestionnaire', 'email' => 'gestion@kani.test', 'role' => 'Gestionnaire'],
            ['name' => 'Moussa Magasinier', 'email' => 'stock@kani.test', 'role' => 'Magasinier'],
            ['name' => 'Fatou Comptable', 'email' => 'compta@kani.test', 'role' => 'Comptable'],
        ];

        foreach ($demo as $d) {
            $user = User::updateOrCreate(
                ['email' => $d['email']],
                ['name' => $d['name'], 'password' => Hash::make('password'), 'actif' => true],
            );
            $user->syncRoles([$d['role']]);
        }

        $this->command->info('Connexion admin : admin@kani.test / password');
    }
}
