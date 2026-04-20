<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

Broadcast::channel('order.{id}', function ($user, int $id): bool {
    $order = DB::table('orders')
        ->select('id', 'client_id')
        ->where('id', $id)
        ->first();

    return (bool) ($order && (int) $order->client_id === (int) $user->id);
});

Broadcast::channel('kitchen.{cookId}', function ($user, int $cookId): bool {
    return (int) $user->id === $cookId && $user->role === 'cook';
});
