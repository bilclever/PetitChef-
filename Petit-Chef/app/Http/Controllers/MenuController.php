<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $dishes = Dish::with('cook')
            ->available()
            ->latest()
            ->get();

        return view('menu.index', compact('dishes'));
    }
}
