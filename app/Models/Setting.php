<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Single-row settings table. Access via static helpers; the row is
 * cached so hot paths (commission resolution, checkout) stay fast.
 */
class Setting extends Model
{
    public const DEFAULTS = [
        'site_name' => 'Soukelkom',
        'support_email' => 'support@soukelkom.test',
        'global_commission_rate' => 10.00,
        'shipping_flat_rate' => 5.00,
        'payout_min' => 50.00,
        'ship_deadline_hours' => 48,
        'earning_hold_days' => 14,
    ];

    protected $fillable = [
        'site_name',
        'support_email',
        'global_commission_rate',
        'shipping_flat_rate',
        'payout_min',
        'ship_deadline_hours',
        'earning_hold_days',
    ];

    protected function casts(): array
    {
        return [
            'global_commission_rate' => 'decimal:2',
            'shipping_flat_rate' => 'decimal:2',
            'payout_min' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return Cache::rememberForever('soukelkom.settings', fn () => self::query()->firstOrCreate(['id' => 1], self::DEFAULTS));
    }

    public static function get(string $key): mixed
    {
        return self::current()->getAttribute($key) ?? self::DEFAULTS[$key];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('soukelkom.settings'));
        static::deleted(fn () => Cache::forget('soukelkom.settings'));
    }
}
