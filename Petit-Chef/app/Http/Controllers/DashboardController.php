<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route(match (auth()->user()->role) {
            'admin' => 'admin.dashboard',
            'cook' => 'cook.dashboard',
            default => 'menu',
        });
    }

    public function client(): View
    {
        $orderIds = [];
        $recentOrders = collect();
        $clientStats = [
            'total_orders' => 0,
            'open_orders' => 0,
            'total_paid_amount' => 0,
            'unpaid_orders' => 0,
            'cart_items' => 0,
            'cart_subtotal' => 0,
        ];

        if (Schema::hasTable('orders')) {
            $ordersBase = Order::query()->where('client_id', auth()->id());

            $recentOrders = (clone $ordersBase)
                ->latest()
                ->limit(8)
                ->get(['id', 'status', 'total_price', 'payment_status', 'is_paid', 'created_at']);

            $orderIds = $recentOrders
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $clientStats['total_orders'] = (clone $ordersBase)->count();
            $clientStats['open_orders'] = (clone $ordersBase)
                ->whereIn('status', ['recue', 'en_preparation', 'prete'])
                ->count();
            $clientStats['total_paid_amount'] = (int) (clone $ordersBase)
                ->where('is_paid', true)
                ->sum('total_price');
            $clientStats['unpaid_orders'] = (clone $ordersBase)
                ->where('is_paid', false)
                ->count();
        }

        $cart = session('cart', ['items' => []]);
        $cartItems = is_array($cart['items'] ?? null) ? $cart['items'] : [];

        foreach ($cartItems as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (int) ($item['price'] ?? 0);
            $clientStats['cart_items'] += $qty;
            $clientStats['cart_subtotal'] += ($qty * $price);
        }

        return view('dashboard', [
            'title' => 'Espace Client',
            'description' => 'Commander, suivre tes commandes et gérer ton compte.',
            'realtimeOrderIds' => $orderIds,
            'realtimeKitchenId' => null,
            'recentOrders' => $recentOrders,
            'clientStats' => $clientStats,
        ]);
    }

    public function cook(): View
    {
        $orderIds = [];

        if (Schema::hasTable('orders')) {
            $orderIds = DB::table('orders')
                ->where('cook_id', auth()->id())
                ->orderByDesc('created_at')
                ->limit(15)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();
        }

        return view('dashboard', [
            'title' => 'Espace Cuisinier',
            'description' => 'Publier tes plats du jour et suivre l activite de ton atelier.',
            'realtimeOrderIds' => $orderIds,
            'realtimeKitchenId' => (int) auth()->id(),
        ]);
    }

    public function admin(): View
    {
        return view('dashboard', [
            'title' => 'Espace Administrateur',
            'description' => 'Valider les comptes cuisiniers, moderer et superviser la plateforme.',
        ]);
    }
}