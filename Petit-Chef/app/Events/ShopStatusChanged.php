<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShopStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $cookId,
        public string $cookName,
        public bool $isOpen,
        public ?string $closesAt = null,
    ) {}

    public function broadcastOn(): array
    {
        // Canal public — tous les visiteurs du menu peuvent recevoir
        return [new Channel('menu.updates')];
    }

    public function broadcastAs(): string { return 'ShopStatusChanged'; }

    public function broadcastWith(): array
    {
        return [
            'cook_id'    => $this->cookId,
            'cook_name'  => $this->cookName,
            'is_open'    => $this->isOpen,
            'closes_at'  => $this->closesAt,
        ];
    }
}
