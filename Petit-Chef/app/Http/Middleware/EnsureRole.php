<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Pas connecté → login
        if (! $user) {
            return redirect()->route('login');
        }

        // Mauvais rôle → rediriger vers son propre dashboard au lieu de 403
        if ($user->role !== $role) {
            return redirect()->route('dashboard')->with('status',
                'Accès refusé : cette section est réservée aux ' . $role . 's.'
            );
        }

        // Compte suspendu ou banni
        if (in_array($user->account_status ?? 'active', ['suspended', 'banned'], true)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte a été ' . ($user->account_status === 'banned' ? 'banni' : 'suspendu') . '.'
                    . ($user->account_status_reason ? ' Raison : ' . $user->account_status_reason : ''),
            ]);
        }

        // Cuisinier non approuvé
        if ($role === 'cook' && ($user->approval_status ?? 'pending') !== 'approved') {
            $status = $user->approval_status === 'rejected' ? 'rejeté' : 'en attente de validation';

            return redirect()->route('profile.edit')->with('status',
                "Votre compte cuisinier est $status. Vous ne pouvez pas accéder à l'espace cuisinier pour l'instant."
            );
        }

        return $next($request);
    }
}
