<?php

namespace Tests\Feature;

use App\Livewire\Dashboard;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_latest_computers_and_total(): void
    {
        $user = User::factory()->create();

        Computer::factory()->count(15)->create();

        $component = Livewire::actingAs($user)->test(Dashboard::class);

        $component->assertOk();
        $this->assertSame(15, $component->viewData('totalCount'));
        $this->assertCount(10, $component->viewData('latest'));
    }

    public function test_dashboard_chart_has_12_weeks_buckets(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(Dashboard::class);

        $this->assertCount(12, $component->viewData('chartLabels'));
        $this->assertCount(12, $component->viewData('chartData'));
        $this->assertSame([0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], $component->viewData('chartData'));
    }
}
