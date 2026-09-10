<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('devis_id')->nullable()->constrained('devis')->nullOnDelete();
            $table->date('date_facture');
            $table->date('date_echeance')->nullable();
            $table->enum('statut', ['brouillon', 'envoyee', 'partiellement_payee', 'payee', 'en_retard', 'annulee'])
                ->default('brouillon');
            $table->decimal('montant_ht', 14, 2)->default(0);
            $table->decimal('montant_remise', 14, 2)->default(0);
            $table->decimal('montant_tva', 14, 2)->default(0);
            $table->decimal('montant_ttc', 14, 2)->default(0);
            $table->decimal('montant_paye', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('stock_decremente_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
