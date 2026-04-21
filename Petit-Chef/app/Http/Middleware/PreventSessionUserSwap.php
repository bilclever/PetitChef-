<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventSessionUserSwap
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionKey = '_bound_auth_user_id';

        if (Auth::check()) {
            $currentUserId = (int) Auth::id();
            $boundUserId = $request->session()->get($sessionKey);

            if ($boundUserId === null) {
                $request->session()->put($sessionKey, $currentUserId);
            } elseif ((int) $boundUserId !== $currentUserId) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Session invalide detectee. Reconnecte-toi.',
                ]);
            }
        } elseif ($request->session()->has($sessionKey)) {
            $request->session()->forget($sessionKey);
        }

        return $next($request);
    }
}
