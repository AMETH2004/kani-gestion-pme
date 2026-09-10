<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entreprises', function (Blueprint $table) {
            $table->id();
            $table->string('raison_sociale');
            $table->string('ninea', 50)->nullable();
            $table->string('rccm', 50)->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('devise', 10)->default('XOF');
            $table->decimal('taux_tva_defaut', 5, 2)->default(18);
            $table->text('pied_de_page')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};
