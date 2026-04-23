<?php

namespace App\Events;

use App\Models\Dish;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DishUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Dish $dish) {}

    public function broadcastOn(): array
    {
        return [new Channel('menu.updates')];
    }

    public function broadcastAs(): string { return 'DishUpdated'; }

    public function broadcastWith(): array
    {
        return [
            'dish_id'    => (int) $this->dish->id,
            'cook_id'    => (int) $this->dish->cook_id,
            'name'       => (string) $this->dish->name,
            'price'      => (int) $this->dish->price,
            'quantity'   => (int) $this->dish->quantity,
            'is_active'  => (bool) $this->dish->is_active,
            'is_of_day'  => (bool) $this->dish->is_of_day,
            'emoji'      => $this->dish->emoji,
            'photo_url'  => $this->dish->photo_path
                ? asset('storage/' . $this->dish->photo_path)
                : null,
            'description' => $this->dish->description,
        ];
    }
}
