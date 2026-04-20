<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
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
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => (int) data_get($this->order, 'id'),
            'cook_id' => (int) data_get($this->order, 'cook_id'),
            'client_id' => (int) data_get($this->order, 'client_id'),
            'status' => (string) data_get($this->order, 'status'),
            'pickup_time' => data_get($this->order, 'pickup_time'),
            'total_price' => data_get($this->order, 'total_price'),
        ];
    }
}
