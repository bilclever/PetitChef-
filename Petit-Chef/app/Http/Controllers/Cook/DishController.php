<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DishController extends Controller
{
    public function dashboard(): View
    {
        $cook = auth()->user();
        $dishes = Schema::hasTable('dishes')
            ? Dish::where('cook_id', $cook->id)->latest()->get()
            : collect();

        $orders = Schema::hasTable('orders')
            ? Order::query()
                ->with('client')
                ->where('cook_id', $cook->id)
                ->whereIn('status', ['recue', 'en_preparation', 'prete'])
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
            : collect();

        $ordersBase = Schema::hasTable('orders')
            ? Order::query()->where('cook_id', $cook->id)
            : null;

        $stats = [
            'commandes' => $ordersBase ? (clone $ordersBase)->count() : 0,
            'livrees'   => $ordersBase ? (clone $ordersBase)->where('status', 'livree')->count() : 0,
            'fcfa'      => $ordersBase ? (int) ((clone $ordersBase)->where('is_paid', true)->sum('total_price')) : 0,
            'plats'     => $dishes->count(),
        ];

        return view('cook.dashboard', [
            'dishes' => $dishes,
            'orders' => $orders,
            'stats'  => $stats,
            'realtimeOrderIds' => $orders->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'realtimeKitchenId' => (int) $cook->id,
        ]);
    }

    public function create(): View
    {
        return view('cook.dishes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('dishes')) {
            return back()->withInput()->withErrors([
                'name' => 'La table des plats n\'est pas encore disponible. Lance les migrations du module plats.',
            ]);
        }

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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Plat publié !',
                'redirect_to' => route('cook.dashboard'),
            ]);
        }

        return redirect()->route('cook.dashboard')->with('status', 'Plat publié !');
    }

    public function edit(int $dish): View
    {
        $dish = $this->loadDishOr404($dish);
        $this->authorizeOwner($dish);

        return view('cook.dishes.edit', compact('dish'));
    }

    public function update(Request $request, int $dish): RedirectResponse
    {
        $dish = $this->loadDishOr404($dish);
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
            // Supprimer l'ancienne photo si elle existe
            if ($dish->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($dish->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        $data['is_of_day'] = $request->boolean('is_of_day');
        unset($data['photo']);

        $dish->update($data);

        event(new \App\Events\DishUpdated($dish->fresh()));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Plat modifié !',
                'redirect_to' => route('cook.dashboard'),
            ]);
        }

        return redirect()->route('cook.dashboard')->with('status', 'Plat modifié !');
    }

    public function destroy(int $dish, Request $request): RedirectResponse
    {
        $dish = $this->loadDishOr404($dish);
        $this->authorizeOwner($dish);
        $dish->delete();

        return redirect()->route('cook.dashboard')->with('status', 'Plat supprimé.');
    }

    public function toggleOfDay(int $dish): RedirectResponse
    {
        $dish = $this->loadDishOr404($dish);
        $this->authorizeOwner($dish);
        $dish->update(['is_of_day' => ! $dish->is_of_day]);

        event(new \App\Events\DishUpdated($dish->fresh()));

        return back()->with('status', $dish->is_of_day ? '⭐ Plat du jour activé' : 'Plat du jour retiré');
    }

    private function loadDishOr404(int $dishId): Dish
    {
        if (! Schema::hasTable('dishes')) {
            abort(503, 'La table des plats n\'est pas encore disponible.');
        }

        return Dish::query()->findOrFail($dishId);
    }

    private function authorizeOwner(Dish $dish): void
    {
        abort_if($dish->cook_id !== auth()->id(), 403);
    }
}
