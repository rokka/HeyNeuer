<?php

namespace Tests\Feature;

use App\Enums\ComputerStatus;
use App\Livewire\Distributions\Create;
use App\Models\Computer;
use App\Models\Distribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DistributionsCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_access_create(): void
    {
        $this->get('/distributions/create')->assertRedirect(route('login', absolute: false));
    }

    public function test_recipient_hash_matches_specification(): void
    {
        // Vorgabe:
        //   $key  = strtolower($firstname . "_" . $lastname . "_" . $birthday);
        //   $hash = hash('sha256', $key);
        $expected = hash('sha256', strtolower('anna_müller_2010-04-12'));
        $actual = Distribution::buildRecipientHash('Anna', 'Müller', '2010-04-12');
        $this->assertSame($expected, $actual);
        $this->assertSame(64, strlen($actual));
    }

    public function test_recipient_hash_is_case_insensitive(): void
    {
        $h1 = Distribution::buildRecipientHash('Anna', 'Müller', '2010-04-12');
        $h2 = Distribution::buildRecipientHash('ANNA', 'müller', '2010-04-12');
        $h3 = Distribution::buildRecipientHash('  Anna  ', '  Müller  ', '2010-04-12');
        $this->assertSame($h1, $h2);
        $this->assertSame($h1, $h3);
    }

    public function test_normalize_computer_number_accepts_full_form(): void
    {
        $this->assertSame('HA-E-1234', Distribution::normalizeComputerNumber('HA-E-1234'));
        $this->assertSame('HA-E-1234', Distribution::normalizeComputerNumber('ha-e-1234'));
        $this->assertSame('HA-E-0042', Distribution::normalizeComputerNumber('HA-E-42'));
    }

    public function test_normalize_computer_number_accepts_digits_only(): void
    {
        $this->assertSame('HA-E-1234', Distribution::normalizeComputerNumber('1234'));
        $this->assertSame('HA-E-0001', Distribution::normalizeComputerNumber('1'));
        $this->assertSame('HA-E-99999', Distribution::normalizeComputerNumber('99999'));
    }

    public function test_normalize_computer_number_rejects_invalid(): void
    {
        $this->assertNull(Distribution::normalizeComputerNumber('foo-bar'));
        $this->assertNull(Distribution::normalizeComputerNumber('HA-X-1234'));
        $this->assertNull(Distribution::normalizeComputerNumber(''));
        $this->assertNull(Distribution::normalizeComputerNumber(null));
    }

    public function test_user_can_create_distribution_with_full_computer_number(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer->number)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('distributions.index', absolute: false));

        $expectedHash = Distribution::buildRecipientHash('Anna', 'Müller', '2010-04-12');
        $distribution = Distribution::first();
        $this->assertNotNull($distribution);
        $this->assertSame($computer->id, $distribution->computer_id);
        $this->assertSame($user->id, $distribution->user_id);
        $this->assertSame($expectedHash, $distribution->recipient_hash);
    }

    public function test_computer_cannot_be_distributed_twice(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create();

        // Erste Ausgabe — OK
        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer->number)
            ->call('save')
            ->assertHasNoErrors();

        // Zweite Ausgabe DESSELBEN Computers an andere Person — abgewiesen
        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Bob')
            ->set('last_name', 'Beispiel')
            ->set('birthdate', '2009-11-30')
            ->set('computer_number_input', $computer->number)
            ->call('save')
            ->assertHasErrors(['computer_number_input']);

        $this->assertSame(1, Distribution::count());
    }

    public function test_successful_distribution_marks_computer_as_delivered(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create(['status' => ComputerStatus::Refurbished]);

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer->number)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(ComputerStatus::Delivered, $computer->fresh()->status);
    }

    public function test_user_can_create_distribution_with_digits_only(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create(['number' => 'HA-E-0042']);

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Bob')
            ->set('last_name', 'Beispiel')
            ->set('birthdate', '2009-11-30')
            ->set('computer_number_input', '42')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('distributions', [
            'computer_id' => $computer->id,
        ]);
    }

    public function test_personal_data_is_not_stored(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'PERSONALEINMALIG')
            ->set('last_name', 'NAMEEINMALIG')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer->number)
            ->call('save')
            ->assertHasNoErrors();

        $columns = collect(\Illuminate\Support\Facades\Schema::getColumnListing('distributions'));
        $this->assertFalse($columns->contains('first_name'));
        $this->assertFalse($columns->contains('last_name'));
        $this->assertFalse($columns->contains('birthdate'));

        $row = \Illuminate\Support\Facades\DB::table('distributions')->first();
        $json = json_encode($row);
        $this->assertStringNotContainsString('PERSONALEINMALIG', $json);
        $this->assertStringNotContainsString('NAMEEINMALIG', $json);
    }

    public function test_duplicate_recipient_is_rejected(): void
    {
        $user      = User::factory()->create();
        $computer1 = Computer::factory()->create();
        $computer2 = Computer::factory()->create();

        // Erste Ausgabe — OK
        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer1->number)
            ->call('save')
            ->assertHasNoErrors();

        // Zweite Ausgabe für DIESELBE Person — abgewiesen
        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer2->number)
            ->call('save')
            ->assertHasErrors(['recipient']);

        $this->assertSame(1, Distribution::count());
    }

    public function test_duplicate_check_is_case_insensitive(): void
    {
        $user      = User::factory()->create();
        $computer1 = Computer::factory()->create();
        $computer2 = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer1->number)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'ANNA')
            ->set('last_name', 'müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', $computer2->number)
            ->call('save')
            ->assertHasErrors(['recipient']);
    }

    public function test_unknown_computer_number_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', '9999')
            ->call('save')
            ->assertHasErrors(['computer_number_input']);

        $this->assertSame(0, Distribution::count());
    }

    public function test_invalid_computer_number_format_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', '2010-04-12')
            ->set('computer_number_input', 'foo-bar')
            ->call('save')
            ->assertHasErrors(['computer_number_input']);
    }

    public function test_birthdate_in_the_future_is_rejected(): void
    {
        $user     = User::factory()->create();
        $computer = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('first_name', 'Anna')
            ->set('last_name', 'Müller')
            ->set('birthdate', now()->addYear()->format('Y-m-d'))
            ->set('computer_number_input', $computer->number)
            ->call('save')
            ->assertHasErrors(['birthdate']);
    }

    public function test_required_fields_are_enforced(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Create::class)
            ->call('save')
            ->assertHasErrors(['first_name', 'last_name', 'birthdate', 'computer_number_input']);
    }

    public function test_query_params_prefill_the_form(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams([
                'first_name' => 'Anna',
                'last_name'  => 'Müller',
                'birthdate'  => '2010-04-12',
            ])
            ->test(Create::class)
            ->assertSet('first_name', 'Anna')
            ->assertSet('last_name', 'Müller')
            ->assertSet('birthdate', '2010-04-12');
    }
}
