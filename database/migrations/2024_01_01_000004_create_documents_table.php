<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id_docum');
            $table->unsignedInteger('ipp');
            $table->unsignedInteger('id_utilisateur');
            $table->string('titre',               200);
            $table->string('chemin_fichier',       500);
            $table->text('mots_cles')->nullable();
            $table->timestamp('date_import')->useCurrent();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('ipp')
                  ->references('ipp')
                  ->on('patients')
                  ->onDelete('restrict');

            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('utilisateurs')
                  ->onDelete('restrict');

            $table->index(['ipp', 'deleted_at']);
            $table->index('date_import');
        });

        // Colonnes enum PostgreSQL via SQL brut
        DB::statement("ALTER TABLE documents ADD COLUMN service      service_hospitalier NOT NULL DEFAULT 'Autre'");
        DB::statement("ALTER TABLE documents ADD COLUMN typedocument type_document       NOT NULL DEFAULT 'autre'");

        DB::statement("CREATE INDEX documents_typedocument_index ON documents (typedocument)");
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
