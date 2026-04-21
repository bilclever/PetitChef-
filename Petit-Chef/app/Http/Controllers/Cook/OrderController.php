<?php

namespace App\Http\Controllers\Cook;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function advance(Order $order): RedirectResponse
    {
        abort_unless((int) $order->cook_id === (int) auth()->id(), 403);

        $nextStatusMap = [
            'recue' => 'en_preparation',
            'en_preparation' => 'prete',
            'prete' => 'livree',
        ];

        $current = strtolower((string) $order->status);

        if (! isset($nextStatusMap[$current])) {
            return back()->withErrors(['orders' => 'Cette commande ne peut plus avancer.']);
        }

        $nextStatus = $nextStatusMap[$current];
        $payload = ['status' => $nextStatus];

        if ($nextStatus === 'livree' && ! $order->is_paid && $order->payment_method === 'cash') {
            $payload['is_paid'] = true;
            $payload['payment_status'] = 'paid';
        }

        $order->update($payload);

        event(new OrderStatusUpdated($order));

        return back()->with('status', 'Statut commande mis a jour.');
    }

    public function show(Order $order): \Illuminate\View\View
    {
        abort_unless((int) $order->cook_id === (int) auth()->id(), 403);
        
        return view('cook.order-detail', [
            'order' => $order->load(['dishes', 'client:id,name,phone']),
        ]);
    }
}
