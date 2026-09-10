<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use Illuminate\Database\Seeder;

class EntrepriseSeeder extends Seeder
{
    public function run(): void
    {
        Entreprise::query()->firstOrCreate([], [
            'raison_sociale' => 'Ma PME SARL',
            'adresse' => 'Dakar, Sénégal',
            'telephone' => '+221 33 800 00 00',
            'email' => 'contact@mapme.sn',
            'devise' => 'XOF',
            'taux_tva_defaut' => 18,
            'pied_de_page' => 'Merci de votre confiance. Paiement à 30 jours — Wave / Orange Money / espèces.',
        ]);
    }
}
