<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Neuen Benutzer einladen
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-6">
                    Der eingeladene Benutzer erhält eine E-Mail mit einem Link, über den er sein Passwort
                    und seinen Namen festlegen kann. Der Link ist {{ config('auth.invitation_expiry_hours') }} Stunden gültig.
                </p>

                <form wire:submit="send" class="space-y-6">
                    <div>
                        <x-input-label for="email" value="E-Mail-Adresse" :required="true" />
                        <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('users.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">
                            Abbrechen
                        </a>
                        <x-primary-button>
                            Einladung senden
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
