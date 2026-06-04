<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Distribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected(): void
    {
        $this->get('/distributions')->assertRedirect(route('login', absolute: false));
    }

    public function test_authenticated_user_sees_distribution_list(): void
    {
        $user = User::factory()->create(['name' => 'Erika Beispiel']);
        $computer = Computer::factory()->create(['model' => 'Dell Precision 7560', 'comment' => 'Akku schwach']);

        Distribution::factory()->create([
            'computer_id'    => $computer->id,
            'user_id'        => $user->id,
            'distributed_at' => '2026-05-15 10:00:00',
            'recipient_hash'       => 'aabbccddeeff00112233445566778899',
        ]);

        $this->actingAs($user)
            ->get('/distributions')
            ->assertOk()
            ->assertSee('Dell Precision 7560')
            ->assertSee('Akku schwach')
            ->assertSee('15.05.2026')
            ->assertSee('Erika Beispiel')
            ->assertSee('aabbccddeeff00112233445566778899');
    }

    public function test_empty_list_shows_placeholder(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/distributions')
            ->assertOk()
            ->assertSee('Keine Ausgaben erfasst.');
    }

    public function test_distribution_lists_orders_by_distributed_at_desc(): void
    {
        $user = User::factory()->create();
        $older = Distribution::factory()->create(['distributed_at' => '2026-01-01']);
        $newer = Distribution::factory()->create(['distributed_at' => '2026-05-01']);

        $response = $this->actingAs($user)->get('/distributions');
        $body = $response->getContent();

        $posNewer = strpos($body, $newer->recipient_hash);
        $posOlder = strpos($body, $older->recipient_hash);

        $this->assertNotFalse($posNewer);
        $this->assertNotFalse($posOlder);
        $this->assertLessThan($posOlder, $posNewer, 'Neuere Ausgabe sollte oberhalb der älteren stehen.');
    }

    public function test_deleted_computer_shows_placeholder(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create();
        $distribution = Distribution::factory()->create([
            'computer_id' => $computer->id,
            'user_id'     => $user->id,
        ]);

        // Computer löschen → cascade entfernt auch die Distribution
        $computer->delete();
        $this->assertNull(Distribution::find($distribution->id));
    }

    public function test_deleted_user_keeps_distribution_with_null(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $distribution = Distribution::factory()->create(['user_id' => $other->id]);

        $other->delete();
        $distribution->refresh();
        $this->assertNull($distribution->user_id);

        $this->actingAs($user)
            ->get('/distributions')
            ->assertOk()
            ->assertSee('(gelöscht)');
    }
}
