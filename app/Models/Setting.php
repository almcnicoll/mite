<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public const DONATION_THRESHOLD_DEFAULT = 50.0;

    public static function donationThreshold(): float
    {
        $value = self::find('donation_threshold')?->value;

        return $value === null ? self::DONATION_THRESHOLD_DEFAULT : (float) $value;
    }

    public static function setDonationThreshold(float $amount): void
    {
        self::updateOrCreate(['key' => 'donation_threshold'], ['value' => (string) $amount]);
    }
}
