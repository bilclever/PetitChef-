<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Ouvrir / fermer manuellement la boutique
     */
    public function toggle(): RedirectResponse
    {
        $user = auth()->user();
        $user->shop_is_open = ! $user->shop_is_open;
        $user->save();

        $msg = $user->shop_is_open
            ? '🟢 Votre boutique est maintenant ouverte.'
            : '🔴 Votre boutique est maintenant fermée.';

        return back()->with('status', $msg);
    }

    /**
     * Définir l'heure de clôture automatique
     */
    public function updateClosingTime(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shop_closes_at' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        auth()->user()->update([
            'shop_closes_at' => $validated['shop_closes_at'] ?: null,
        ]);

        $msg = $validated['shop_closes_at']
            ? "⏰ Clôture automatique définie à {$validated['shop_closes_at']}."
            : '⏰ Clôture automatique désactivée.';

        return back()->with('status', $msg);
    }
}
