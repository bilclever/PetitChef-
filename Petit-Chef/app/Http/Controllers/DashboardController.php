<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route(match (auth()->user()->role) {
            'admin' => 'admin.dashboard',
            'cook' => 'cook.dashboard',
            default => 'client.dashboard',
        });
    }

    public function client(): View
    {
        $orderIds = [];

        if (Schema::hasTable('orders')) {
            $orderIds = DB::table('orders')
                ->where('client_id', auth()->id())
                ->orderByDesc('created_at')
                ->limit(15)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
        }

        return view('dashboard', [
            'title' => 'Espace Client',
            'description' => 'Commander, suivre tes commandes et gérer ton compte.',
            'realtimeOrderIds' => $orderIds,
            'realtimeKitchenId' => null,
        ]);
    }

    public function cook(): View
    {
        $orderIds = [];

        if (Schema::hasTable('orders')) {
            $orderIds = DB::table('orders')
                ->where('cook_id', auth()->id())
                ->orderByDesc('created_at')
                ->limit(15)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
        }

        return view('dashboard', [
            'title' => 'Espace Cuisinier',
            'description' => 'Publier tes plats du jour et suivre l’activité de ton atelier.',
            'realtimeOrderIds' => $orderIds,
            'realtimeKitchenId' => (int) auth()->id(),
        ]);
    }

    public function admin(): View
    {
        return view('dashboard', [
            'title' => 'Espace Administrateur',
            'description' => 'Valider les comptes cuisiniers, modérer et superviser la plateforme.',
        ]);
    }
}