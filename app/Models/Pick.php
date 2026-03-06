<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pick extends Model
{
    protected $fillable = ['date', 'user_id', 'guest_name', 'cause_id'];

    protected $casts = ['date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cause(): BelongsTo
    {
        return $this->belongsTo(Cause::class);
    }

    public function isGuest(): bool
    {
        return is_null($this->user_id);
    }

    public function pickerName(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Guest';
    }

    public function dailyAmount(): float
    {
        return Setup::rateFor($this->date);
    }

    public static function todayOrCreate(): ?self
    {
        $nextUser = User::nextInRotation();

        if (!$nextUser) {
            return null;
        }

        return self::firstOrCreate(
            ['date' => today()->toDateString()],
            ['user_id' => User::nextInRotation()?->id]
        );
    }
}