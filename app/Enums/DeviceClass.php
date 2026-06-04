<?php

namespace App\Enums;

enum DeviceClass: string
{
    case Unknown = 'unknown';
    case Laptop = 'laptop';
    case Desktop = 'desktop';
    case Tablet = 'tablet';

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Unbekannt',
            self::Laptop  => 'Laptop',
            self::Desktop => 'Desktop',
            self::Tablet  => 'Tablett',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Unknown => 'fas fa-question',
            self::Laptop  => 'fas fa-laptop',
            self::Desktop => 'fas fa-desktop',
            self::Tablet  => 'fas fa-tablet-alt',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
