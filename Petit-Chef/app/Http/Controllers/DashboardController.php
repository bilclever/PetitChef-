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
        $user = auth()->user();

        // Récupérer les vraies statistiques du client
        $ordersCount = $user->orders()->count();
        $totalSpent = $user->orders()->sum('total_amount');
        $totalDishesOrdered = $user->orders()->with('dishes')->get()->sum(function ($order) {
            return $order->dishes->sum('pivot.quantity');
        });

        $query = \App\Models\Dish::available()->with('cook');

        // Filtre par prix maximum
        if (request('max_price')) {
            $query->where('price', '<=', request('max_price'));
        }

        // Filtre par cuisinier
        if (request('cook_id')) {
            $query->where('cook_id', request('cook_id'));
        }

        // Tri
        switch (request('sort')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $dishes = $query->get();

        return view('dashboard', [
            'title' => 'Espace Client',
            'description' => 'Commander, suivre tes commandes et gérer ton compte.',
            'dishes' => $dishes,
            'stats' => [
                'orders_count' => $ordersCount,
                'total_spent' => $totalSpent,
                'total_dishes_ordered' => $totalDishesOrdered,
                'user_id' => $user->id,
            ],
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