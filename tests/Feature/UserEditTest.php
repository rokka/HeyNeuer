<?php

namespace Tests\Feature;

use App\Livewire\Users\Edit;
use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class UserEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_user_basics(): void
    {
        $admin  = User::factory()->admin()->create();
        $target = User::factory()->create(['name' => 'Alt', 'email' => 'alt@example.com', 'is_admin' => false]);

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $target])
            ->set('name', 'Neu')
            ->set('email', 'neu@example.com')
            ->set('is_admin', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index', absolute: false));

        $target->refresh();
        $this->assertSame('Neu', $target->name);
        $this->assertSame('neu@example.com', $target->email);
        $this->assertTrue($target->is_admin);
    }

    public function test_admin_can_set_new_password(): void
    {
        $admin  = User::factory()->admin()->create();
        $target = User::factory()->create();
        $oldHash = $target->password;

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $target])
            ->set('new_password', 'streng-geheim-2026')
            ->set('new_password_confirmation', 'streng-geheim-2026')
            ->call('save')
            ->assertHasNoErrors();

        $target->refresh();
        $this->assertNotSame($oldHash, $target->password);
        $this->assertTrue(Hash::check('streng-geheim-2026', $target->password));
    }

    public function test_admin_cannot_remove_own_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $admin])
            ->set('is_admin', false)
            ->call('save')
            ->assertHasErrors(['is_admin']);

        $admin->refresh();
        $this->assertTrue($admin->is_admin);
    }

    public function test_non_admin_cannot_access_edit(): void
    {
        $user   = User::factory()->create();
        $target = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Edit::class, ['user' => $target])
            ->assertForbidden();
    }

    public function test_admin_can_resend_invitation_to_invited_user(): void
    {
        Mail::fake();

        $admin   = User::factory()->admin()->create();
        $invited = User::factory()->invited()->create();
        $oldToken = $invited->invitation_token;

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $invited])
            ->call('resendInvitation')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index', absolute: false));

        $invited->refresh();
        $this->assertNotSame($oldToken, $invited->invitation_token);
        $this->assertNotNull($invited->invitation_token);

        Mail::assertSent(UserInvitationMail::class, fn ($mail) => $mail->invitee->is($invited));
    }

    public function test_resend_invitation_fails_for_active_user(): void
    {
        $admin  = User::factory()->admin()->create();
        $active = User::factory()->create(); // registered_at is set by factory

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $active])
            ->call('resendInvitation')
            ->assertHasErrors(['resend']);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin  = User::factory()->admin()->create();
        $target = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $target])
            ->call('delete')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index', absolute: false));

        $this->assertNull($target->fresh());
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(Edit::class, ['user' => $admin])
            ->call('delete')
            ->assertForbidden();

        $this->assertNotNull($admin->fresh());
    }
}
