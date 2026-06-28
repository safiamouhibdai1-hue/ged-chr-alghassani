<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\LogActivite;
use App\Models\Patient;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RapportController extends Controller
{
    public function index(Request $request): View
    {
        $moi     = Auth::user();
        $periode = $request->input('periode', '12');
        $nbMois  = is_numeric($periode) ? (int) $periode : 12;
        $debut   = now()->subMonths($nbMois - 1)->startOfMonth()->toDateString();

        // Cartes de synthèse
        $total       = Document::count();
        $totalPrec   = Document::whereMonth('date_import', now()->subMonth()->month)
                                ->whereYear('date_import', now()->subMonth()->year)->count();
        $totalMois   = Document::whereMonth('date_import', now()->month)
                                ->whereYear('date_import', now()->year)->count();

        $cartes = [
            'patients_total'      => Patient::count(),
            'documents_total'     => $total,
            'uploads_ce_mois'     => $totalMois,
            'utilisateurs_actifs' => Utilisateur::where('actif', true)->count(),
            'connexions_semaine'  => LogActivite::whereBetween('date_action', [
                                        now()->startOfWeek()->toDateString(),
                                        now()->endOfWeek()->toDateString(),
                                    ])->count(),
            'docs_mois_precedent' => $totalPrec,
            'variation_uploads'   => $totalPrec > 0
                                        ? round((($totalMois - $totalPrec) / $totalPrec) * 100)
                                        : null,
        ];

        // Docs par type
        $typeLabels = [
            'rapport_consultation'    => 'Rapport consultation',
            'compte_rendu_operatoire' => 'CR opératoire',
            'resultat_laboratoire'    => 'Résultat labo',
            'resultat_radiologie'     => 'Résultat radio',
            'ordonnance'              => 'Ordonnance',
            'courrier_medical'        => 'Courrier médical',
            'autre'                   => 'Autre',
        ];
        $typeCouleurs = [
            'rapport_consultation'    => '#2AB09A',
            'compte_rendu_operatoire' => '#534AB7',
            'resultat_laboratoire'    => '#F59E0B',
            'resultat_radiologie'     => '#3B82F6',
            'ordonnance'              => '#10B981',
            'courrier_medical'        => '#E8547A',
            'autre'                   => '#94A3B8',
        ];

        $docsParType = Document::selectRaw('typedocument, COUNT(*) as total')
            ->groupBy('typedocument')->orderByDesc('total')
            ->pluck('total', 'typedocument');

        $chartTypes = [
            'labels'   => $docsParType->keys()->map(fn($k) => $typeLabels[$k] ?? $k)->values()->toArray(),
            'data'     => $docsParType->values()->toArray(),
            'couleurs' => $docsParType->keys()->map(fn($k) => $typeCouleurs[$k] ?? '#94A3B8')->values()->toArray(),
        ];

        // Docs par service
        $docsParService = Document::selectRaw('service, COUNT(*) as total')
            ->groupBy('service')->orderByDesc('total')
            ->pluck('total', 'service');

        $serviceCouleurs = [
            'Cardiologie'=>'#E8547A','Chirurgie'=>'#534AB7','Pediatrie'=>'#3B82F6',
            'Neurologie'=>'#2AB09A','Laboratoire'=>'#F59E0B','Radiologie'=>'#10B981',
            'Urgences'=>'#F97316','Autre'=>'#94A3B8',
        ];

        $chartServices = [
            'labels'   => $docsParService->keys()->values()->toArray(),
            'data'     => $docsParService->values()->toArray(),
            'couleurs' => $docsParService->keys()->map(fn($k) => $serviceCouleurs[$k] ?? '#94A3B8')->values()->toArray(),
        ];

        // Uploads par mois
        $uploadsRaw = Document::selectRaw("TO_CHAR(date_import, 'YYYY-MM') as mois, COUNT(*) as total")
            ->where('date_import', '>=', $debut)
            ->groupBy('mois')->orderBy('mois')
            ->pluck('total', 'mois');

        $moisLabels = [];
        $moisData   = [];
        for ($i = $nbMois - 1; $i >= 0; $i--) {
            $m          = now()->subMonths($i)->format('Y-m');
            $moisLabels[] = now()->subMonths($i)->locale('fr')->isoFormat('MMM YY');
            $moisData[]   = (int) ($uploadsRaw[$m] ?? 0);
        }

        $chartMois = ['labels' => $moisLabels, 'data' => $moisData];

        // Actions journal (déduit de la description)
        // Pas de colonne 'action' dans la nouvelle BD → tableau PHP simple (pas Collection)
        $allDesc = LogActivite::pluck('description');
        $actionsArr = [
            'CONNEXION'    => 0,
            'UPLOAD'       => 0,
            'CONSULTATION' => 0,
            'CREATION'     => 0,
            'MODIFICATION' => 0,
            'RECHERCHE'    => 0,
        ];
        foreach ($allDesc as $d) {
            $d = strtoupper($d ?? '');
            if (str_contains($d, 'CONNEXION'))        $actionsArr['CONNEXION']++;
            elseif (str_contains($d, 'UPLOAD'))       $actionsArr['UPLOAD']++;
            elseif (str_contains($d, 'CONSULTATION')) $actionsArr['CONSULTATION']++;
            elseif (str_contains($d, 'CREATION'))     $actionsArr['CREATION']++;
            elseif (str_contains($d, 'MODIFICATION')) $actionsArr['MODIFICATION']++;
            elseif (str_contains($d, 'RECHERCHE'))    $actionsArr['RECHERCHE']++;
        }
        // Convertir en Collection seulement après les incrémentations
        $actionsCount = collect($actionsArr)->filter(fn($v) => $v > 0)->sortDesc();

        $actionCouleurs = [
            'CONNEXION'=>'#2AB09A','UPLOAD'=>'#534AB7','CONSULTATION'=>'#3B82F6',
            'CREATION'=>'#10B981','MODIFICATION'=>'#E8547A','RECHERCHE'=>'#F59E0B',
        ];

        $chartActions = [
            'labels'   => $actionsCount->keys()->toArray(),
            'data'     => $actionsCount->values()->toArray(),
            'couleurs' => $actionsCount->keys()->map(fn($k) => $actionCouleurs[$k] ?? '#94A3B8')->values()->toArray(),
        ];

        // Top patients
        $topPatients = Patient::withCount('documents')
            ->orderByDesc('documents_count')->limit(10)->get()
            ->filter(fn($p) => $p->documents_count > 0)->take(5);

        // Classement utilisateurs
        $classementUtilisateurs = Utilisateur::withCount('logActivites')
            ->orderByDesc('log_activites_count')->get();

        // Uploads récents
        $uploadsRecents = Document::with(['patient', 'utilisateur'])
            ->where('date_import', '>=', now()->subDays(7)->toDateString())
            ->orderByDesc('date_import')->limit(8)->get();

        return view('rapports.index', compact(
            'cartes', 'chartTypes', 'chartServices', 'chartMois', 'chartActions',
            'topPatients', 'classementUtilisateurs', 'uploadsRecents',
            'periode', 'typeLabels', 'moi'
        ));
    }

    public function exportCsv(): Response
    {
        $csv  = "\xEF\xBB\xBF";
        $csv .= "=== RAPPORT STATISTIQUE GED MÉDICALE — CHR AL GHASSANI ===\n";
        $csv .= "Généré le;" . now()->locale('fr')->isoFormat('dddd D MMMM YYYY à H:mm') . "\n\n";

        $total = Document::count();

        $csv .= "RÉSUMÉ GÉNÉRAL\n";
        $csv .= "Indicateur;Valeur\n";
        $csv .= "Total patients;" . Patient::count() . "\n";
        $csv .= "Total documents;" . $total . "\n";
        $csv .= "Imports ce mois;" . Document::whereMonth('date_import', now()->month)->whereYear('date_import', now()->year)->count() . "\n";
        $csv .= "Utilisateurs actifs;" . Utilisateur::where('actif', true)->count() . "\n";
        $csv .= "Entrées journal;" . LogActivite::count() . "\n\n";

        $csv .= "DOCUMENTS PAR TYPE\n";
        $csv .= "Type;Nombre;%\n";
        Document::selectRaw('typedocument, COUNT(*) as total')->groupBy('typedocument')->orderByDesc('total')->get()
            ->each(fn($r) => $csv .= '"'.$r->typedocument.'";'.$r->total.';'.($total > 0 ? round($r->total/$total*100,1) : 0)."%\n");

        $csv .= "\nDOCUMENTS PAR SERVICE\n";
        $csv .= "Service;Nombre;%\n";
        Document::selectRaw('service, COUNT(*) as total')->groupBy('service')->orderByDesc('total')->get()
            ->each(fn($r) => $csv .= '"'.$r->service.'";'.$r->total.';'.($total > 0 ? round($r->total/$total*100,1) : 0)."%\n");

        $csv .= "\nUPLOADS PAR MOIS (12 DERNIERS MOIS)\n";
        $csv .= "Mois;Imports\n";
        $uploadsRaw = Document::selectRaw("TO_CHAR(date_import, 'YYYY-MM') as mois, COUNT(*) as total")
            ->where('date_import', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->groupBy('mois')->orderBy('mois')->pluck('total', 'mois');
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i)->format('Y-m');
            $csv .= now()->subMonths($i)->format('m/Y').';'.($uploadsRaw[$m] ?? 0)."\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rapport_ged_'.now()->format('Ymd').'.csv"',
        ]);
    }
}
