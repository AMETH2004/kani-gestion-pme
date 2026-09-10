<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 60)->unique();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->decimal('prix_achat', 14, 2)->default(0);
            $table->decimal('prix_vente', 14, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(18);
            $table->string('unite', 20)->default('unité');
            $table->decimal('stock_actuel', 14, 3)->default(0);
            $table->decimal('stock_alerte', 14, 3)->default(0);
            $table->boolean('gerer_stock')->default(true);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
