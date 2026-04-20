<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
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
        return view('dashboard', [
            'title' => 'Espace Client',
            'description' => 'Commander, suivre tes commandes et gérer ton compte.',
        ]);
    }

    public function cook(): View
    {
        return view('dashboard', [
            'title' => 'Espace Cuisinier',
            'description' => 'Publier tes plats du jour et suivre l’activité de ton atelier.',
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