<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\LogActivite;
use App\Models\Patient;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // Stats communes
        $stats = [
            'total_patients'          => Patient::count(),
            'total_documents'         => Document::count(),
            'total_utilisateurs'      => Utilisateur::where('actif', true)->count(),
            'uploads_ce_mois'         => Document::whereMonth('date_import', now()->month)
                                                   ->whereYear('date_import', now()->year)
                                                   ->count(),
            'mes_actions_aujourd_hui' => LogActivite::where('id_utilisateur', $user->id_utilisateur)
                                                     ->whereDate('date_action', today())
                                                     ->count(),
        ];

        $documentsRecents = Document::with(['patient', 'utilisateur'])
            ->orderByDesc('date_import')->limit(6)->get();

        $activitesRecentes = LogActivite::with('utilisateur')
            ->where('id_utilisateur', $user->id_utilisateur)
            ->orderByDesc('date_action')->limit(8)->get();

        $docParType = Document::selectRaw('typedocument, COUNT(*) as total')
            ->groupBy('typedocument')->orderByDesc('total')
            ->pluck('total', 'typedocument');

        // Données supplémentaires pour l'admin
        $statsAdmin = null;
        if ($user->isAdministratif()) {
            $statsAdmin = [
                // Utilisateurs
                'utilisateurs_par_role'   => Utilisateur::selectRaw('role, COUNT(*) as total')
                                                ->groupBy('role')->pluck('total', 'role'),
                'utilisateurs_inactifs'   => Utilisateur::where('actif', false)->count(),

                // Documents par service
                'docs_par_service'        => Document::selectRaw('service, COUNT(*) as total')
                                                ->groupBy('service')->orderByDesc('total')
                                                ->pluck('total', 'service'),

                // Activité globale (tous les utilisateurs)
                'activite_globale'        => LogActivite::with('utilisateur')
                                                ->orderByDesc('date_action')->limit(10)->get(),

                // Utilisateurs les plus actifs
                'top_utilisateurs'        => LogActivite::selectRaw('id_utilisateur, COUNT(*) as total')
                                                ->with('utilisateur')
                                                ->groupBy('id_utilisateur')
                                                ->orderByDesc('total')
                                                ->limit(5)->get(),

                // Imports aujourd'hui
                'imports_aujourd_hui'     => Document::whereDate('date_import', today())->count(),

                // Patients récemment créés
                'patients_ce_mois'        => Patient::whereMonth('date_creation', now()->month)
                                                ->whereYear('date_creation', now()->year)->count(),
            ];
        }

        return view('dashboard.index', compact(
            'stats', 'documentsRecents', 'activitesRecentes', 'docParType', 'statsAdmin'
        ));
    }
}
