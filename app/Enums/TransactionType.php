<?php

namespace App\Enums;

enum TransactionType: string
{
    case Earning = 'earning';
    case Commission = 'commission';
    case Payout = 'payout';
    case Refund = 'refund';

    /** Signed convention: credits positive, debits negative. */
    public function sign(): int
    {
        return match ($this) {
            self::Earning, self::Commission => 1,
            self::Payout, self::Refund => -1,
        };
    }
}
