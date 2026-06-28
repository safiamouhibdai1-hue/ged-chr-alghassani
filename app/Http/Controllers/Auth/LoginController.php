<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LogActivite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        // Si déjà connecté, pas besoin de revoir la page de login
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // Validation des champs du formulaire
        $request->validate([
            'email'    => ['required', 'email', 'max:191'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required'    => 'L\'adresse e-mail est obligatoire.',
            'email.email'       => 'L\'adresse e-mail n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);

        // Protection brute-force : max 5 tentatives / minute par IP
        $rateLimitKey = 'login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "Trop de tentatives de connexion. Réessayez dans {$seconds} secondes.",
                ]);
        }

        // Tentative d'authentification
        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $utilisateur = Auth::user();

            // Vérifier que le compte est actif
            if (! $utilisateur->actif) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Votre compte a été désactivé. Contactez l\'administrateur.']);
            }

            // Succès : effacer le compteur brute-force
            RateLimiter::clear($rateLimitKey);

            // Régénérer la session (protection CSRF)
            $request->session()->regenerate();

            // Enregistrer la connexion dans le journal d'audit
            LogActivite::enregistrer(
                $utilisateur->id_utilisateur,
                'CONNEXION depuis ' . $request->ip()
            );

            // Rediriger vers le dashboard
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Bienvenue, ' . $utilisateur->prenom . ' !');
        }

        // Échec : incrémenter le compteur brute-force (fenêtre 60s)
        RateLimiter::hit($rateLimitKey, 60);

        $tentativesRestantes = 5 - RateLimiter::attempts($rateLimitKey);

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Identifiants incorrects. ' .
                           ($tentativesRestantes > 0
                               ? "Il vous reste {$tentativesRestantes} tentative(s)."
                               : 'Compte temporairement bloqué.'),
            ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $utilisateur = Auth::user();

        // Enregistrer la déconnexion dans le journal d'audit
        if ($utilisateur) {
            LogActivite::enregistrer(
                $utilisateur->id_utilisateur,
                'DECONNEXION depuis ' . $request->ip()
            );
        }

        // Détruire la session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Vous avez été déconnecté avec succès.');
    }
}
