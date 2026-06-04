<?php

namespace App\Livewire\Statistics;

use App\Enums\ComputerStatus;
use App\Enums\DeviceClass;
use App\Models\Computer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Matrix extends Component
{
    #[Layout('layouts.app')]
    #[Title('Statistik')]
    public function render(): mixed
    {
        $rows = Computer::query()
            ->selectRaw('device_class, status, COUNT(*) as cnt')
            ->groupBy('device_class', 'status')
            ->get(['device_class', 'status']);

        $matrix = [];
        foreach (ComputerStatus::cases() as $status) {
            foreach (DeviceClass::cases() as $class) {
                $matrix[$status->value][$class->value] = 0;
            }
        }

        foreach ($rows as $row) {
            $classKey  = $row->device_class instanceof DeviceClass ? $row->device_class->value : (string) $row->device_class;
            $statusKey = $row->status instanceof ComputerStatus ? $row->status->value : (string) $row->status;
            $matrix[$statusKey][$classKey] = (int) $row->cnt;
        }

        $rowTotals = [];
        foreach ($matrix as $statusKey => $classes) {
            $rowTotals[$statusKey] = array_sum($classes);
        }

        $colTotals = [];
        foreach (DeviceClass::cases() as $class) {
            $colTotals[$class->value] = collect($matrix)->sum(fn ($row) => $row[$class->value]);
        }

        $grandTotal = array_sum($rowTotals);

        $orderedClasses = collect(DeviceClass::cases())
            ->reject(fn (DeviceClass $c) => $c === DeviceClass::Unknown)
            ->push(DeviceClass::Unknown)
            ->values()
            ->all();

        return view('livewire.statistics.matrix', [
            'classes'    => $orderedClasses,
            'statuses'   => ComputerStatus::cases(),
            'matrix'     => $matrix,
            'rowTotals'  => $rowTotals,
            'colTotals'  => $colTotals,
            'grandTotal' => $grandTotal,
        ]);
    }
}
