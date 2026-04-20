<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DishController extends Controller
{
    public function dashboard(): View
    {
        $cook = auth()->user();
        $dishes = Dish::where('cook_id', $cook->id)->latest()->get();

        // Données fictives pour les commandes tant que le module n'existe pas
        $orders = collect();

        $stats = [
            'commandes' => 0,
            'livrees'   => 0,
            'fcfa'      => 0,
            'plats'     => $dishes->count(),
        ];

        return view('cook.dashboard', compact('dishes', 'orders', 'stats'));
    }

    public function create(): View
    {
        return view('cook.dishes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'quantity'    => 'required|integer|min:0',
            'emoji'       => 'nullable|string|max:10',
            'photo'       => 'nullable|image|max:5120',
            'is_of_day'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        $data['cook_id']   = auth()->id();
        $data['is_of_day'] = $request->boolean('is_of_day');
        unset($data['photo']);

        Dish::create($data);

        return redirect()->route('cook.dashboard')->with('status', 'Plat publié !');
    }

    public function edit(Dish $dish): View
    {
        $this->authorizeOwner($dish);
        return view('cook.dishes.edit', compact('dish'));
    }

    public function update(Request $request, Dish $dish): RedirectResponse
    {
        $this->authorizeOwner($dish);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'quantity'    => 'required|integer|min:0',
            'emoji'       => 'nullable|string|max:10',
            'photo'       => 'nullable|image|max:5120',
            'is_of_day'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        $data['is_of_day'] = $request->boolean('is_of_day');
        unset($data['photo']);

        $dish->update($data);

        return redirect()->route('cook.dashboard')->with('status', 'Plat modifié !');
    }

    public function destroy(Dish $dish): RedirectResponse
    {
        $this->authorizeOwner($dish);
        $dish->delete();
        return redirect()->route('cook.dashboard')->with('status', 'Plat supprimé.');
    }

    public function toggleOfDay(Dish $dish): RedirectResponse
    {
        $this->authorizeOwner($dish);
        $dish->update(['is_of_day' => ! $dish->is_of_day]);
        return back()->with('status', $dish->is_of_day ? '⭐ Plat du jour activé' : 'Plat du jour retiré');
    }

    private function authorizeOwner(Dish $dish): void
    {
        abort_if($dish->cook_id !== auth()->id(), 403);
    }
}
