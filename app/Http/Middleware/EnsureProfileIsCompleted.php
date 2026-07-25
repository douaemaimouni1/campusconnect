<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    /**
     * Redirige vers /onboarding si l'utilisateur connecté
     * n'a pas encore complété son profil.
     * Évite aussi de rester bloqué SUR /onboarding si le profil
     * est déjà complet.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->profile_completed && !$request->routeIs('onboarding')) {
            return redirect()->route('onboarding');
        }

        if ($user && $user->profile_completed && $request->routeIs('onboarding')) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}