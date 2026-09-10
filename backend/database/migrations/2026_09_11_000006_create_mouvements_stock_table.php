<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->enum('type', ['entree', 'sortie', 'ajustement']);
            $table->decimal('quantite', 14, 3);
            $table->decimal('stock_avant', 14, 3);
            $table->decimal('stock_apres', 14, 3);
            $table->string('motif')->nullable();
            $table->string('reference_document')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_mouvement');
            $table->timestamps();

            $table->index(['produit_id', 'date_mouvement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
    }
};
