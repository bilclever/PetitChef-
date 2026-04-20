<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $dishes = Schema::hasTable('dishes')
            ? Dish::with('cook')
                ->available()
                ->latest()
                ->get()
            : collect();

        return view('menu.index', compact('dishes'));
    }
}
