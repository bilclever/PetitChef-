<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public array $user,
        public string $eventType,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.stream')];
    }

    public function broadcastAs(): string
    {
        return 'UserChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'event_type' => $this->eventType,
            'user_id' => (int) ($this->user['id'] ?? 0),
            'name' => (string) ($this->user['name'] ?? ''),
            'email' => (string) ($this->user['email'] ?? ''),
            'phone' => $this->user['phone'] ?? null,
            'role' => (string) ($this->user['role'] ?? ''),
            'approval_status' => $this->user['approval_status'] ?? null,
            'account_status' => $this->user['account_status'] ?? null,
            'account_status_reason' => $this->user['account_status_reason'] ?? null,
            'updated_at' => $this->user['updated_at'] ?? null,
            'created_at' => $this->user['created_at'] ?? null,
        ];
    }
}
