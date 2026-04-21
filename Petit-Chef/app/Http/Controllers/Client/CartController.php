<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = $this->getCart();
        $totals = $this->computeTotals($cart);

        return view('client.cart', [
            'cart' => $cart,
            'subtotal' => $totals['subtotal'],
            'itemsCount' => $totals['items_count'],
        ]);
    }

    public function add(Request $request, int $dish): RedirectResponse
    {
        if (! Schema::hasTable('dishes')) {
            return back()->withErrors(['cart' => 'Le module plats nest pas disponible.']);
        }

        $payload = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $dishModel = Dish::query()->with('cook')->findOrFail($dish);

        if (! $dishModel->is_active || $dishModel->quantity <= 0) {
            return back()->withErrors(['cart' => 'Ce plat nest plus disponible.']);
        }

        // Vérifier que la boutique du cuisinier est ouverte
        if ($dishModel->cook && ! $dishModel->cook->isShopOpen()) {
            return back()->withErrors(['cart' => 'La boutique de ce cuisinier est actuellement fermée.']);
        }

        $cart = $this->getCart();

        if ($cart['cook_id'] !== null && (int) $cart['cook_id'] !== (int) $dishModel->cook_id) {
            return back()->withErrors(['cart' => 'Le panier ne peut contenir que des plats dun seul cuisinier. Vide le panier pour changer.']);
        }

        $requestedQty = (int) $payload['quantity'];
        $currentQty = (int) ($cart['items'][$dishModel->id]['quantity'] ?? 0);
        $newQty = $currentQty + $requestedQty;

        if ($newQty > (int) $dishModel->quantity) {
            return back()->withErrors(['cart' => 'Stock insuffisant pour ce plat.']);
        }

        $cart['cook_id'] = (int) $dishModel->cook_id;
        $cart['items'][$dishModel->id] = [
            'dish_id' => (int) $dishModel->id,
            'name' => (string) $dishModel->name,
            'emoji' => $dishModel->emoji,
            'photo_path' => $dishModel->photo_path,
            'price' => (int) $dishModel->price,
            'cook_id' => (int) $dishModel->cook_id,
            'cook_name' => (string) optional($dishModel->cook)->name,
            'quantity' => $newQty,
        ];

        session(['cart' => $cart]);

        return back()->with('status', 'Plat ajoute au panier.');
    }

    public function update(Request $request, int $dish): RedirectResponse
    {
        $payload = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $cart = $this->getCart();

        if (! isset($cart['items'][$dish])) {
            return back()->withErrors(['cart' => 'Article introuvable dans le panier.']);
        }

        $newQty = (int) $payload['quantity'];

        if ($newQty === 0) {
            unset($cart['items'][$dish]);
        } else {
            $dishModel = Dish::query()->find($dish);

            if (! $dishModel || ! $dishModel->is_active || $dishModel->quantity < $newQty) {
                return back()->withErrors(['cart' => 'Quantite demandee non disponible en stock.']);
            }

            $cart['items'][$dish]['quantity'] = $newQty;
        }

        if (empty($cart['items'])) {
            $cart['cook_id'] = null;
        }

        session(['cart' => $cart]);

        return back()->with('status', 'Panier mis a jour.');
    }

    public function remove(int $dish): RedirectResponse
    {
        $cart = $this->getCart();

        unset($cart['items'][$dish]);

        if (empty($cart['items'])) {
            $cart['cook_id'] = null;
        }

        session(['cart' => $cart]);

        return back()->with('status', 'Article retire du panier.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget('cart');

        return back()->with('status', 'Panier vide.');
    }

    private function getCart(): array
    {
        $cart = session('cart', ['cook_id' => null, 'items' => []]);

        $cart['cook_id'] = $cart['cook_id'] ?? null;
        $cart['items'] = is_array($cart['items'] ?? null) ? $cart['items'] : [];

        return $cart;
    }

    private function computeTotals(array $cart): array
    {
        $subtotal = 0;
        $itemsCount = 0;

        foreach ($cart['items'] as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (int) ($item['price'] ?? 0);
            $itemsCount += $qty;
            $subtotal += $qty * $price;
        }

        return ['subtotal' => $subtotal, 'items_count' => $itemsCount];
    }
}
