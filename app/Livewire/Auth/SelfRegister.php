<?php

namespace App\Livewire\Auth;

use App\Mail\NewSelfRegistrationMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SelfRegister extends Component
{
    public string $token = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        if (! $this->tokenIsValid($token)) {
            throw new NotFoundHttpException();
        }

        $this->token = $token;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:200'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function submit(): void
    {
        // Re-check zur Sicherheit, falls Config geändert wurde
        if (! $this->tokenIsValid($this->token)) {
            throw new NotFoundHttpException();
        }

        $validated = $this->validate();

        /** @var User $user */
        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'password'          => Hash::make($validated['password']),
            'is_admin'          => false, // hardcoded, NIE aus User-Input
            'registered_at'     => now(),
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        // Admins benachrichtigen
        $admins = User::where('is_admin', true)->whereNotNull('registered_at')->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new NewSelfRegistrationMail($user));
        }

        Auth::login($user);

        $this->redirectRoute('dashboard', navigate: true);
    }

    protected function tokenIsValid(string $token): bool
    {
        $expected = (string) config('auth.self_registration_token');

        if ($expected === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    #[Layout('layouts.guest')]
    #[Title('Konto anlegen')]
    public function render(): mixed
    {
        return view('livewire.auth.self-register');
    }
}
