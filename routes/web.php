<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\UtilisateurController;
use Illuminate\Support\Facades\Route;

// Routes publiques
// Redirection de la racine vers /login
Route::get('/', fn () => redirect()->route('login'));

// Page de connexion (formulaire)
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

// Traitement du formulaire de connexion
Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

// Déconnexion
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

// Routes protégées (nécessitent une connexion via le middleware 'ged.auth')
Route::middleware(['ged.auth'])->group(function () {

    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Patients
    Route::resource('/patients', PatientController::class)
         ->parameters(['patients' => 'ipp']); // ipp comme clé au lieu de {patient}

    // Documents
    Route::resource('/documents', DocumentController::class)
         ->parameters(['documents' => 'id'])
         ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::get('/documents/{id}/confirm-delete', [DocumentController::class, 'confirmDelete'])
         ->name('documents.confirm-delete');

    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])
         ->name('documents.download');

    Route::get('/documents/{id}/preview', [DocumentController::class, 'preview'])
         ->name('documents.preview');

    // Historique / Journal d'audit
    Route::get('/historique', [HistoriqueController::class, 'index'])
         ->name('historique.index');

    // Export CSV (admin uniquement — vérifié dans le contrôleur)
    Route::get('/historique/export', [HistoriqueController::class, 'exportCsv'])
         ->name('historique.export');

    // ROUTES ADMIN UNIQUEMENT
    Route::middleware(['role:administratif'])->group(function () {

        // Gestion des utilisateurs — CRUD complet incluant suppression
        Route::resource('/utilisateurs', UtilisateurController::class)
             ->parameters(['utilisateurs' => 'id'])
             ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // Bascule actif / inactif en un clic
        Route::patch('/utilisateurs/{id}/toggle-actif', [UtilisateurController::class, 'toggleActif'])
             ->name('utilisateurs.toggle-actif');

        // Changement de rôle rapide depuis la liste
        Route::patch('/utilisateurs/{id}/change-role', [UtilisateurController::class, 'changeRole'])
             ->name('utilisateurs.change-role');

        Route::get('/rapports', [RapportController::class, 'index'])
             ->name('rapports.index');

        Route::get('/rapports/export', [RapportController::class, 'exportCsv'])
             ->name('rapports.export');
    });

});
