<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cause extends Model
{
    protected $fillable = ['name', 'email', 'notes', 'colour', 'picture'];

    public function picks(): HasMany
    {
        return $this->hasMany(Pick::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function totalPicked(): float
    {
        return $this->picks()
            ->whereNotNull('cause_id')
            ->get()
            ->sum(fn($pick) => Setup::rateFor($pick->date));
    }

    public function totalDonated(): float
    {
        return $this->donations()->sum('amount');
    }

    public function balance(): float
    {
        return $this->totalPicked() - $this->totalDonated();
    }
}