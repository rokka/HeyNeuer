<?php

namespace Tests\Feature;

use App\Enums\ComputerStatus;
use App\Enums\DeviceClass;
use App\Enums\DiskType;
use App\Livewire\Statistics\Matrix;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_matrix_sums_equal_total(): void
    {
        $user = User::factory()->create();

        Computer::factory()->count(3)->create([
            'device_class' => DeviceClass::Laptop->value,
            'status'       => ComputerStatus::New->value,
            'disk_type'    => DiskType::SSD->value,
        ]);
        Computer::factory()->count(2)->create([
            'device_class' => DeviceClass::Desktop->value,
            'status'       => ComputerStatus::Delivered->value,
            'disk_type'    => DiskType::HDD->value,
        ]);

        $component = Livewire::actingAs($user)->test(Matrix::class);

        $matrix = $component->viewData('matrix');
        $rowTotals = $component->viewData('rowTotals');
        $colTotals = $component->viewData('colTotals');
        $grandTotal = $component->viewData('grandTotal');

        $this->assertSame(3, $matrix[ComputerStatus::New->value][DeviceClass::Laptop->value]);
        $this->assertSame(2, $matrix[ComputerStatus::Delivered->value][DeviceClass::Desktop->value]);
        $this->assertSame(3, $rowTotals[ComputerStatus::New->value]);
        $this->assertSame(2, $rowTotals[ComputerStatus::Delivered->value]);
        $this->assertSame(3, $colTotals[DeviceClass::Laptop->value]);
        $this->assertSame(2, $colTotals[DeviceClass::Desktop->value]);
        $this->assertSame(5, $grandTotal);
        $this->assertSame(Computer::count(), $grandTotal);
    }
}
