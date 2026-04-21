<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public array $report,
        public string $action = 'updated'
    ) {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.stream'),
            new PrivateChannel('client.'.(int) ($this->report['client_id'] ?? 0)),
        ];

        $cookId = (int) ($this->report['cook_id'] ?? 0);
        if ($cookId > 0) {
            $channels[] = new PrivateChannel('kitchen.'.$cookId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'ReportChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'report_id' => (int) ($this->report['id'] ?? 0),
            'client_id' => (int) ($this->report['client_id'] ?? 0),
            'cook_id' => (int) ($this->report['cook_id'] ?? 0),
            'order_id' => isset($this->report['order_id']) ? (int) $this->report['order_id'] : null,
            'type' => (string) ($this->report['type'] ?? ''),
            'description' => (string) ($this->report['description'] ?? ''),
            'status' => (string) ($this->report['status'] ?? 'open'),
            'admin_note' => $this->report['admin_note'] ?? null,
            'client_name' => $this->report['client_name'] ?? null,
            'cook_name' => $this->report['cook_name'] ?? null,
            'dish_name' => $this->report['dish_name'] ?? null,
            'created_at' => $this->report['created_at'] ?? null,
            'updated_at' => $this->report['updated_at'] ?? null,
        ];
    }
}
