<?php

namespace App\Livewire;

use App\Models\Computer;
use Carbon\CarbonImmutable;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    private const CHART_WEEKS = 12;

    #[Layout('layouts.app')]
    #[Title('Startseite')]
    public function render(): mixed
    {
        $latest = Computer::orderByDesc('created_at')->limit(10)->get();

        $start = CarbonImmutable::now('Europe/Berlin')
            ->startOfWeek()
            ->subWeeks(self::CHART_WEEKS - 1);

        $weeklyCounts = Computer::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn ($c) => $c->created_at->copy()->startOfWeek()->format('Y-m-d'))
            ->map->count();

        $labels = [];
        $data = [];
        for ($i = 0; $i < self::CHART_WEEKS; $i++) {
            $weekStart = $start->addWeeks($i);
            $key = $weekStart->format('Y-m-d');
            $labels[] = 'KW ' . $weekStart->isoWeek();
            $data[] = (int) ($weeklyCounts[$key] ?? 0);
        }

        return view('livewire.dashboard', [
            'latest'      => $latest,
            'chartLabels' => $labels,
            'chartData'   => $data,
            'totalCount'  => Computer::count(),
        ]);
    }
}
