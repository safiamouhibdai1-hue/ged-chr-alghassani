<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware CheckRole — contrôle l'accès selon le rôle de l'utilisateur
 *
 * Utilisation dans les routes :
 *   ->middleware('role:administratif')          // admins seulement
 *   ->middleware('role:medecin,administratif')  // médecins OU admins
 *   ->middleware('role:medecin,infirmier')      // soignants seulement
 *
 * Si l'utilisateur n'a pas le bon rôle → erreur 403 (accès refusé)
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        // L'utilisateur doit être connecté (CheckAuth doit passer en premier)
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $utilisateur = Auth::user();

        // Vérifier si le rôle de l'utilisateur est dans la liste autorisée
        if (! in_array($utilisateur->role, $roles)) {
            // Accès refusé → page 403
            abort(403, 'Accès refusé. Vous n\'avez pas les droits nécessaires pour cette page.');
        }

        return $next($request);
    }
}
