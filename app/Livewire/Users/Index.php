<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    #[Layout('layouts.app')]
    #[Title('Benutzer')]
    public function render(): mixed
    {
        $token = config('auth.self_registration_token');
        $selfRegisterLink = ! empty($token)
            ? url(route('self-register', ['token' => $token], absolute: false))
            : null;

        return view('livewire.users.index', [
            'users'            => User::orderBy('email')->paginate(25),
            'selfRegisterLink' => $selfRegisterLink,
        ]);
    }
}
