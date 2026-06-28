<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware CheckAuth — protège les pages réservées aux utilisateurs connectés
 *
 * Si l'utilisateur n'est PAS connecté → redirection vers /login
 * Si l'utilisateur EST connecté       → la requête continue normalement
 *
 * Comment ça marche :
 *   Chaque requête HTTP passe par ce "filtre" avant d'atteindre le contrôleur.
 *   C'est comme un portail de sécurité à l'entrée de chaque page protégée.
 */
class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur n'est pas authentifié → renvoyer vers /login
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        // Si l'utilisateur est bien connecté → laisser passer la requête
        return $next($request);
    }
}
