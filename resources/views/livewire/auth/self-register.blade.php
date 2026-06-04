<div>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Konto anlegen</h1>
        <p class="text-sm text-gray-600 mt-2">
            Sie wurden eingeladen, sich selbst ein Konto für die Hey, Alter! Essen Computerverwaltung anzulegen.
        </p>
    </div>

    <form wire:submit="submit" class="space-y-4">
        <div>
            <x-input-label for="name" value="Name" :required="true" />
            <x-text-input wire:model="name" id="name" type="text" class="block mt-1 w-full" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="E-Mail-Adresse" :required="true" />
            <x-text-input wire:model="email" id="email" type="email" class="block mt-1 w-full" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
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
                Konto anlegen
            </x-primary-button>
        </div>
    </form>
</div>
