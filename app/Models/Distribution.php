<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'computer_id',
        'user_id',
        'distributed_at',
        'recipient_hash',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'distributed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Computer, $this> */
    public function computer(): BelongsTo
    {
        return $this->belongsTo(Computer::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bildet den Empfänger-Hash aus Vor-/Nachname und Geburtsdatum.
     * Vorgaben:
     *   $key  = strtolower($firstname . "_" . $lastname . "_" . $birthday);
     *   $hash = hash('sha256', $key);
     *
     * Die Klartextdaten werden NICHT gespeichert — nur der Hash. Damit kann
     * der Datensatz nicht trivial zurück auf eine Person aufgelöst werden,
     * Duplikate sind aber erkennbar.
     */
    public static function buildRecipientHash(string $firstName, string $lastName, string $birthday): string
    {
        $key = strtolower(trim($firstName) . '_' . trim($lastName) . '_' . trim($birthday));
        return hash('sha256', $key);
    }

    /**
     * Akzeptiert komplette Nummer "HA-E-1234" ODER nur Ziffern "1234" und
     * normalisiert beides auf das offizielle Format "HA-E-####".
     * Liefert null, wenn nicht parsbar.
     */
    public static function normalizeComputerNumber(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $input = trim($input);
        if ($input === '') {
            return null;
        }

        // Komplette Form HA-E-#### (mind. 4 Ziffern)
        if (preg_match('/^HA-E-(\d+)$/i', $input, $m)) {
            return 'HA-E-' . str_pad($m[1], 4, '0', STR_PAD_LEFT);
        }

        // Nur Ziffern
        if (preg_match('/^\d+$/', $input)) {
            return 'HA-E-' . str_pad($input, 4, '0', STR_PAD_LEFT);
        }

        return null;
    }
}
