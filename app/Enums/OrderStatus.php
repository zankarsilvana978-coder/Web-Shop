<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Awaiting Payment',
            self::Paid => 'Paid',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingPayment => 'bg-amber-100 text-amber-800',
            self::Paid => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-gray-200 text-gray-700',
            self::Refunded => 'bg-purple-100 text-purple-800',
        };
    }
}
