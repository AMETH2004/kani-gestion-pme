<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone', 30)->nullable()->after('email');
            $table->string('poste', 100)->nullable()->after('telephone');
            $table->boolean('actif')->default(true)->after('poste');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telephone', 'poste', 'actif']);
        });
    }
};
