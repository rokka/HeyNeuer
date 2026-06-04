<?php

namespace Tests\Feature;

use App\Enums\ComputerStatus;
use App\Enums\DeviceClass;
use App\Enums\DiskType;
use App\Livewire\Computers\Form;
use App\Livewire\Computers\Index;
use App\Models\Computer;
use App\Models\Sequence;
use App\Models\User;
use App\Services\ComputerNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ComputerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_see_computers_index(): void
    {
        $this->get(route('computers.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_index_shows_existing_computers(): void
    {
        $user = User::factory()->create();
        Computer::factory()->create(['model' => 'Brand-New Laptop']);
        Computer::factory()->create(['model' => 'Older Desktop']);

        $this->actingAs($user)
            ->get(route('computers.index'))
            ->assertOk()
            ->assertSee('Brand-New Laptop')
            ->assertSee('Older Desktop')
            ->assertDontSee('Keine Computer gefunden');
    }

    public function test_authenticated_user_can_create_computer_with_auto_number(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Form::class)
            ->set('device_class', DeviceClass::Laptop->value)
            ->set('model', 'Dell Latitude 5520')
            ->set('status', ComputerStatus::New->value)
            ->set('disk_type', DiskType::SSD->value)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('computers.index', absolute: false));

        $computer = Computer::first();

        $this->assertNotNull($computer);
        $this->assertSame('HA-E-0001', $computer->number);
        $this->assertSame(DeviceClass::Laptop, $computer->device_class);
        $this->assertSame(ComputerStatus::New, $computer->status);
    }

    public function test_number_generator_produces_sequential_numbers(): void
    {
        $generator = app(ComputerNumberGenerator::class);

        $this->assertSame('HA-E-0001', $generator->next());
        $this->assertSame('HA-E-0002', $generator->next());
        $this->assertSame('HA-E-0003', $generator->next());

        $this->assertSame(3, Sequence::find(ComputerNumberGenerator::SEQUENCE_NAME)->value);
    }

    public function test_number_generator_pads_to_4_digits_but_grows(): void
    {
        $generator = app(ComputerNumberGenerator::class);
        Sequence::create(['name' => ComputerNumberGenerator::SEQUENCE_NAME, 'value' => 9999]);

        $this->assertSame('HA-E-10000', $generator->next());
    }

    public function test_existing_computer_can_be_updated(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create([
            'model' => 'Old Model',
        ]);

        Livewire::actingAs($user)
            ->test(Form::class, ['computer' => $computer])
            ->set('model', 'New Model')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('New Model', $computer->refresh()->model);
    }

    public function test_non_admin_cannot_delete_computer_from_form(): void
    {
        $user = User::factory()->create();
        $computer = Computer::factory()->create();

        Livewire::actingAs($user)
            ->test(Form::class, ['computer' => $computer])
            ->call('delete')
            ->assertForbidden();

        $this->assertNotNull($computer->fresh());
    }

    public function test_admin_can_delete_computer_from_form(): void
    {
        $admin = User::factory()->admin()->create();
        $computer = Computer::factory()->create();

        Livewire::actingAs($admin)
            ->test(Form::class, ['computer' => $computer])
            ->call('delete')
            ->assertHasNoErrors()
            ->assertRedirect(route('computers.index', absolute: false));

        $this->assertNull($computer->fresh());
    }

    public function test_create_form_can_be_prefilled_via_query_parameters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/computers/create?'
            . 'model=Dell+Precision+7560'
            . '&cpu=Intel+Core+i7-11800H'
            . '&type_name=laptop'
            . '&memory_in_gb=32'
            . '&hard_drive_type=2'
            . '&hard_drive_space_in_gb=1024');

        $response->assertOk()
            ->assertSee('Dell Precision 7560')
            ->assertSee('Intel Core i7-11800H')
            ->assertSee('32')
            ->assertSee('1024');

        // Verifiziere die einzelnen pre-filled Felder via Livewire-Komponente
        Livewire::actingAs($user)
            ->withQueryParams([
                'model'                  => 'Dell Precision 7560',
                'cpu'                    => 'Intel Core i7-11800H',
                'type_name'              => 'laptop',
                'memory_in_gb'           => '32',
                'hard_drive_type'        => '2',
                'hard_drive_space_in_gb' => '1024',
            ])
            ->test(Form::class)
            ->assertSet('model', 'Dell Precision 7560')
            ->assertSet('cpu_model', 'Intel Core i7-11800H')
            ->assertSet('device_class', DeviceClass::Laptop->value)
            ->assertSet('ram_gb', 32)
            ->assertSet('disk_type', DiskType::SSD->value)
            ->assertSet('disk_gb', 1024)
            ->assertSet('status', ComputerStatus::Refurbished->value);
    }

    public function test_query_param_disk_type_one_maps_to_hdd(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['hard_drive_type' => '1'])
            ->test(Form::class)
            ->assertSet('disk_type', DiskType::HDD->value)
            ->assertSet('status', ComputerStatus::Refurbished->value);
    }

    public function test_query_param_type_name_desktop_maps_to_desktop(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams(['type_name' => 'desktop'])
            ->test(Form::class)
            ->assertSet('device_class', DeviceClass::Desktop->value)
            ->assertSet('status', ComputerStatus::Refurbished->value);
    }

    public function test_invalid_query_values_are_ignored(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->withQueryParams([
                'type_name'       => 'mainframe',  // ungültig
                'hard_drive_type' => '99',          // ungültig
            ])
            ->test(Form::class)
            ->assertSet('device_class', DeviceClass::Unknown->value)
            ->assertSet('disk_type', DiskType::Unknown->value)
            // status bleibt aber auf Refurbished, weil bekannte Keys gesetzt waren
            ->assertSet('status', ComputerStatus::Refurbished->value);
    }

    public function test_create_form_without_query_params_keeps_defaults(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Form::class)
            ->assertSet('status', ComputerStatus::New->value)
            ->assertSet('device_class', DeviceClass::Unknown->value)
            ->assertSet('model', '');
    }

    public function test_unauthenticated_user_is_redirected_to_login_preserving_query_params(): void
    {
        $url = '/computers/create?model=Foo&type_name=laptop&hard_drive_type=2';

        $response = $this->get($url);
        $response->assertRedirect(route('login', absolute: false));

        $intended = (string) session()->get('url.intended');
        $this->assertStringStartsWith(url('/computers/create'), $intended);
        parse_str(parse_url($intended, PHP_URL_QUERY) ?? '', $params);
        $this->assertSame('Foo', $params['model'] ?? null);
        $this->assertSame('laptop', $params['type_name'] ?? null);
        $this->assertSame('2', $params['hard_drive_type'] ?? null);
    }

    public function test_statusfilter_query_param_preselects_filter(): void
    {
        $user = User::factory()->create();

        Computer::factory()->create([
            'status' => ComputerStatus::New->value,
            'model'  => 'Sollte sichtbar sein',
        ]);
        Computer::factory()->create([
            'status' => ComputerStatus::Delivered->value,
            'model'  => 'Sollte versteckt sein',
        ]);

        $this->actingAs($user)
            ->get('/computers?statusFilter=' . ComputerStatus::New->value)
            ->assertOk()
            ->assertSee('Sollte sichtbar sein')
            ->assertDontSee('Sollte versteckt sein');
    }

    public function test_combined_status_and_class_filters_via_url(): void
    {
        $user = User::factory()->create();

        Computer::factory()->create([
            'status'       => ComputerStatus::New->value,
            'device_class' => DeviceClass::Laptop->value,
            'model'        => 'Laptop New',
        ]);
        Computer::factory()->create([
            'status'       => ComputerStatus::New->value,
            'device_class' => DeviceClass::Desktop->value,
            'model'        => 'Desktop New',
        ]);
        Computer::factory()->create([
            'status'       => ComputerStatus::Delivered->value,
            'device_class' => DeviceClass::Laptop->value,
            'model'        => 'Laptop Delivered',
        ]);

        $url = '/computers?statusFilter=' . ComputerStatus::New->value
             . '&classFilter=' . DeviceClass::Laptop->value;

        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertSee('Laptop New')
            ->assertDontSee('Desktop New')
            ->assertDontSee('Laptop Delivered');
    }

    public function test_activity_log_descriptions_and_values_are_german(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $computer = Computer::factory()->create([
            'device_class' => DeviceClass::Laptop->value,
            'status'       => ComputerStatus::New->value,
            'has_webcam'   => false,
        ]);

        $computer->update([
            'status'     => ComputerStatus::Refurbished->value,
            'has_webcam' => true,
        ]);

        $activities = $computer->activitiesAsSubject()->orderByDesc('id')->get();

        $this->assertCount(2, $activities);
        $this->assertSame('Computer bearbeitet', $activities[0]->description);
        $this->assertSame('Computer angelegt', $activities[1]->description);

        $this->assertSame('Computer angelegt', Computer::activityEventLabel('created'));
        $this->assertSame('Computer bearbeitet', Computer::activityEventLabel('updated'));
        $this->assertSame('Computer gelöscht', Computer::activityEventLabel('deleted'));
        $this->assertSame('Computer bearbeitet', Computer::activityEventLabel('updated', 'updated'));

        // activityDescription() berücksichtigt Status-Übergänge speziell
        $createdActivity = $activities[1]; // 'created'
        $updatedActivity = $activities[0]; // 'updated' mit Status-Wechsel
        $this->assertSame('Computer angelegt', Computer::activityDescription($createdActivity));
        $this->assertSame(
            'Status von „Neu“ auf „Aufbereitet“ geändert',
            Computer::activityDescription($updatedActivity)
        );

        // Update OHNE Status-Wechsel fällt auf "Computer bearbeitet" zurück
        $computer->update(['model' => 'Anderes Modell']);
        $modelOnly = $computer->activitiesAsSubject()->orderByDesc('id')->first();
        $this->assertSame('Computer bearbeitet', Computer::activityDescription($modelOnly));

        $this->assertSame('Geräteklasse', Computer::fieldLabel('device_class'));
        $this->assertSame('Aufbereitet', Computer::formatActivityValue('status', ComputerStatus::Refurbished->value));
        $this->assertSame('Laptop', Computer::formatActivityValue('device_class', DeviceClass::Laptop->value));
        $this->assertSame('ja', Computer::formatActivityValue('has_webcam', true));
        $this->assertSame('nein', Computer::formatActivityValue('has_webcam', false));
    }

    public function test_index_can_filter_by_status_and_class(): void
    {
        $user = User::factory()->create();

        Computer::factory()->create([
            'status'       => ComputerStatus::New->value,
            'device_class' => DeviceClass::Laptop->value,
            'model'        => 'Laptop New',
        ]);
        Computer::factory()->create([
            'status'       => ComputerStatus::Delivered->value,
            'device_class' => DeviceClass::Desktop->value,
            'model'        => 'Desktop Delivered',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('statusFilter', ComputerStatus::Delivered->value)
            ->assertSee('Desktop Delivered')
            ->assertDontSee('Laptop New');
    }
}
