<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::Active => 'Active',
            self::Rejected => 'Rejected',
            self::Archived => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-200 text-gray-700',
            self::PendingReview => 'bg-amber-100 text-amber-800',
            self::Active => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-red-100 text-red-800',
            self::Archived => 'bg-gray-200 text-gray-500',
        };
    }
}
