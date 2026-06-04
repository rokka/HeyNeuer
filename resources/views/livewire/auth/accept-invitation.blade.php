<div>
    @if (! $invitee)
        <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold mb-1">Einladung ungültig</p>
            <p>Die Einladung ist ungültig oder wurde bereits eingelöst. Bitte wenden Sie sich an einen Administrator.</p>
            <x-input-error :messages="$errors->get('token')" class="mt-2" />
        </div>
    @else
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-900">Einladung annehmen</h1>
            <p class="text-sm text-gray-600 mt-2">Hallo {{ $invitee->email }} — bitte tragen Sie Ihren Namen ein und vergeben Sie ein Passwort.</p>
        </div>

        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="name" value="Name" :required="true" />
                <x-text-input wire:model="name" id="name" type="text" class="block mt-1 w-full" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Passwort" :required="true" />
                <x-text-input wire:model="password" id="password" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Passwort bestätigen" :required="true" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    Registrierung abschließen
                </x-primary-button>
            </div>
        </form>
    @endif
</div>
