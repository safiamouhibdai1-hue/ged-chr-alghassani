<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\LogActivite;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientController extends Controller
{
    private const SERVICES = [
        'Cardiologie', 'Chirurgie', 'Pediatrie',
        'Neurologie', 'Laboratoire', 'Radiologie',
        'Urgences', 'Autre',
    ];

    private const TYPES_DOCS = [
        'rapport_consultation'    => 'Rapport consultation',
        'compte_rendu_operatoire' => 'C.R. opératoire',
        'resultat_laboratoire'    => 'Résultat labo',
        'resultat_radiologie'     => 'Résultat radio',
        'ordonnance'              => 'Ordonnance',
        'courrier_medical'        => 'Courrier médical',
        'autre'                   => 'Autre',
    ];

    // LISTE
    public function index(Request $request): View
    {
        $query = Patient::withCount('documents')
                        ->orderBy('nom')->orderBy('prenom');

        if ($request->filled('search')) {
            $term = '%' . strtolower(trim($request->input('search'))) . '%';
            $query->where(fn($q) =>
                $q->whereRaw('LOWER(nom) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(prenom) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(cin) LIKE ?', [$term])
                  ->orWhereRaw('LOWER("numero_dossier") LIKE ?', [$term])
            );
        }

        if ($request->filled('service')) {
            $query->where('service', $request->input('service'));
        }

        $patients      = $query->paginate(15)->withQueryString();
        $totalPatients = Patient::count();

        return view('patients.index', [
            'patients'      => $patients,
            'services'      => self::SERVICES,
            'totalPatients' => $totalPatients,
        ]);
    }

    // DÉTAIL
    public function show(int $ipp): View
    {
        $patient = Patient::with(['documents.utilisateur'])->findOrFail($ipp);

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "CONSULTATION dossier patient #{$ipp} — {$patient->nom_complet}"
        );

        $docs = $patient->documents->sortByDesc('date_import');

        $stats = [
            'total_docs'    => $docs->count(),
            'dernier_doc'   => $docs->first()?->date_import,
            'docs_par_type' => $docs->groupBy('typedocument')->map->count(),
        ];

        return view('patients.show', [
            'patient'  => $patient,
            'stats'    => $stats,
            'typesDocs'=> self::TYPES_DOCS,
        ]);
    }

    // CRÉER (infirmier + administratif)
    public function create(): View
    {
        $user = Auth::user();

        // Seul l'infirmier ou l'administratif peut créer des patients
        if (!$user->isInfirmier() && !$user->isAdministratif()) {
            abort(403, 'Accès non autorisé.');
        }

        $dernierNum    = Patient::count() + 1;
        $numeroDossier = 'DOSS-' . now()->year . '-' . str_pad($dernierNum, 4, '0', STR_PAD_LEFT);

        return view('patients.create', [
            'services'      => self::SERVICES,
            'numeroDossier' => $numeroDossier,
        ]);
    }

    // STORE
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isInfirmier() && !$user->isAdministratif()) {
            abort(403);
        }

        $validated = $request->validate([
            'ipp'            => ['required', 'integer', 'min:1', 'unique:patients,ipp'],
            'cin'            => ['required', 'string', 'max:20', 'unique:patients,cin'],
            'numero_dossier' => ['required', 'string', 'max:50', 'unique:patients,numero_dossier'],
            'nom'            => ['required', 'string', 'max:100'],
            'prenom'         => ['required', 'string', 'max:100'],
            'date_naissance' => ['required', 'date', 'before:today'],
            'service'        => ['required', 'string', 'in:' . implode(',', self::SERVICES)],
        ], [
            'ipp.required'            => 'Le numéro IPP est obligatoire.',
            'ipp.unique'              => 'Ce numéro IPP est déjà utilisé.',
            'cin.required'            => 'Le CIN est obligatoire.',
            'cin.unique'              => 'Ce CIN est déjà enregistré.',
            'numero_dossier.required' => 'Le numéro de dossier est obligatoire.',
            'numero_dossier.unique'   => 'Ce numéro de dossier est déjà utilisé.',
            'nom.required'            => 'Le nom est obligatoire.',
            'prenom.required'         => 'Le prénom est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.before'   => 'La date de naissance doit être dans le passé.',
            'service.required'        => 'Veuillez sélectionner un service.',
        ]);

        $validated['date_creation'] = now()->toDateString();
        $patient = Patient::create($validated);

        LogActivite::enregistrer(
            $user->id_utilisateur,
            "CREATION dossier patient #{$patient->ipp} — {$patient->nom_complet}"
        );

        return redirect()->route('patients.show', $patient->ipp)
            ->with('success', "Dossier de {$patient->nom_complet} créé avec succès.");
    }

    // EDIT / UPDATE (infirmier + administratif)
    public function edit(int $ipp): View
    {
        $user = Auth::user();
        if (!$user->isInfirmier() && !$user->isAdministratif()) {
            abort(403, 'Accès non autorisé.');
        }

        return view('patients.edit', [
            'patient'  => Patient::findOrFail($ipp),
            'services' => self::SERVICES,
        ]);
    }

    public function update(Request $request, int $ipp): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isInfirmier() && !$user->isAdministratif()) {
            abort(403);
        }

        $patient = Patient::findOrFail($ipp);

        $validated = $request->validate([
            'nom'              => ['required', 'string', 'max:100'],
            'prenom'           => ['required', 'string', 'max:100'],
            'date_naissance'   => ['required', 'date', 'before:today'],
            'service'          => ['required', 'string', 'in:' . implode(',', self::SERVICES)],
        ]);

        $patient->update($validated);

        LogActivite::enregistrer(
            $user->id_utilisateur,
            "MODIFICATION dossier patient #{$ipp} — {$patient->nom_complet}"
        );

        return redirect()->route('patients.show', $ipp)
            ->with('success', 'Dossier mis à jour avec succès.');
    }
}
