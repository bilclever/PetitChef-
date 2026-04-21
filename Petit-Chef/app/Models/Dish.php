<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Dish extends Model
{
    protected $fillable = [
        'cook_id', 'name', 'description', 'price',
        'quantity', 'photo_path', 'is_of_day', 'is_active', 'served_date',
    ];

    protected $casts = [
        'is_of_day'   => 'boolean',
        'is_active'   => 'boolean',
        'served_date' => 'date',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function cook(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cook_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /** Plat(s) du jour actifs pour aujourd'hui */
    public function scopeOfTheDay($query)
    {
        return $query
            ->where('is_of_day', true)
            ->where('is_active', true)
            ->whereDate('served_date', today());
    }

    /** Plats actifs avec stock disponible */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('quantity', '>', 0);
    }

    // ─── Commande ─────────────────────────────────────────────────

    /**
     * Décrémente le stock dans une transaction pour éviter la survente.
     * Retourne le prix unitaire au moment de la commande.
     *
     * @throws \RuntimeException si le stock est insuffisant
     */
    public function order(int $qty = 1): int
    {
        return DB::transaction(function () use ($qty) {
            // Verrouillage pessimiste pour éviter les race conditions
            $dish = static::lockForUpdate()->findOrFail($this->id);

            if ($dish->quantity < $qty) {
                throw new \RuntimeException("Stock insuffisant pour le plat « {$dish->name} ».");
            }

            $dish->decrement('quantity', $qty);

            // Retourne le prix unitaire figé au moment de la commande
            return $dish->price;
        });
    }

    // ─── Clôture de service ───────────────────────────────────────

    /**
     * Désactive tous les plats du jour restants (action de masse).
     * À appeler en fin de service.
     */
    public static function closeService(): int
    {
        return static::where('is_of_day', true)
            ->where('is_active', true)
            ->whereDate('served_date', today())
            ->update(['is_active' => false]);
    }
}
