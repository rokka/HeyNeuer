<?php

namespace App\Livewire\Users;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class InviteForm extends Component
{
    public string $email = '';

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', Rule::unique('users', 'email')],
        ];
    }

    public function send(): void
    {
        $this->authorize('create', User::class);

        $validated = $this->validate();

        $user = User::create([
            'email'            => $validated['email'],
            'invited_at'       => now(),
            'invitation_token' => User::generateInvitationToken(),
        ]);

        Mail::to($user->email)->send(new UserInvitationMail($user));

        session()->flash('status', 'Einladung an ' . $user->email . ' wurde versendet.');

        $this->reset('email');

        $this->redirectRoute('users.index', navigate: true);
    }

    #[Layout('layouts.app')]
    #[Title('Benutzer einladen')]
    public function render(): mixed
    {
        return view('livewire.users.invite-form');
    }
}
