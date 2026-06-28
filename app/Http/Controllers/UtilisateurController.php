<?php

namespace App\Http\Controllers;

use App\Models\LogActivite;
use App\Models\Utilisateur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    private const ROLES = ['medecin', 'infirmier', 'administratif'];

    // LISTE
    public function index(Request $request): View
    {
        $query = Utilisateur::orderBy('nom')->orderBy('prenom');

        if ($request->filled('search')) {
            $term = '%' . strtolower($request->input('search')) . '%';
            $query->where(fn($q) =>
                $q->whereRaw('LOWER(nom)    LIKE ?', [$term])
                  ->orWhereRaw('LOWER(prenom) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(email)  LIKE ?', [$term])
            );
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('actif')) {
            $query->where('actif', (bool) $request->input('actif'));
        }

        $utilisateurs = $query->paginate(15)->withQueryString();

        $stats = [
            'total'    => Utilisateur::count(),
            'actifs'   => Utilisateur::where('actif', true)->count(),
            'inactifs' => Utilisateur::where('actif', false)->count(),
            'par_role' => Utilisateur::selectRaw('role, COUNT(*) as total')
                              ->groupBy('role')
                              ->pluck('total', 'role'),
        ];

        return view('utilisateurs.index', compact('utilisateurs', 'stats'));
    }

    // CRÉER
    public function create(): View
    {
        return view('utilisateurs.create', ['roles' => self::ROLES]);
    }

    // STORE
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom'      => ['required', 'string', 'max:100'],
            'prenom'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:191', 'unique:utilisateurs,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', Rule::in(self::ROLES)],
            'actif'    => ['sometimes', 'boolean'],
        ], [
            'nom.required'       => 'Le nom est obligatoire.',
            'prenom.required'    => 'Le prénom est obligatoire.',
            'email.required'     => 'L\'e-mail est obligatoire.',
            'email.email'        => 'L\'e-mail n\'est pas valide.',
            'email.unique'       => 'Cet e-mail est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit faire au moins 8 caractères.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'role.required'      => 'Le rôle est obligatoire.',
        ]);

        $utilisateur = Utilisateur::create([
            'nom'      => $data['nom'],
            'prenom'   => $data['prenom'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'actif'    => $request->boolean('actif', true),
        ]);

        LogActivite::enregistrer(
            Auth::user()->id_utilisateur,
            "CREATION compte utilisateur : {$utilisateur->prenom} {$utilisateur->nom} ({$utilisateur->role})"
        );

        return redirect()->route('utilisateurs.index')
            ->with('success', "Compte créé pour {$utilisateur->prenom} {$utilisateur->nom}.");
    }

    // EDIT
    public function edit(int $id): View
    {
        return view('utilisateurs.edit', [
            'utilisateur' => Utilisateur::findOrFail($id),
            'roles'       => self::ROLES,
        ]);
    }

    // UPDATE
    public function update(Request $request, int $id): RedirectResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $moi = Auth::user();

        $data = $request->validate([
            'nom'          => ['required', 'string', 'max:100'],
            'prenom'       => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:191',
                               Rule::unique('utilisateurs', 'email')->ignore($id, 'id_utilisateur')],
            'role'         => ['required', Rule::in(self::ROLES)],
            'actif'        => ['sometimes', 'boolean'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique'          => 'Cet e-mail est déjà utilisé par un autre compte.',
            'new_password.min'      => 'Le nouveau mot de passe doit faire au moins 8 caractères.',
            'new_password.confirmed'=> 'Les deux mots de passe ne correspondent pas.',
        ]);

        // Protection : ne pas se désactiver soi-même
        $nouvelActif = $request->boolean('actif', true);
        if ($utilisateur->id_utilisateur === $moi->id_utilisateur && !$nouvelActif) {
            return back()->withErrors(['actif' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        $utilisateur->nom    = $data['nom'];
        $utilisateur->prenom = $data['prenom'];
        $utilisateur->email  = $data['email'];
        $utilisateur->role   = $data['role'];
        $utilisateur->actif  = $nouvelActif;

        if (!empty($data['new_password'])) {
            $utilisateur->password = Hash::make($data['new_password']);
        }

        $utilisateur->save();

        LogActivite::enregistrer(
            $moi->id_utilisateur,
            "MODIFICATION compte : {$utilisateur->prenom} {$utilisateur->nom} (rôle={$utilisateur->role})"
        );

        return redirect()->route('utilisateurs.index')
            ->with('success', "Compte de {$utilisateur->prenom} {$utilisateur->nom} mis à jour.");
    }

    // DESTROY (suppression définitive)
    public function destroy(int $id): RedirectResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $moi = Auth::user();

        // Impossible de se supprimer soi-même
        if ($utilisateur->id_utilisateur === $moi->id_utilisateur) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $nomComplet = "{$utilisateur->prenom} {$utilisateur->nom}";
        $role       = $utilisateur->role;

        $utilisateur->delete();

        LogActivite::enregistrer(
            $moi->id_utilisateur,
            "SUPPRESSION compte utilisateur : {$nomComplet} ({$role})"
        );

        return redirect()->route('utilisateurs.index')
            ->with('success', "Compte de {$nomComplet} supprimé définitivement.");
    }

    // TOGGLE ACTIF
    public function toggleActif(int $id): RedirectResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $moi = Auth::user();

        if ($utilisateur->id_utilisateur === $moi->id_utilisateur) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $utilisateur->actif = !$utilisateur->actif;
        $utilisateur->save();

        $etat = $utilisateur->actif ? 'activé' : 'désactivé';

        LogActivite::enregistrer(
            $moi->id_utilisateur,
            "Compte {$etat} : {$utilisateur->prenom} {$utilisateur->nom}"
        );

        return back()->with('success', "Compte de {$utilisateur->prenom} {$utilisateur->nom} {$etat}.");
    }

    // CHANGE ROLE (AJAX-friendly, depuis la liste)
    public function changeRole(Request $request, int $id): RedirectResponse
    {
        $utilisateur = Utilisateur::findOrFail($id);
        $moi = Auth::user();

        $data = $request->validate([
            'role' => ['required', Rule::in(self::ROLES)],
        ]);

        $ancienRole = $utilisateur->role;
        $utilisateur->role = $data['role'];
        $utilisateur->save();

        LogActivite::enregistrer(
            $moi->id_utilisateur,
            "Changement de rôle : {$utilisateur->prenom} {$utilisateur->nom} : {$ancienRole} → {$data['role']}"
        );

        return back()->with('success', "Rôle de {$utilisateur->prenom} {$utilisateur->nom} modifié.");
    }
}
