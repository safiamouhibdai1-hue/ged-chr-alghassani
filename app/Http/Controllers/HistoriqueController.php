<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HistoriqueController extends Controller
{
    private const ACTIONS_LABELS = [
        'CONNEXION'    => 'Connexion',
        'DECONNEXION'  => 'Déconnexion',
        'CONSULTATION' => 'Consultation',
        'UPLOAD'       => 'Import document',
        'RECHERCHE'    => 'Recherche',
        'CREATION'     => 'Création',
        'MODIFICATION' => 'Modification',
        'SUPPRESSION'  => 'Suppression',
    ];

    public function index(Request $request): View
    {
        /** @var Utilisateur $moi */
        $moi = Auth::user();

        $query = LogActivite::with(['utilisateur', 'document'])
            ->orderByDesc('date_action');

        if (!$moi->isAdministratif()) {
            $query->where('id_utilisateur', $moi->id_utilisateur);
        }

        // Filtre utilisateur (admin)
        if ($moi->isAdministratif() && $request->filled('utilisateur')) {
            $query->where('id_utilisateur', $request->input('utilisateur'));
        }

        // Filtre date
        if ($request->filled('date_debut')) {
            $query->whereDate('date_action', '>=', $request->input('date_debut'));
        }

        // Filtre IP
        if ($request->filled('ip')) {
            $query->where('adresse_ip', 'like', '%' . $request->input('ip') . '%');
        }

        $activites = $query->paginate(20)->withQueryString();

        // Stats
        $base = LogActivite::query();
        if (!$moi->isAdministratif()) {
            $base->where('id_utilisateur', $moi->id_utilisateur);
        }

        $stats = [
            'total'           => (clone $base)->count(),
            'aujourd_hui'     => (clone $base)->whereDate('date_action', today())->count(),
            'cette_semaine'   => (clone $base)->whereBetween('date_action', [
                                    now()->startOfWeek()->toDateString(),
                                    now()->endOfWeek()->toDateString(),
                                ])->count(),
            'par_action'      => (clone $base)->get()->groupBy('action')->map->count()->sortDesc(),
            'top_utilisateurs'=> $moi->isAdministratif()
                ? LogActivite::selectRaw('id_utilisateur, COUNT(*) as total')
                    ->with('utilisateur')->groupBy('id_utilisateur')
                    ->orderByDesc('total')->limit(5)->get()
                : collect(),
        ];

        $utilisateurs = $moi->isAdministratif()
            ? Utilisateur::orderBy('nom')->get(['id_utilisateur', 'nom', 'prenom', 'role'])
            : collect();

        return view('historique.index', [
            'activites'     => $activites,
            'stats'         => $stats,
            'utilisateurs'  => $utilisateurs,
            'actionsLabels' => self::ACTIONS_LABELS,
            'moi'           => $moi,
        ]);
    }

    public function exportCsv(Request $request): Response|RedirectResponse
    {
        if (!Auth::user()->isAdministratif()) {
            abort(403, 'Export réservé aux administratifs.');
        }

        $query = LogActivite::with('utilisateur')->orderByDesc('date_action');
        if ($request->filled('utilisateur')) $query->where('id_utilisateur', $request->input('utilisateur'));
        if ($request->filled('date_debut'))  $query->whereDate('date_action', '>=', $request->input('date_debut'));

        $activites = $query->get();

        $csv = "\xEF\xBB\xBF";
        $csv .= "ID;Utilisateur;Rôle;Description;Date;Adresse IP\n";

        foreach ($activites as $a) {
            $csv .= implode(';', [
                $a->id_logactivite,
                '"' . ($a->utilisateur ? $a->utilisateur->prenom . ' ' . $a->utilisateur->nom : '?') . '"',
                '"' . ($a->utilisateur?->role ?? '') . '"',
                '"' . str_replace('"', '""', $a->description ?? '') . '"',
                $a->date_action->format('d/m/Y'),
                $a->adresse_ip ?? '',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="journal_ged_' . now()->format('Ymd') . '.csv"',
        ]);
    }
}
