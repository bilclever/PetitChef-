<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $dishes = collect();

        if (Schema::hasTable('dishes')) {
            $dishes = Dish::with('cook')
                ->available()
                ->latest()
                ->get();
        }

        // Grouper par cuisinier avec statut boutique
        $cookGroups = $dishes->groupBy('cook_id')->map(function ($cookDishes) {
            $cook = $cookDishes->first()->cook;
            return [
                'cook'       => $cook,
                'is_open'    => $cook ? $cook->isShopOpen() : false,
                'closes_at'  => $cook->shop_closes_at ?? null,
                'dishes'     => $cookDishes,
            ];
        })->values();

        // IDs commandes actives pour le temps réel (client)
        $realtimeOrderIds = [];
        if (auth()->check() && auth()->user()->role === 'client' && Schema::hasTable('orders')) {
            $realtimeOrderIds = Order::query()
                ->where('client_id', auth()->id())
                ->whereIn('status', ['recue', 'en_preparation', 'prete'])
                ->latest()
                ->limit(20)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return view('menu.index', [
            'dishes'           => $dishes,
            'cookGroups'       => $cookGroups,
            'realtimeOrderIds' => $realtimeOrderIds,
            'realtimeKitchenId' => null,
        ]);
    }
}
