<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case ManualTransfer = 'manual_transfer';
    case Stripe = 'stripe';

    public function label(): string
    {
        return match ($this) {
            self::ManualTransfer => 'Bank Transfer (Manual Verification)',
            self::Stripe => 'Card Payment (Stripe)',
        };
    }
}
