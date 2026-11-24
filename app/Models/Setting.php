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

    public const REMINDER_THRESHOLD_DEFAULT = 5;

    /** Outstanding picks at which a person gets a reminder email; 0 = reminders off. */
    public static function reminderThreshold(): int
    {
        $value = self::find('reminder_threshold')?->value;

        return $value === null ? self::REMINDER_THRESHOLD_DEFAULT : (int) $value;
    }

    public static function setReminderThreshold(int $count): void
    {
        self::updateOrCreate(['key' => 'reminder_threshold'], ['value' => (string) $count]);
    }

    /** Master switch for reminder emails. Off until someone turns it on (no row = off). */
    public static function reminderEmailsEnabled(): bool
    {
        return self::find('reminder_emails_enabled')?->value === '1';
    }

    public static function setReminderEmailsEnabled(bool $enabled): void
    {
        self::updateOrCreate(['key' => 'reminder_emails_enabled'], ['value' => $enabled ? '1' : '0']);
    }
}
