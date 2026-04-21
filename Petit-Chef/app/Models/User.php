<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'approval_status',
        'account_status',
        'account_status_reason',
        'profile_photo_path',
        'rejection_reason',
        'shop_is_open',
        'shop_closes_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return asset('storage/'.$this->profile_photo_path);
    }

    public function clientOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'client_id');
    }

    public function cookOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'cook_id');
    }

    /**
     * Vérifie si le cuisinier est actuellement ouvert
     */
    public function isShopOpen(): bool
    {
        if ($this->role !== 'cook') {
            return false;
        }

        // Fermé manuellement
        if (! $this->shop_is_open) {
            return false;
        }

        // Vérifier l'heure de clôture automatique
        if ($this->shop_closes_at) {
            $now = now()->format('H:i');
            return $now < $this->shop_closes_at;
        }

        return true;
    }
}
