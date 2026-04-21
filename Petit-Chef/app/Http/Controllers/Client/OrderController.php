<?php

namespace App\Http\Controllers\Client;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pickup_time' => ['nullable', 'date', 'after:now'],
            'fulfillment_type' => ['required', 'in:pickup,delivery'],
            'payment_method' => ['required', 'in:cash,mobile_money,card'],
        ]);

        if (! Schema::hasTable('orders') || ! Schema::hasTable('order_dish')) {
            return back()->withErrors(['cart' => 'Le module commandes nest pas encore disponible.']);
        }

        $cart = session('cart', ['cook_id' => null, 'items' => []]);
        $items = is_array($cart['items'] ?? null) ? $cart['items'] : [];

        if (empty($items)) {
            return back()->withErrors(['cart' => 'Ton panier est vide.']);
        }

        try {
            /** @var Order $order */
            $order = DB::transaction(function () use ($items, $cart, $data): Order {
                $dishIds = collect($items)->pluck('dish_id')->map(fn ($id) => (int) $id)->values()->all();

                $dishes = Dish::query()
                    ->whereIn('id', $dishIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($dishes->count() !== count($dishIds)) {
                    throw ValidationException::withMessages([
                        'cart' => 'Un ou plusieurs plats du panier n existent plus.',
                    ]);
                }

                $cookId = null;
                $total = 0;
                $attach = [];

                foreach ($items as $item) {
                    $dishId = (int) ($item['dish_id'] ?? 0);
                    $requestedQty = (int) ($item['quantity'] ?? 0);
                    $dish = $dishes->get($dishId);

                    if (! $dish || $requestedQty <= 0) {
                        throw ValidationException::withMessages([
                            'cart' => 'Panier invalide. Merci de verifier les quantites.',
                        ]);
                    }

                    if (! $dish->is_active || $dish->quantity < $requestedQty) {
                        throw ValidationException::withMessages([
                            'cart' => 'Stock insuffisant pour le plat '.$dish->name.'.',
                        ]);
                    }

                    if ($cookId === null) {
                        $cookId = (int) $dish->cook_id;
                    }

                    if ((int) $dish->cook_id !== $cookId || ($cart['cook_id'] !== null && (int) $cart['cook_id'] !== (int) $dish->cook_id)) {
                        throw ValidationException::withMessages([
                            'cart' => 'Le panier ne peut contenir que des plats du meme cuisinier.',
                        ]);
                    }

                    // Vérifier que la boutique du cuisinier est ouverte
                    $cookUser = \App\Models\User::find($cookId);
                    if ($cookUser && ! $cookUser->isShopOpen()) {
                        throw ValidationException::withMessages([
                            'cart' => 'La boutique de ce cuisinier est actuellement fermée. Impossible de passer commande.',
                        ]);
                    }

                    $lineTotal = (int) $dish->price * $requestedQty;
                    $total += $lineTotal;

                    $attach[$dishId] = [
                        'quantity' => $requestedQty,
                        'unit_price' => (int) $dish->price,
                        'line_total' => $lineTotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $dish->quantity = (int) $dish->quantity - $requestedQty;
                    if ($dish->quantity <= 0) {
                        $dish->quantity = 0;
                        $dish->is_active = false;
                    }
                    $dish->save();
                }

                $order = Order::query()->create([
                    'client_id' => (int) auth()->id(),
                    'cook_id' => (int) $cookId,
                    'status' => 'recue',
                    'total_price' => $total,
                    'pickup_time' => $data['pickup_time'] ?? null,
                    'fulfillment_type' => $data['fulfillment_type'],
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'pending',
                    'is_paid' => false,
                    'payment_reference' => null,
                ]);

                $order->dishes()->attach($attach);

                return $order->load(['client', 'cook']);
            }, 3);
        } catch (ValidationException $e) {
            throw $e;
        }

        session()->forget('cart');
        event(new OrderStatusUpdated($order));

        return redirect()->route('menu')->with('status', 'Commande creee avec succes.');
    }

    public function pay(Order $order): RedirectResponse
    {
        abort_unless((int) $order->client_id === (int) auth()->id(), 403);

        if ($order->is_paid) {
            return back()->with('status', 'Cette commande est deja marquee comme payee.');
        }

        $order->update([
            'is_paid' => true,
            'payment_status' => 'paid',
            'payment_reference' => $order->payment_reference ?: 'SIM-'.now()->format('YmdHis').'-'.$order->id,
        ]);

        event(new OrderStatusUpdated($order));

        return back()->with('status', 'Paiement enregistre.');
    }

    public function show(Order $order): \Illuminate\View\View
    {
        abort_unless((int) $order->client_id === (int) auth()->id(), 403);

        return view('client.order-detail', [
            'order' => $order->load(['dishes', 'cook:id,name,phone']),
            'realtimeOrderIds' => [(int) $order->id],
            'realtimeKitchenId' => null,
        ]);
    }

    public function history(): \Illuminate\View\View
    {
        $orders = Order::query()
            ->where('client_id', auth()->id())
            ->with('dishes')
            ->latest()
            ->paginate(10);

        // IDs des commandes actives pour le temps réel
        $realtimeOrderIds = $orders->filter(fn ($o) => in_array($o->status, ['recue', 'en_preparation', 'prete']))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return view('client.orders-history', [
            'orders' => $orders,
            'realtimeOrderIds' => $realtimeOrderIds,
            'realtimeKitchenId' => null,
        ]);
    }
}
