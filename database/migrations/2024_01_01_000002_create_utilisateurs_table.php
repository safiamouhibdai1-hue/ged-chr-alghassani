<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->increments('id_utilisateur');
            $table->string('nom',    100);
            $table->string('prenom', 100);
            $table->string('email',  191)->unique();
            $table->string('password', 255);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        // Ajout de la colonne enum PostgreSQL via SQL brut
        DB::statement("ALTER TABLE utilisateurs ADD COLUMN role role_utilisateur NOT NULL DEFAULT 'medecin'");
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
