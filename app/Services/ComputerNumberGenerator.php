<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ComputerNumberGenerator
{
    public const SEQUENCE_NAME = 'computer_number';
    public const PREFIX = 'HA-E-';
    public const MIN_DIGITS = 4;

    public function next(): string
    {
        return DB::transaction(function () {
            $row = DB::table('sequences')
                ->where('name', self::SEQUENCE_NAME)
                ->lockForUpdate()
                ->first();

            $next = ($row->value ?? 0) + 1;

            DB::table('sequences')->updateOrInsert(
                ['name' => self::SEQUENCE_NAME],
                ['value' => $next, 'updated_at' => now(), 'created_at' => $row->created_at ?? now()],
            );

            return $this->format($next);
        });
    }

    public function format(int $value): string
    {
        return self::PREFIX . str_pad((string) $value, self::MIN_DIGITS, '0', STR_PAD_LEFT);
    }
}
