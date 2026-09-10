<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained('devis')->cascadeOnDelete();
            $table->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete();
            $table->string('designation');
            $table->decimal('quantite', 14, 3)->default(1);
            $table->decimal('prix_unitaire', 14, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(18);
            $table->decimal('montant_ht', 14, 2)->default(0);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_devis');
    }
};
