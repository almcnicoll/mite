<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = ['cause_id', 'date_paid', 'amount'];

    protected $casts = ['date_paid' => 'date'];

    public function cause(): BelongsTo
    {
        return $this->belongsTo(Cause::class);
    }
}