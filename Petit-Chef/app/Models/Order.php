<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = ['client_id', 'cook_id', 'status', 'total', 'pickup_time'];

    const STATUSES = ['recue', 'preparation', 'prete', 'livree'];

    const NEXT_STATUS = [
        'recue'       => 'preparation',
        'preparation' => 'prete',
        'prete'       => 'livree',
    ];

    const STATUS_LABELS = [
        'recue'       => 'Reçue',
        'preparation' => 'En préparation',
        'prete'       => 'Prête',
        'livree'      => 'Livrée',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function cook(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cook_id');
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class)
            ->withPivot('quantity', 'unit_price');
    }

    public function advance(): bool
    {
        $next = self::NEXT_STATUS[$this->status] ?? null;
        if (! $next) return false;
        $this->update(['status' => $next]);
        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
