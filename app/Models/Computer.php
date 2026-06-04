<?php

namespace App\Models;

use App\Enums\ComputerStatus;
use App\Enums\DeviceClass;
use App\Enums\DiskType;
use App\Services\ComputerNumberGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Computer extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'number',
        'device_class',
        'model',
        'has_webcam',
        'has_wifi',
        'status',
        'comment',
        'cpu_model',
        'ram_gb',
        'disk_type',
        'disk_gb',
    ];

    protected function casts(): array
    {
        return [
            'device_class' => DeviceClass::class,
            'status'       => ComputerStatus::class,
            'disk_type'    => DiskType::class,
            'has_webcam'   => 'boolean',
            'has_wifi'     => 'boolean',
            'ram_gb'       => 'integer',
            'disk_gb'      => 'integer',
        ];
    }

    /** @return HasMany<Distribution, $this> */
    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Computer $computer) {
            if (empty($computer->number)) {
                $computer->number = app(ComputerNumberGenerator::class)->next();
            }
        });
    }

    /**
     * Workaround für Spatie\Activitylog 5.0 + PHP 8.5: der Trait macht
     * `$model->oldAttributes = $changes` aus einem Closure heraus. Dabei
     * greift Eloquents `__set` und schreibt den Wert fälschlich als DB-Spalte
     * statt in die typed Trait-Property. Wir leiten den Schreibzugriff hier
     * direkt auf die deklarierte Property um.
     */
    public function __set($key, $value): void
    {
        if ($key === 'oldAttributes' && is_array($value)) {
            (new \ReflectionProperty(self::class, 'oldAttributes'))->setValue($this, $value);
            return;
        }

        parent::__set($key, $value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('computer')
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Computer angelegt',
                'updated' => 'Computer bearbeitet',
                'deleted' => 'Computer gelöscht',
                default   => $eventName,
            });
    }

    public const FIELD_LABELS = [
        'number'       => 'Nummer',
        'device_class' => 'Geräteklasse',
        'model'        => 'Modell',
        'has_webcam'   => 'Web-Cam integriert',
        'has_wifi'     => 'WLAN integriert',
        'status'       => 'Status',
        'comment'      => 'Kommentar',
        'cpu_model'    => 'CPU-Modell',
        'ram_gb'       => 'Arbeitsspeicher (GB)',
        'disk_type'    => 'Festplattentyp',
        'disk_gb'      => 'Festplattengröße (GB)',
    ];

    public static function fieldLabel(string $key): string
    {
        return self::FIELD_LABELS[$key] ?? $key;
    }

    public static function activityEventLabel(?string $event, ?string $fallback = null): string
    {
        return match ($event) {
            'created' => 'Computer angelegt',
            'updated' => 'Computer bearbeitet',
            'deleted' => 'Computer gelöscht',
            default   => $fallback ?? ($event ?? 'Aktivität'),
        };
    }

    /**
     * Liefert die Beschriftung für einen Activity-Log-Eintrag. Wenn beim
     * Bearbeiten der Status geändert wurde, erscheint statt "Computer bearbeitet"
     * eine spezifische Status-Übergangs-Meldung.
     */
    public static function activityDescription(\Spatie\Activitylog\Models\Activity $activity): string
    {
        if ($activity->event === 'updated') {
            $changes = $activity->attribute_changes;
            $new = $changes['attributes']['status'] ?? null;
            $old = $changes['old']['status']       ?? null;

            if ($new !== null && $old !== null && $new !== $old) {
                $oldLabel = ComputerStatus::tryFrom((string) $old)?->label() ?? (string) $old;
                $newLabel = ComputerStatus::tryFrom((string) $new)?->label() ?? (string) $new;
                return "Status von „{$oldLabel}“ auf „{$newLabel}“ geändert";
            }
        }

        return self::activityEventLabel($activity->event, $activity->description);
    }

    public static function formatActivityValue(string $key, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($key) {
            'device_class' => DeviceClass::tryFrom((string) $value)?->label() ?? (string) $value,
            'status'       => ComputerStatus::tryFrom((string) $value)?->label() ?? (string) $value,
            'disk_type'    => DiskType::tryFrom((string) $value)?->label() ?? (string) $value,
            'has_webcam',
            'has_wifi'     => $value ? 'ja' : 'nein',
            default        => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
        };
    }
}
