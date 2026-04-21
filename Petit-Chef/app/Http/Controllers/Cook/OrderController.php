<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /** Avance le statut de la commande au suivant */
    public function advance(Order $order): RedirectResponse
    {
        abort_if($order->cook_id !== auth()->id(), 403);

        $order->advance();

        $label = Order::STATUS_LABELS[$order->fresh()->status] ?? '';

        return back()->with('status', "Commande #{$order->id} — {$label}");
    }
}
