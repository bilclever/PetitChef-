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

        // R√©cup√©rer les plats du jour disponibles pour ajout rapide
        $availableDishes = Dish::available()->with('cook')->latest()->take(6)->get();

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
            'availableDishes' => $availableDishes,
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
            return back()->withErrors(['quantity' => 'Quantit√© demand√©e sup√©rieure au stock disponible.']);
        }

        $cart = session('cart', []);
        $previousQuantity = $cart[$dish->id] ?? 0;
        $cart[$dish->id] = min($dish->quantity, ($cart[$dish->id] ?? 0) + $quantity);
        $addedQuantity = $cart[$dish->id] - $previousQuantity;

        session(['cart' => $cart]);

        return back()->with('status', "Plat ajout√© au panier ({$addedQuantity} √ó {$dish->name}). Total : " . array_sum($cart) . " article(s).");
    }

    public function remove(Request $request): RedirectResponse
    {
        $request->validate([
            'dish_id' => ['required', 'integer', 'exists:dishes,id'],
        ]);

        $cart = session('cart', []);
        unset($cart[$request->integer('dish_id')]);
        session(['cart' => $cart]);

        return back()->with('status', 'Article supprim√© du panier.');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'dish_id' => ['required', 'integer', 'exists:dishes,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $dish = Dish::findOrFail($request->integer('dish_id'));
        $quantity = $request->integer('quantity');

        if ($quantity > $dish->quantity) {
            return back()->withErrors(['quantity' => 'Quantit√© demand√©e sup√©rieure au stock disponible.']);
        }

        $cart = session('cart', []);
        if ($quantity <= 0) {
            unset($cart[$dish->id]);
        } else {
            $cart[$dish->id] = $quantity;
        }

        session(['cart' => $cart]);

        return back()->with('status', 'Quantit√© mise √† jour.');
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
                        'cart' => 'Un plat du panier n‚Äôest plus disponible. Rafra√Æchis la page et r√©essaie.',
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

        return redirect()->route('cart.index')->with('status', 'Commande enregistr√©e.');
    }

    public function index(): View
    {
        $orders = auth()->user()->orders()->with('dishes')->latest()->get();

        return view('cart.index', [
            'orders' => $orders,
            'items' => collect(session('cart', []))->map(fn ($quantity, $dishId) => null),
        ]);
    }
    public function track(Order $order): View
    {
        // VÈrifier que l'utilisateur est propriÈtaire de la commande
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['order_dishes.dish', 'user']);

        // DÈfinir les Ètapes du suivi selon le statut
        $steps = $this->getTrackingSteps($order);

        return view('orders.track', compact('order', 'steps'));
    }

    public function cancel(Order $order): RedirectResponse
    {
        // VÈrifier que l'utilisateur est propriÈtaire de la commande
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Ne permettre l'annulation que si la commande est en attente
        if ($order->status !== 'pending') {
            return back()->withErrors(['order' => 'Cette commande ne peut plus Ítre annulÈe.']);
        }

        // Remettre les quantitÈs en stock
        foreach ($order->order_dishes as $orderDish) {
            $orderDish->dish->increment('quantity', $orderDish->quantity);
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('status', 'Commande annulÈe avec succËs.');
    }

    private function getTrackingSteps(Order $order): array
    {
        $steps = [
            [
                'status' => 'pending',
                'title' => 'Commande reÁue',
                'description' => 'Votre commande a ÈtÈ enregistrÈe et est en attente de confirmation.',
                'icon' => '??',
                'completed' => in_array($order->status, ['pending', 'confirmed', 'ready', 'delivered']),
                'current' => $order->status === 'pending',
                'timestamp' => $order->created_at,
            ],
            [
                'status' => 'confirmed',
                'title' => 'Commande confirmÈe',
                'description' => 'Votre commande a ÈtÈ confirmÈe par le cuisinier et est en cours de prÈparation.',
                'icon' => '?',
                'completed' => in_array($order->status, ['confirmed', 'ready', 'delivered']),
                'current' => $order->status === 'confirmed',
                'timestamp' => $order->status === 'confirmed' ? $order->updated_at : null,
            ],
            [
                'status' => 'ready',
                'title' => 'PrÍt ‡ rÈcupÈrer',
                'description' => 'Votre commande est prÍte et vous pouvez venir la rÈcupÈrer.',
                'icon' => '???',
                'completed' => in_array($order->status, ['ready', 'delivered']),
                'current' => $order->status === 'ready',
                'timestamp' => $order->status === 'ready' ? $order->updated_at : null,
            ],
            [
                'status' => 'delivered',
                'title' => 'LivrÈ',
                'description' => 'Votre commande a ÈtÈ livrÈe avec succËs.',
                'icon' => '??',
                'completed' => $order->status === 'delivered',
                'current' => $order->status === 'delivered',
                'timestamp' => $order->status === 'delivered' ? $order->updated_at : null,
            ],
        ];

        return $steps;
    }
}
