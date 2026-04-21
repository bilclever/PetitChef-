<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'client_id',
        'cook_id',
        'status',
        'total_price',
        'pickup_time',
        'fulfillment_type',
        'payment_method',
        'payment_status',
        'is_paid',
        'payment_reference',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'is_paid' => 'boolean',
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
        return $this->belongsToMany(Dish::class, 'order_dish')
            ->withPivot(['quantity', 'unit_price', 'line_total'])
            ->withTimestamps();
    }
}
