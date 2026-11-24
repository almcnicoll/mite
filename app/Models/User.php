<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'colour', 'picture', 'rotation_order', 'password', 'reminders_enabled'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'reminders_enabled' => 'boolean',
        'last_reminded_at'  => 'datetime',
    ];

    public function picks(): HasMany
    {
        return $this->hasMany(Pick::class);
    }

    public static function nextInRotation(): ?User
    {
        $lastUserPick = Pick::whereNotNull('user_id')
            ->orderByDesc('date')
            ->first();

        $maxOrder = self::max('rotation_order');

        if (!$lastUserPick) {
            return self::orderBy('rotation_order')->first();
        }

        $lastOrder = $lastUserPick->user->rotation_order;
        $nextOrder = $lastOrder >= $maxOrder ? 1 : $lastOrder + 1;

        return self::where('rotation_order', $nextOrder)->first();
    }
}