<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->decimal('montant', 14, 2);
            $table->enum('mode', ['especes', 'cheque', 'virement', 'wave', 'orange_money', 'autre'])
                ->default('especes');
            $table->string('reference')->nullable();
            $table->string('fournisseur_paiement', 30)->default('manual');
            $table->string('transaction_id')->nullable();
            $table->date('date_paiement');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
