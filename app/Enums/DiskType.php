<?php

namespace App\Enums;

enum DiskType: string
{
    case Unknown = 'unknown';
    case HDD = 'hdd';
    case SSD = 'ssd';

    public function label(): string
    {
        return match ($this) {
            self::Unknown => 'Unbekannt',
            self::HDD     => 'HDD',
            self::SSD     => 'SSD',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
