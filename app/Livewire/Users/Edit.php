<?php

namespace App\Livewire\Users;

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public bool $is_admin = false;

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public function mount(User $user): void
    {
        $this->authorize('update', $user);

        $this->user     = $user;
        $this->name     = $user->name ?? '';
        $this->email    = $user->email;
        $this->is_admin = (bool) $user->is_admin;
    }

    public function rules(): array
    {
        return [
            'name'         => ['nullable', 'string', 'max:200'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'is_admin'     => ['boolean'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->user);

        $validated = $this->validate();

        // Selbstschutz: ein Admin darf sich nicht selbst die Admin-Rolle entziehen
        if (Auth::id() === $this->user->id && ! $validated['is_admin']) {
            $this->addError('is_admin', 'Sie können sich nicht selbst die Administrator-Rolle entziehen.');
            return;
        }

        $this->user->fill([
            'name'     => $validated['name'] ?: null,
            'email'    => $validated['email'],
            'is_admin' => $validated['is_admin'],
        ]);

        if (! empty($validated['new_password'])) {
            $this->user->password = Hash::make($validated['new_password']);
        }

        $this->user->save();

        $this->reset(['new_password', 'new_password_confirmation']);

        session()->flash('status', 'Benutzer ' . $this->user->email . ' wurde aktualisiert.');
        $this->redirectRoute('users.index', navigate: true);
    }

    public function resendInvitation(): void
    {
        $this->authorize('update', $this->user);

        if ($this->user->hasAcceptedInvitation()) {
            $this->addError('resend', 'Dieser Benutzer hat die Einladung bereits akzeptiert.');
            return;
        }

        $this->user->forceFill([
            'invitation_token' => User::generateInvitationToken(),
            'invited_at'       => now(),
        ])->save();

        Mail::to($this->user->email)->send(new UserInvitationMail($this->user));

        session()->flash('status', 'Einladung an ' . $this->user->email . ' wurde erneut versendet.');
        $this->redirectRoute('users.index', navigate: true);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->user);

        $email = $this->user->email;
        $this->user->delete();

        session()->flash('status', 'Benutzer ' . $email . ' wurde gelöscht.');
        $this->redirectRoute('users.index', navigate: true);
    }

    #[Layout('layouts.app')]
    #[Title('Benutzer bearbeiten')]
    public function render(): mixed
    {
        return view('livewire.users.edit');
    }
}
