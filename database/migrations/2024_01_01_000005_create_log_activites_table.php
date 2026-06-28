<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_activites', function (Blueprint $table) {
            $table->increments('id_logactivite');
            $table->unsignedInteger('id_utilisateur');
            $table->unsignedInteger('id_docum')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('date_action')->useCurrent();
            $table->string('adresse_ip', 45)->nullable();

            $table->foreign('id_utilisateur')
                  ->references('id_utilisateur')
                  ->on('utilisateurs')
                  ->onDelete('restrict');

            $table->foreign('id_docum')
                  ->references('id_docum')
                  ->on('documents')
                  ->onDelete('set null');

            $table->index('date_action');
            $table->index(['id_utilisateur', 'date_action']);
        });

        // Colonne enum PostgreSQL via SQL brut
        DB::statement("ALTER TABLE log_activites ADD COLUMN action action_log NOT NULL DEFAULT 'CONNEXION'");

        DB::statement("CREATE INDEX log_activites_action_index ON log_activites (action)");
    }

    public function down(): void
    {
        Schema::dropIfExists('log_activites');
    }
};
