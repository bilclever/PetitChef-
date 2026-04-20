<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function cart(): View
    {
        $cart = session('cart', []);
        $dishIds = array_keys($cart);
        $dishes = Dish::whereIn('id', $dishIds)->get()->keyBy('id');

        return view('cart.index', [
            'items' => collect($cart)->map(function ($quantity, $dishId) use ($dishes) {
                $dish = $dishes->get($dishId);

                if (! $dish) {
                    return null;
                }

                return [
                    'dish' => $dish,
                    'quantity' => $quantity,
                    'subtotal' => $dish->price * $quantity,
                ];
            })->filter(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'dish_id' => ['required', 'integer', 'exists:dishes,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $dish = Dish::findOrFail($request->integer('dish_id'));
        $quantity = $request->integer('quantity');

        if ($quantity > $dish->quantity) {
            return back()->withErrors(['quantity' => 'Quantité demandée supérieure au stock disponible.']);
        }

        $cart = session('cart', []);
        $cart[$dish->id] = min($dish->quantity, ($cart[$dish->id] ?? 0) + $quantity);

        session(['cart' => $cart]);

        return back()->with('status', 'Plat ajouté au panier.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->validate([
            'dish_id' => ['required', 'integer', 'exists:dishes,id'],
        ]);

        $cart = session('cart', []);
        unset($cart[$request->integer('dish_id')]);
        session(['cart' => $cart]);

        return back()->with('status', 'Article supprimé du panier.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Votre panier est vide.']);
        }

        $request->validate([
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($cart, $request) {
            $dishes = Dish::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
            $total = 0;

            foreach ($cart as $dishId => $quantity) {
                $dish = $dishes->get($dishId);

                if (! $dish || $dish->quantity < $quantity || ! $dish->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Un plat du panier n’est plus disponible. Rafraîchis la page et réessaie.',
                    ]);
                }

                $total += $dish->price * $quantity;
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => 'pending',
                'total_amount' => $total,
                'delivery_address' => $request->input('delivery_address'),
                'note' => $request->input('note'),
            ]);

            foreach ($cart as $dishId => $quantity) {
                $dish = $dishes->get($dishId);
                $order->dishes()->attach($dish->id, [
                    'quantity' => $quantity,
                    'unit_price' => $dish->price,
                    'subtotal' => $dish->price * $quantity,
                ]);

                $dish->decrement('quantity', $quantity);
            }

            session()->forget('cart');
        });

        return redirect()->route('cart.index')->with('status', 'Commande enregistrée.');
    }

    public function index(): View
    {
        $orders = auth()->user()->orders()->with('dishes')->latest()->get();

        return view('cart.index', [
            'orders' => $orders,
            'items' => collect(session('cart', []))->map(fn ($quantity, $dishId) => null),
        ]);
    }
}
