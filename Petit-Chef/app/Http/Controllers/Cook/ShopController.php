<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Ouvrir / fermer manuellement la boutique
     */
    public function toggle(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $user->shop_is_open = ! $user->shop_is_open;
        $user->save();

        event(new \App\Events\ShopStatusChanged(
            cookId: (int) $user->id,
            cookName: (string) $user->name,
            isOpen: (bool) $user->shop_is_open,
            closesAt: $user->shop_closes_at,
        ));

        $msg = $user->shop_is_open
            ? '🟢 Votre boutique est maintenant ouverte.'
            : '🔴 Votre boutique est maintenant fermée.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'is_open' => (bool) $user->shop_is_open,
            ]);
        }

        return back()->with('status', $msg);
    }

    /**
     * Définir l'heure de clôture automatique
     */
    public function updateClosingTime(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'shop_closes_at' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        auth()->user()->update([
            'shop_closes_at' => $validated['shop_closes_at'] ?: null,
        ]);

        $user = auth()->user()->fresh();
        event(new \App\Events\ShopStatusChanged(
            cookId: (int) $user->id,
            cookName: (string) $user->name,
            isOpen: (bool) $user->isShopOpen(),
            closesAt: $user->shop_closes_at,
        ));

        $msg = $validated['shop_closes_at']
            ? "⏰ Clôture automatique définie à {$validated['shop_closes_at']}."
            : '⏰ Clôture automatique désactivée.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'closing_time' => $validated['shop_closes_at'] ?? null,
            ]);
        }

        return back()->with('status', $msg);
    }
}
