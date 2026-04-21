<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DishController extends Controller
{
    public function dashboard(): View
    {
        $cook   = auth()->user();
        $dishes = Dish::where('cook_id', $cook->id)->latest()->get();
        $orders = Order::where('cook_id', $cook->id)
            ->whereIn('status', ['recue', 'preparation', 'prete'])
            ->with('client')
            ->latest()
            ->get();

        $stats = [
            'commandes' => Order::where('cook_id', $cook->id)->count(),
            'livrees'   => Order::where('cook_id', $cook->id)->where('status', 'livree')->count(),
            'fcfa'      => Order::where('cook_id', $cook->id)->where('status', 'livree')->sum('total'),
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
            'photo'       => 'nullable|image|max:5120',
            'is_of_day'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        $data['cook_id']   = auth()->id();
        $data['is_of_day'] = $request->boolean('is_of_day');
        $data['served_date'] = $data['is_of_day'] ? today() : null;
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
            'photo'       => 'nullable|image|max:5120',
            'is_of_day'   => 'nullable|boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        $data['is_of_day'] = $request->boolean('is_of_day');
        $data['served_date'] = $data['is_of_day'] ? today() : null;
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
        $isOfDay = ! $dish->is_of_day;
        $dish->update([
            'is_of_day'   => $isOfDay,
            'served_date' => $isOfDay ? today() : null,
        ]);
        return back()->with('status', $isOfDay ? 'Plat du jour activé' : 'Plat du jour retiré');
    }

    /** Clôture du service : désactive tous les plats du jour restants */
    public function closeService(): RedirectResponse
    {
        $count = Dish::closeService();
        return redirect()->route('cook.dashboard')
            ->with('status', "{$count} plat(s) du jour désactivé(s). Service clôturé.");
    }

    /** Active / désactive manuellement un plat */
    public function toggleActive(Dish $dish): RedirectResponse
    {
        $this->authorizeOwner($dish);
        $dish->update(['is_active' => ! $dish->is_active]);
        return back()->with('status', $dish->is_active ? 'Plat réactivé.' : 'Plat désactivé.');
    }

    private function authorizeOwner(Dish $dish): void
    {
        abort_if($dish->cook_id !== auth()->id(), 403);
    }
}
