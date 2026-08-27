<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case AwaitingShipment = 'awaiting_shipment';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingShipment => 'Awaiting Shipment',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AwaitingShipment => 'bg-amber-100 text-amber-800',
            self::Shipped => 'bg-blue-100 text-blue-800',
            self::Delivered => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-gray-200 text-gray-700',
            self::Refunded => 'bg-purple-100 text-purple-800',
        };
    }
}
