<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\LogActivite;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private const MAX_TAILLE_KO = 10240;

    private const TYPES_DOCS = [
        'rapport_consultation'    => 'Rapport consultation',
        'compte_rendu_operatoire' => 'C.R. opératoire',
        'resultat_laboratoire'    => 'Résultat laboratoire',
        'resultat_radiologie'     => 'Résultat radiologie',
        'ordonnance'              => 'Ordonnance',
        'courrier_medical'        => 'Courrier médical',
        'autre'                   => 'Autre',
    ];

    private const SERVICES = [
        'Cardiologie', 'Chirurgie', 'Pediatrie',
        'Neurologie', 'Laboratoire', 'Radiologie',
        'Urgences', 'Autre',
    ];

    // LISTE
    public function index(Request $request): View
    {
        $query = Document::with(['patient', 'utilisateur'])
                         ->orderByDesc('date_import');

        if ($request->filled('search')) {
            $term = '%' . strtolower($request->input('search')) . '%';
            $query->where(fn($q) => $q->whereRaw('LOWER(titre) LIKE ?', [$term]));
        }

        if ($request->filled('type')) {
            $query->where('typedocument', $request->input('type'));
        }

        if ($request->filled('service')) {
            $query->where('service', $request->input('service'));
        }

        $documents = $query->paginate(15)->withQueryString();

        $compteurs = [
            'total'         => Document::count(),
            'ce_mois'       => Document::whereMonth('date_import', now()->month)
                                        ->whereYear('date_import', now()->year)->count(),
            'cette_semaine' => Document::whereBetween('date_import',
                                [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count(),
        ];

        if ($request->filled('search') || $request->filled('type') || $request->filled('service')) {
            LogActivite::enregistrer(
                Auth::user()->id_utilisateur,
                'RECHERCHE documents : ' . trim(
                    ($request->input('search') ?? '') . ' ' .
                    ($request->input('type') ?? '') . ' ' .
                    ($request->input('service') ?? '')
                )
            );
        }

        return view('documents.index', [
            'documents' => $documents,
            'typesDocs' => self::TYPES_DOCS,
            'services'  => self::SERVICES,
            'compteurs' => $compteurs,
        ]);
    }

    // FORMULAIRE UPLOAD
    public function create(Request $request): View
    {
        $patients = Patient::orderBy('nom')->orderBy('prenom')
                           ->get(['ipp', 'nom', 'prenom', 'numero_dossier', 'service']);

        $ippPreselectionne       = $request->input('ipp');
        $patientPreselectionne   = $ippPreselectionne ? Patient::find($ippPreselectionne) : null;

        return view('documents.create', [
            'patients'              => $patients,
            'patientPreselectionne' => $patientPreselectionne,
            'typesDocs'             => self::TYPES_DOCS,
            'services'              => self::SERVICES,
        ]);
    }

    // STORE
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'ipp'          => ['required', 'integer', 'exists:patients,ipp'],
            'titre'        => ['required', 'string', 'max:200'],
            'typedocument' => ['required', 'string', 'in:' . implode(',', array_keys(self::TYPES_DOCS))],
            'service'      => ['required', 'string', 'in:' . implode(',', self::SERVICES)],
            'fichier'      => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:' . self::MAX_TAILLE_KO],
        ], [
            'ipp.required'          => 'Veuillez sélectionner un patient.',
            'ipp.exists'            => 'Patient introuvable.',
            'titre.required'        => 'Le titre est obligatoire.',
            'typedocument.required' => 'Le type de document est obligatoire.',
            'service.required'      => 'Le service est obligatoire.',
            'fichier.required'      => 'Veuillez sélectionner un fichier.',
            'fichier.mimes'         => 'Formats acceptés : PDF, JPG, PNG.',
            'fichier.max'           => 'Le fichier ne peut pas dépasser 10 Mo.',
        ]);

        $ipp       = $request->input('ipp');
        $fichier   = $request->file('fichier');
        $extension = $fichier->getClientOriginalExtension();
        $slug      = Str::slug($request->titre);
        $dossier   = "documents/{$ipp}/" . now()->year;
        $nomFichier = "{$slug}_" . now()->timestamp . ".{$extension}";

        $fichier->storeAs($dossier, $nomFichier, 'local');

        $document = Document::create([
            'ipp'                 => $ipp,
            'id_utilisateur'      => Auth::user()->id_utilisateur,
            'titre'               => $request->titre,
            'chemin_fichier'      => "{$dossier}/{$nomFichier}",
            'service'             => $request->service,
            'typedocument'        => $request->typedocument,
            'date_import'         => now()->toDateString(),
            'mots_cles'           => $request->mots_cles ?: null,
        ]);

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "UPLOAD document \"{$document->titre}\" pour patient IPP #{$ipp}",
            $document->id_docum
        );

        if ($request->input('redirect_to_patient')) {
            return redirect()->route('patients.show', $ipp)
                ->with('success', "Document « {$document->titre} » importé avec succès.");
        }

        return redirect()->route('documents.show', $document->id_docum)
            ->with('success', 'Document importé avec succès.');
    }

    // SHOW
    public function show(int $id): View
    {
        $document = Document::with(['patient', 'utilisateur'])->findOrFail($id);

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "CONSULTATION document #{$id} : « {$document->titre} »",
            $id
        );

        $fichierExiste = Storage::disk('local')->exists($document->chemin_fichier);

        return view('documents.show', compact('document', 'fichierExiste'));
    }

    // DOWNLOAD
    public function download(int $id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::disk('local')->exists($document->chemin_fichier)) {
            return back()->with('error', 'Fichier introuvable sur le serveur.');
        }

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "Téléchargement document #{$id} : « {$document->titre} »",
            $id
        );

        $nom = Str::slug($document->titre) . '.' . $document->extension;
        return response()->download(
            Storage::disk('local')->path($document->chemin_fichier),
            $nom
        );
    }

    // PREVIEW
    public function preview(int $id)
    {
        $document = Document::findOrFail($id);

        if (!Storage::disk('local')->exists($document->chemin_fichier)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "Aperçu en ligne document #{$id}",
            $id
        );

        $mime = match ($document->extension) {
            'pdf'        => 'application/pdf',
            'png'        => 'image/png',
            'jpg','jpeg' => 'image/jpeg',
            default      => 'application/octet-stream',
        };

        $chemin = Storage::disk('local')->path($document->chemin_fichier);

        return response()->file($chemin, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($chemin) . '"',
        ]);
    }

    // EDIT / UPDATE
    public function edit(int $id): View
    {
        $document = Document::with(['patient', 'utilisateur'])->findOrFail($id);

        return view('documents.edit', [
            'document'  => $document,
            'typesDocs' => self::TYPES_DOCS,
            'services'  => self::SERVICES,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $document = Document::findOrFail($id);

        $data = $request->validate([
            'titre'        => ['required', 'string', 'max:200'],
            'typedocument' => ['required', 'string', 'in:' . implode(',', array_keys(self::TYPES_DOCS))],
            'service'      => ['required', 'string'],
        ]);

        $document->update($data);

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "MODIFICATION métadonnées document #{$id} : « {$document->titre} »",
            $id
        );

        return redirect()->route('documents.show', $id)
            ->with('success', 'Document mis à jour avec succès.');
    }

    // CONFIRM DELETE — Page de validation préalable
    public function confirmDelete(int $id): View
    {
        if (!Auth::user()->isAdministratif()) {
            abort(403, 'Seul l\'administratif peut supprimer des documents.');
        }

        $document = Document::with(['patient', 'utilisateur'])->findOrFail($id);

        return view('documents.confirm-delete', compact('document'));
    }

    // DESTROY
    public function destroy(Request $request, int $id): RedirectResponse
    {
        if (!Auth::user()->isAdministratif()) {
            abort(403, 'Seul l\'administratif peut supprimer des documents.');
        }

        $document   = Document::findOrFail($id);
        $patientIpp = $document->ipp;
        $titre      = $document->titre;

        // Supprimer le fichier physique s'il existe
        if (Storage::disk('local')->exists($document->chemin_fichier)) {
            Storage::disk('local')->delete($document->chemin_fichier);
        }

        $document->delete();

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "SUPPRESSION document #{$id} : « {$titre} » — validée par l'administrateur"
        );

        return redirect()->route('patients.show', $patientIpp)
            ->with('success', "Le document « {$titre} » a été supprimé définitivement.");
    }
}
