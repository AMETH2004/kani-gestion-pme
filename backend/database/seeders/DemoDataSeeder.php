<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\Produit;
use App\Support\Numerotation;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect(['Boissons', 'Épicerie', 'Hygiène', 'Papeterie'])
            ->mapWithKeys(fn ($nom) => [$nom => Categorie::firstOrCreate(['nom' => $nom])]);

        $produits = [
            ['Boissons', 'Eau minérale 1,5L', 'EAU-15', 250, 400, 24, 12],
            ['Boissons', 'Jus d\'orange 1L', 'JUS-OR-1', 600, 1000, 15, 6],
            ['Épicerie', 'Riz parfumé 5kg', 'RIZ-5', 3200, 4500, 8, 10],
            ['Épicerie', 'Huile végétale 1L', 'HUILE-1', 900, 1300, 20, 8],
            ['Hygiène', 'Savon de Marseille', 'SAV-MAR', 300, 500, 40, 15],
            ['Papeterie', 'Cahier 200 pages', 'CAH-200', 400, 700, 30, 10],
        ];

        foreach ($produits as [$cat, $designation, $ref, $achat, $vente, $stock, $alerte]) {
            Produit::firstOrCreate(
                ['reference' => $ref],
                [
                    'designation' => $designation,
                    'categorie_id' => $categories[$cat]->id,
                    'prix_achat' => $achat,
                    'prix_vente' => $vente,
                    'taux_tva' => 18,
                    'unite' => 'unité',
                    'stock_actuel' => $stock,
                    'stock_alerte' => $alerte,
                ],
            );
        }

        $clients = [
            ['Boutique Chez Aïda', 'entreprise', '+221 77 123 45 67', 'aida@example.sn'],
            ['Restaurant Le Baobab', 'entreprise', '+221 78 987 65 43', 'contact@baobab.sn'],
            ['Ndèye Sarr', 'particulier', '+221 76 555 44 33', null],
        ];

        foreach ($clients as [$nom, $type, $tel, $email]) {
            Client::firstOrCreate(
                ['nom' => $nom],
                [
                    'code' => Numerotation::prochain(Client::class, 'CLI', 'code'),
                    'type' => $type,
                    'telephone' => $tel,
                    'email' => $email,
                ],
            );
        }
    }
}
