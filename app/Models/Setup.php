<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setup extends Model
{
    protected $fillable = ['date_from', 'amount_per_day'];

    protected $casts = ['date_from' => 'date'];

    public static function rateFor(string|\Carbon\Carbon $date): float
    {
        return self::where('date_from', '<=', $date)
            ->orderByDesc('date_from')
            ->value('amount_per_day') ?? 0.0;
    }
}