<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderReceived implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public mixed $order) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kitchen.'.(int) data_get($this->order, 'cook_id')),
            new PrivateChannel('admin.stream'),
        ];
    }

    public function broadcastAs(): string { return 'NewOrderReceived'; }

    public function broadcastWith(): array
    {
        return [
            'order_id'    => (int) data_get($this->order, 'id'),
            'cook_id'     => (int) data_get($this->order, 'cook_id'),
            'client_id'   => (int) data_get($this->order, 'client_id'),
            'client_name' => (string) (data_get($this->order, 'client.name') ?? ''),
            'total_price' => (int) data_get($this->order, 'total_price'),
            'status'      => 'recue',
            'order_url'   => route('cook.orders.show', ['order' => (int) data_get($this->order, 'id')]),
        ];
    }
}
