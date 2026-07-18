<?php

namespace Tests\Feature;

use App\Enums\ComputerStatus;
use App\Livewire\Distributions\CreateBulk;
use App\Models\Computer;
use App\Models\Distribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DistributionsCreateBulkTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_create_bulk(): void
    {
        $this->get('/distributions/create/bulk')->assertRedirect(route('login', absolute: false));
    }

    public function test_add_number_normalizes_and_appends(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create(['number' => 'HA-E-0042', 'status' => ComputerStatus::Refurbished]);

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('computer_number_input', '42')
            ->call('addNumber')
            ->assertHasNoErrors()
            ->assertSet('numbers', ['HA-E-0042'])
            ->assertSet('computer_number_input', '');
    }

    public function test_add_number_rejects_invalid_format(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('computer_number_input', 'foo-bar')
            ->call('addNumber')
            ->assertHasErrors(['computer_number_input'])
            ->assertSet('numbers', []);
    }

    public function test_add_number_rejects_empty_input(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('computer_number_input', '  ')
            ->call('addNumber')
            ->assertHasErrors(['computer_number_input']);
    }

    public function test_add_number_rejects_unknown_computer(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('computer_number_input', '9999')
            ->call('addNumber')
            ->assertHasErrors(['computer_number_input'])
            ->assertSet('numbers', []);
    }

    public function test_add_number_rejects_already_delivered_computer(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create(['status' => ComputerStatus::Delivered]);

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('computer_number_input', $computer->number)
            ->call('addNumber')
            ->assertHasErrors(['computer_number_input'])
            ->assertSet('numbers', []);
    }

    public function test_add_number_rejects_duplicate_within_batch(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create(['status' => ComputerStatus::Refurbished]);

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('computer_number_input', $computer->number)
            ->call('addNumber')
            ->assertHasNoErrors()
            ->set('computer_number_input', $computer->number)
            ->call('addNumber')
            ->assertHasErrors(['computer_number_input'])
            ->assertSet('numbers', [$computer->number]);
    }

    public function test_remove_number_removes_by_index_and_reindexes(): void
    {
        $user = User::factory()->create();
        $c1   = Computer::factory()->create();
        $c2   = Computer::factory()->create();
        $c3   = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('numbers', [$c1->number, $c2->number, $c3->number])
            ->call('removeNumber', 1)
            ->assertSet('numbers', [$c1->number, $c3->number]);
    }

    public function test_save_marks_all_computers_as_delivered(): void
    {
        $user = User::factory()->create();
        $c1   = Computer::factory()->create(['status' => ComputerStatus::Refurbished]);
        $c2   = Computer::factory()->create(['status' => ComputerStatus::Refurbished]);
        $c3   = Computer::factory()->create(['status' => ComputerStatus::Picked]);

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('numbers', [$c1->number, $c2->number, $c3->number])
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('distributions.index', absolute: false));

        $this->assertSame(ComputerStatus::Delivered, $c1->fresh()->status);
        $this->assertSame(ComputerStatus::Delivered, $c2->fresh()->status);
        $this->assertSame(ComputerStatus::Delivered, $c3->fresh()->status);
    }

    public function test_save_creates_distribution_rows_with_null_hash(): void
    {
        $user = User::factory()->create();
        $c1   = Computer::factory()->create();
        $c2   = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('numbers', [$c1->number, $c2->number])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, Distribution::count());
        $this->assertSame(2, Distribution::whereNull('recipient_hash')->count());
        $this->assertSame(1, Distribution::where('computer_id', $c1->id)->count());
        $this->assertSame(1, Distribution::where('computer_id', $c2->id)->count());
    }

    public function test_save_stores_comment_on_all_distributions(): void
    {
        $user = User::factory()->create();
        $c1   = Computer::factory()->create();
        $c2   = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('numbers', [$c1->number, $c2->number])
            ->set('comment', 'Verteilaktion Schule XYZ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, Distribution::where('comment', 'Verteilaktion Schule XYZ')->count());
    }

    public function test_save_stores_null_comment_when_empty(): void
    {
        $user = User::factory()->create();
        $c    = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('numbers', [$c->number])
            ->set('comment', '   ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, Distribution::whereNull('comment')->count());
    }

    public function test_save_records_current_user_and_timestamp(): void
    {
        $user = User::factory()->create();
        $c    = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->set('numbers', [$c->number])
            ->call('save')
            ->assertHasNoErrors();

        $d = Distribution::first();
        $this->assertSame($user->id, $d->user_id);
        $this->assertNotNull($d->distributed_at);
    }

    public function test_save_rejects_empty_list(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateBulk::class)
            ->call('save')
            ->assertHasErrors(['numbers']);
    }
}
