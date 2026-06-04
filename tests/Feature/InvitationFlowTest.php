<?php

namespace Tests\Feature;

use App\Livewire\Auth\AcceptInvitation;
use App\Livewire\Users\InviteForm;
use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_invite_a_user(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(InviteForm::class)
            ->set('email', 'neuer@example.com')
            ->call('send')
            ->assertHasNoErrors()
            ->assertRedirect(route('users.index', absolute: false));

        $invitee = User::where('email', 'neuer@example.com')->first();

        $this->assertNotNull($invitee);
        $this->assertNotNull($invitee->invitation_token);
        $this->assertNotNull($invitee->invited_at);
        $this->assertNull($invitee->registered_at);
        $this->assertNull($invitee->password);

        Mail::assertSent(UserInvitationMail::class, fn ($mail) => $mail->invitee->is($invitee));
    }

    public function test_non_admin_cannot_invite_a_user(): void
    {
        $regular = User::factory()->create();

        Livewire::actingAs($regular)
            ->test(InviteForm::class)
            ->set('email', 'someone@example.com')
            ->call('send')
            ->assertForbidden();
    }

    public function test_invitee_can_accept_invitation_and_is_logged_in(): void
    {
        $invitee = User::factory()->invited()->create();
        $token = $invitee->invitation_token;

        Livewire::test(AcceptInvitation::class, ['token' => $token])
            ->set('name', 'Anna Schmidt')
            ->set('password', 'geheim-1234')
            ->set('password_confirmation', 'geheim-1234')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $invitee->refresh();

        $this->assertSame('Anna Schmidt', $invitee->name);
        $this->assertNotNull($invitee->password);
        $this->assertNotNull($invitee->registered_at);
        $this->assertNull($invitee->invitation_token);

        $this->assertAuthenticatedAs($invitee);
    }

    public function test_invalid_invitation_token_fails(): void
    {
        Livewire::test(AcceptInvitation::class, ['token' => 'does-not-exist'])
            ->assertHasErrors('token');
    }

    public function test_invitation_route_requires_valid_signature(): void
    {
        $invitee = User::factory()->invited()->create();

        $unsignedResponse = $this->get('/invitation/' . $invitee->invitation_token);
        $unsignedResponse->assertForbidden();
    }
}
