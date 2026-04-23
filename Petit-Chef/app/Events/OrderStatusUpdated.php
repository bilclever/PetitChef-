<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public mixed $order)
    {
    }

    public function broadcastOn(): array
    {
        $orderId = (int) data_get($this->order, 'id');
        $cookId = (int) data_get($this->order, 'cook_id');

        return [
            new PrivateChannel('order.'.$orderId),
            new PrivateChannel('kitchen.'.$cookId),
            new PrivateChannel('admin.stream'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        $orderId = (int) data_get($this->order, 'id');

        return [
            'order_id' => $orderId,
            'cook_id' => (int) data_get($this->order, 'cook_id'),
            'client_id' => (int) data_get($this->order, 'client_id'),
            'client_name' => (string) (data_get($this->order, 'client.name') ?? ''),
            'status' => (string) data_get($this->order, 'status'),
            'pickup_time' => data_get($this->order, 'pickup_time'),
            'total_price' => data_get($this->order, 'total_price'),
            'created_at' => data_get($this->order, 'created_at'),
            'order_url' => $orderId > 0 ? route('cook.orders.show', ['order' => $orderId]) : null,
            'advance_url' => $orderId > 0 ? route('cook.orders.advance', ['order' => $orderId]) : null,
        ];
    }
}
