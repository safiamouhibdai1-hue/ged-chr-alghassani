<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->increments('id_rapport');
            $table->unsignedInteger('id_utilisateur');
            $table->string('titre', 200);
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->timestamp('date_generation')->useCurrent();

            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('utilisateurs')
                  ->onDelete('restrict');
        });

        // Colonne enum PostgreSQL via SQL brut
        DB::statement("ALTER TABLE rapports ADD COLUMN type_rapport type_rapport NOT NULL DEFAULT 'R01_ACTIVITE'");
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
