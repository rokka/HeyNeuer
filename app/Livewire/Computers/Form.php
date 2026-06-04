<?php

namespace App\Livewire\Computers;

use App\Enums\ComputerStatus;
use App\Enums\DeviceClass;
use App\Enums\DiskType;
use App\Models\Computer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Form extends Component
{
    public ?Computer $computer = null;

    public string $device_class = DeviceClass::Unknown->value;
    public string $model = '';
    public bool $has_webcam = false;
    public bool $has_wifi = false;
    public string $status = ComputerStatus::New->value;
    public ?string $comment = null;
    public ?string $cpu_model = null;
    public ?int $ram_gb = null;
    public string $disk_type = DiskType::Unknown->value;
    public ?int $disk_gb = null;

    public function mount(?Computer $computer = null): void
    {
        if ($computer && $computer->exists) {
            $this->authorize('update', $computer);
            $this->computer = $computer;
            $this->fill([
                'device_class' => $computer->device_class->value,
                'model'        => $computer->model,
                'has_webcam'   => $computer->has_webcam,
                'has_wifi'     => $computer->has_wifi,
                'status'       => $computer->status->value,
                'comment'      => $computer->comment,
                'cpu_model'    => $computer->cpu_model,
                'ram_gb'       => $computer->ram_gb,
                'disk_type'    => $computer->disk_type->value,
                'disk_gb'      => $computer->disk_gb,
            ]);
            return;
        }

        $this->authorize('create', Computer::class);
        $this->applyQueryDefaults(request());
    }

    /**
     * Erlaubt Pre-Fill via Query-Parameter beim Neuanlegen, z.B. von externen
     * Tools, die die Hardware schon ausgelesen haben:
     *
     *   /computers/create?model=...&cpu=...&type_name=desktop&memory_in_gb=8
     *                    &hard_drive_type=2&hard_drive_space_in_gb=512
     *
     * - type_name: "desktop" | "laptop"
     * - hard_drive_type: "1" = HDD, "2" = SSD
     * - Sobald irgendein bekannter Parameter gesetzt ist → Status = "Aufbereitet"
     */
    protected function applyQueryDefaults(\Illuminate\Http\Request $request): void
    {
        $knownKeys = ['model', 'cpu', 'type_name', 'memory_in_gb', 'hard_drive_type', 'hard_drive_space_in_gb'];
        $hasAny = collect($knownKeys)->contains(fn ($k) => $request->filled($k));

        if (! $hasAny) {
            return;
        }

        if ($request->filled('model')) {
            $this->model = (string) $request->query('model', '');
        }

        if ($request->filled('cpu')) {
            $this->cpu_model = (string) $request->query('cpu');
        }

        $deviceClassMap = [
            'desktop' => DeviceClass::Desktop,
            'laptop'  => DeviceClass::Laptop,
        ];
        $typeName = strtolower((string) $request->query('type_name', ''));
        if (isset($deviceClassMap[$typeName])) {
            $this->device_class = $deviceClassMap[$typeName]->value;
        }

        if ($request->filled('memory_in_gb')) {
            $mem = (int) $request->query('memory_in_gb');
            $this->ram_gb = $mem >= 0 ? $mem : null;
        }

        $diskTypeMap = [
            '1' => DiskType::HDD,
            '2' => DiskType::SSD,
        ];
        $diskType = (string) $request->query('hard_drive_type', '');
        if (isset($diskTypeMap[$diskType])) {
            $this->disk_type = $diskTypeMap[$diskType]->value;
        }

        if ($request->filled('hard_drive_space_in_gb')) {
            $disk = (int) $request->query('hard_drive_space_in_gb');
            $this->disk_gb = $disk >= 0 ? $disk : null;
        }

        // Wer per externem Link kommt, hat das Gerät bereits aufbereitet
        $this->status = ComputerStatus::Refurbished->value;
    }

    public function rules(): array
    {
        return [
            'device_class' => ['required', 'string', 'in:' . implode(',', array_column(DeviceClass::cases(), 'value'))],
            'model'        => ['required', 'string', 'max:255'],
            'has_webcam'   => ['boolean'],
            'has_wifi'     => ['boolean'],
            'status'       => ['required', 'string', 'in:' . implode(',', array_column(ComputerStatus::cases(), 'value'))],
            'comment'      => ['nullable', 'string', 'max:5000'],
            'cpu_model'    => ['nullable', 'string', 'max:255'],
            'ram_gb'       => ['nullable', 'integer', 'min:0', 'max:99999'],
            'disk_type'    => ['required', 'string', 'in:' . implode(',', array_column(DiskType::cases(), 'value'))],
            'disk_gb'      => ['nullable', 'integer', 'min:0', 'max:9999999'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->computer) {
            $this->computer->update($validated);
            session()->flash('status', 'Computer ' . $this->computer->number . ' wurde aktualisiert.');
        } else {
            $created = Computer::create($validated);
            session()->flash('status', 'Computer ' . $created->number . ' wurde angelegt.');
        }

        $this->redirectRoute('computers.index', navigate: true);
    }

    public function delete(): void
    {
        if (! $this->computer) {
            return;
        }

        $this->authorize('delete', $this->computer);

        $number = $this->computer->number;
        $this->computer->delete();

        session()->flash('status', 'Computer ' . $number . ' wurde gelöscht.');
        $this->redirectRoute('computers.index', navigate: true);
    }

    #[Layout('layouts.app')]
    #[Title('Computer')]
    public function render(): mixed
    {
        return view('livewire.computers.form', [
            'deviceClasses' => DeviceClass::options(),
            'statuses'      => ComputerStatus::options(),
            'diskTypes'     => DiskType::options(),
            'activities'    => $this->computer
                ? $this->computer->activitiesAsSubject()->latest()->limit(20)->get()
                : collect(),
        ]);
    }
}
