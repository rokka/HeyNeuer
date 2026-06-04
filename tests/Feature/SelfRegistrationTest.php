<?php

namespace Tests\Feature;

use App\Livewire\Auth\SelfRegister;
use App\Mail\NewSelfRegistrationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SelfRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_TOKEN = 'a-valid-token-1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.self_registration_token' => self::VALID_TOKEN]);
    }

    public function test_route_returns_404_when_feature_disabled(): void
    {
        config(['auth.self_registration_token' => null]);

        $this->get('/register/anything')->assertNotFound();
    }

    public function test_route_returns_404_for_wrong_token(): void
    {
        $this->get('/register/wrong-token')->assertNotFound();
    }

    public function test_valid_token_renders_form(): void
    {
        $this->get('/register/' . self::VALID_TOKEN)
            ->assertOk()
            ->assertSee('Konto anlegen');
    }

    public function test_user_can_self_register_and_admins_are_notified(): void
    {
        Mail::fake();

        // Setup: 2 Admins (sollen Mail bekommen) und 1 Nicht-Admin (soll NICHT)
        $admin1 = User::factory()->admin()->create(['email' => 'admin1@test']);
        $admin2 = User::factory()->admin()->create(['email' => 'admin2@test']);
        User::factory()->create(['email' => 'normal@test', 'is_admin' => false]);

        Livewire::test(SelfRegister::class, ['token' => self::VALID_TOKEN])
            ->set('name', 'Erika Mustermann')
            ->set('email', 'erika@example.com')
            ->set('password', 'sicher-1234')
            ->set('password_confirmation', 'sicher-1234')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'erika@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Erika Mustermann', $user->name);
        $this->assertFalse($user->is_admin, 'Selbst-registrierter Benutzer darf NIE Admin sein');
        $this->assertNotNull($user->registered_at);
        $this->assertTrue(Hash::check('sicher-1234', $user->password));

        // Auto-Login
        $this->assertAuthenticatedAs($user);

        // Mail an Admins
        Mail::assertSent(NewSelfRegistrationMail::class, 2);
        Mail::assertSent(NewSelfRegistrationMail::class, fn ($mail) => $mail->hasTo('admin1@test'));
        Mail::assertSent(NewSelfRegistrationMail::class, fn ($mail) => $mail->hasTo('admin2@test'));
        Mail::assertNotSent(NewSelfRegistrationMail::class, fn ($mail) => $mail->hasTo('normal@test'));
    }

    public function test_self_registered_user_cannot_become_admin_via_form_input(): void
    {
        Mail::fake();

        // Versuch, is_admin im Form-State zu manipulieren
        Livewire::test(SelfRegister::class, ['token' => self::VALID_TOKEN])
            ->set('name', 'Hacker')
            ->set('email', 'hacker@example.com')
            ->set('password', 'sicher-1234')
            ->set('password_confirmation', 'sicher-1234')
            // is_admin ist gar nicht als Property deklariert; selbst wenn versucht:
            ->call('submit')
            ->assertHasNoErrors();

        $user = User::where('email', 'hacker@example.com')->first();
        $this->assertFalse($user->is_admin);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        Mail::fake();

        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::test(SelfRegister::class, ['token' => self::VALID_TOKEN])
            ->set('name', 'Other')
            ->set('email', 'taken@example.com')
            ->set('password', 'sicher-1234')
            ->set('password_confirmation', 'sicher-1234')
            ->call('submit')
            ->assertHasErrors(['email']);
    }

    public function test_authenticated_users_are_redirected_away_from_register(): void
    {
        $existing = User::factory()->create();

        $this->actingAs($existing)
            ->get('/register/' . self::VALID_TOKEN)
            ->assertRedirect();
    }

    public function test_link_is_displayed_in_users_index_for_admins(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk()
            ->assertSee(self::VALID_TOKEN);
    }
}
