<?php

namespace App\Enums;

enum ComputerStatus: string
{
    case New = 'new';
    case Processing = 'processing';
    case Refurbished = 'refurbished';
    case Delivered = 'delivered';
    case Picked = 'picked';
    case Loss = 'loss';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::New         => 'Neu',
            self::Processing  => 'Wird bearbeitet',
            self::Refurbished => 'Aufbereitet',
            self::Delivered   => 'Ausgeliefert',
            self::Picked      => 'Kommissioniert',
            self::Loss        => 'Schwund',
            self::Disposed    => 'Entsorgt',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::New         => 'bg-blue-100 text-blue-800',
            self::Processing  => 'bg-yellow-100 text-yellow-800',
            self::Refurbished => 'bg-green-100 text-green-800',
            self::Delivered   => 'bg-emerald-100 text-emerald-800',
            self::Picked      => 'bg-purple-100 text-purple-800',
            self::Loss        => 'bg-red-100 text-red-800',
            self::Disposed    => 'bg-gray-200 text-gray-800',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
