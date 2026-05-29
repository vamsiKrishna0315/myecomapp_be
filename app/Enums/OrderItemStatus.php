<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Packed = 'packed';
    case Dispatched = 'dispatched';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';

    /**
     * Get the integer value for the enum case
     */
    public function toInt(): int
    {
        return match($this) {
            self::Pending => 0,
            self::Confirmed => 1,
            self::Preparing => 2,
            self::Ready => 3,
            self::Packed => 4,
            self::Dispatched => 5,
            self::Delivered => 6,
            self::Cancelled => 7,
            self::Returned => 8,
        };
    }

    /**
     * Get enum case from integer value
     */
    public static function fromInt(int $value): self
    {
        return match($value) {
            0 => self::Pending,
            1 => self::Confirmed,
            2 => self::Preparing,
            3 => self::Ready,
            4 => self::Packed,
            5 => self::Dispatched,
            6 => self::Delivered,
            7 => self::Cancelled,
            8 => self::Returned,
            default => throw new \InvalidArgumentException("Invalid order item status value: {$value}"),
        };
    }

    /**
     * Get all integer values mapped to their enum cases
     */
    public static function getIntegerMapping(): array
    {
        return [
            0 => self::Pending,
            1 => self::Confirmed,
            2 => self::Preparing,
            3 => self::Ready,
            4 => self::Packed,
            5 => self::Dispatched,
            6 => self::Delivered,
            7 => self::Cancelled,
            8 => self::Returned,
        ];
    }

    /**
     * Get human readable label
     */
    public function getLabel(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Preparing => 'Preparing',
            self::Ready => 'Ready',
            self::Packed => 'Packed',
            self::Dispatched => 'Dispatched',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Returned => 'Returned',
        };
    }
}
