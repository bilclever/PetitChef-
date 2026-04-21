<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DishController extends Controller
{
    public function show(Dish $dish): View
    {
        // Charger le cuisinier et ses autres plats disponibles
        $dish->load('cook');

        $cookDishes = Schema::hasTable('dishes')
            ? Dish::query()
                ->where('cook_id', $dish->cook_id)
                ->where('is_active', true)
                ->where('quantity', '>', 0)
                ->latest()
                ->get()
            : collect();

        $isOpen = $dish->cook?->isShopOpen() ?? false;

        return view('dish.show', [
            'dish'       => $dish,
            'cookDishes' => $cookDishes,
            'isOpen'     => $isOpen,
        ]);
    }
}
