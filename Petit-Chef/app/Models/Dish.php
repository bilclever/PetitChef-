<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dish extends Model
{
    protected $fillable = [
        'cook_id', 'name', 'description', 'price',
        'quantity', 'emoji', 'photo_path', 'is_of_day', 'is_active',
    ];

    protected $casts = [
        'is_of_day' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function cook(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cook_id');
    }

    /** Scope : plat(s) du jour actifs */
    public function scopeOfTheDay($query)
    {
        return $query->where('is_of_day', true)->where('is_active', true);
    }

    /** Scope : plats actifs avec stock */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('quantity', '>', 0);
    }
}
