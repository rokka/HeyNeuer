<?php

namespace App\Livewire\Distributions;

use App\Models\Distribution;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    #[Title('Ausgabe')]
    public function render(): mixed
    {
        $distributions = Distribution::query()
            ->with(['computer', 'user'])
            ->orderByDesc('distributed_at')
            ->paginate(25);

        return view('livewire.distributions.index', [
            'distributions' => $distributions,
        ]);
    }
}
