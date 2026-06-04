<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class AcceptInvitation extends Component
{
    public string $token = '';

    public string $name = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?User $invitee = null;

    public function mount(string $token): void
    {
        $this->token = $token;

        $this->invitee = User::where('invitation_token', $token)->first();

        if (! $this->invitee) {
            $this->addError('token', 'Die Einladung ist ungültig oder wurde bereits eingelöst.');
        }
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:200'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function submit(): void
    {
        if (! $this->invitee) {
            $this->addError('token', 'Die Einladung ist ungültig oder wurde bereits eingelöst.');
            return;
        }

        $validated = $this->validate();

        $this->invitee->forceFill([
            'name'              => $validated['name'],
            'password'          => Hash::make($validated['password']),
            'registered_at'     => now(),
            'invitation_token'  => null,
            'email_verified_at' => now(),
        ])->save();

        event(new Registered($this->invitee));

        Auth::login($this->invitee);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }

    #[Layout('layouts.guest')]
    #[Title('Einladung annehmen')]
    public function render(): mixed
    {
        return view('livewire.auth.accept-invitation');
    }
}
