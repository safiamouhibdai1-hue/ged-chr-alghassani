<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->unsignedInteger('ipp')->primary();
            $table->string('cin',            20)->unique();
            $table->string('numero_dossier', 50)->unique();
            $table->string('nom',    100);
            $table->string('prenom', 100);
            $table->date('date_naissance');
            $table->date('date_creation')->default(now());
            $table->timestamps();
        });

        DB::statement("ALTER TABLE patients ADD COLUMN service service_hospitalier NOT NULL DEFAULT 'Autre'");
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
