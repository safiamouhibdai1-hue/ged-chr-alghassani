<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration 1 — Création des types ENUM PostgreSQL
 *
 * PostgreSQL utilise des types enum personnalisés (CREATE TYPE … AS ENUM)
 * plutôt que des colonnes ENUM comme MySQL.
 * Ces types sont créés en premier, avant les tables qui les utilisent.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE role_utilisateur AS ENUM ('medecin','infirmier','administratif');
            EXCEPTION WHEN duplicate_object THEN NULL;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE service_hospitalier AS ENUM (
                    'Cardiologie','Chirurgie','Pediatrie','Neurologie',
                    'Laboratoire','Radiologie','Urgences','Autre'
                );
            EXCEPTION WHEN duplicate_object THEN NULL;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE type_document AS ENUM (
                    'rapport_consultation','compte_rendu_operatoire',
                    'resultat_laboratoire','resultat_radiologie',
                    'ordonnance','courrier_medical','autre'
                );
            EXCEPTION WHEN duplicate_object THEN NULL;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE action_log AS ENUM (
                    'CONNEXION','DECONNEXION','CONSULTATION',
                    'UPLOAD','RECHERCHE','CREATION','MODIFICATION'
                );
            EXCEPTION WHEN duplicate_object THEN NULL;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE type_rapport AS ENUM (
                    'R01_ACTIVITE','R02_DOCS_TYPE',
                    'R03_DOCS_SERVICE','R04_UTILISATEURS'
                );
            EXCEPTION WHEN duplicate_object THEN NULL;
            END $$;
        ");
    }

    public function down(): void
    {
        // Supprimer dans l'ordre inverse (les tables dépendantes doivent être supprimées d'abord)
        DB::statement('DROP TYPE IF EXISTS type_rapport      CASCADE');
        DB::statement('DROP TYPE IF EXISTS action_log        CASCADE');
        DB::statement('DROP TYPE IF EXISTS type_document     CASCADE');
        DB::statement('DROP TYPE IF EXISTS service_hospitalier CASCADE');
        DB::statement('DROP TYPE IF EXISTS role_utilisateur  CASCADE');
    }
};
