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

Broadcast::channel('admin.stream', function ($user): bool {
    return $user->role === 'admin';
});

Broadcast::channel('client.{clientId}', function ($user, int $clientId): bool {
    return (int) $user->id === $clientId && $user->role === 'client';
});

// Canal public pour les mises à jour du menu (statut boutique, stock)
Broadcast::channel('menu.updates', function ($user): bool {
    return true; // accessible à tous les utilisateurs connectés
});
