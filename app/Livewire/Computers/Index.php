<?php

namespace App\Livewire\Computers;

use App\Enums\ComputerStatus;
use App\Enums\DeviceClass;
use App\Models\Computer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $classFilter = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    #[Title('Computer')]
    public function render(): mixed
    {
        $query = Computer::query()
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('number', 'like', $term)
                      ->orWhere('model', 'like', $term)
                      ->orWhere('cpu_model', 'like', $term)
                      ->orWhere('comment', 'like', $term);
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->classFilter !== '', fn ($q) => $q->where('device_class', $this->classFilter))
            ->orderByDesc('created_at');

        return view('livewire.computers.index', [
            'computers'    => $query->paginate(25),
            'statuses'     => ComputerStatus::options(),
            'deviceClasses' => DeviceClass::options(),
        ]);
    }
}
