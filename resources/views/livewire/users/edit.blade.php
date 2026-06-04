<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Benutzer bearbeiten — {{ $user->email }}
    </h2>
</x-slot>

<div class="py-6 sm:py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        {{-- Hauptformular --}}
        <div class="bg-white shadow sm:rounded-lg">
            <div class="p-6">
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        @if (! $user->hasAcceptedInvitation())
                            <p class="text-xs text-amber-700 mt-1">
                                Benutzer hat die Einladung noch nicht angenommen — der Name wird normalerweise vom Benutzer selbst gesetzt.
                            </p>
                        @endif
                    </div>

                    <div>
                        <x-input-label for="email" value="E-Mail-Adresse" :required="true" />
                        <x-text-input wire:model="email" id="email" type="email" class="mt-1 block w-full" required autocomplete="email" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_admin" id="is_admin" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <label for="is_admin" class="text-sm text-gray-700 select-none">Administrator</label>
                        <x-input-error :messages="$errors->get('is_admin')" class="ml-2" />
                    </div>

                    <hr class="my-2">

                    <div class="space-y-1">
                        <x-input-label for="new_password" value="Neues Passwort (optional)" />
                        <p class="text-xs text-gray-500">Leer lassen, um das aktuelle Passwort beizubehalten.</p>
                        <x-text-input wire:model="new_password" id="new_password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('new_password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="new_password_confirmation" value="Neues Passwort bestätigen" />
                        <x-text-input wire:model="new_password_confirmation" id="new_password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('users.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">Abbrechen</a>
                        <x-primary-button>Speichern</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Einladung erneut senden --}}
        @if (! $user->hasAcceptedInvitation())
            <div class="bg-amber-50 border border-amber-200 sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-amber-900">Einladung erneut senden</h3>
                <p class="text-sm text-amber-900 mt-1">
                    Dieser Benutzer hat die Einladung noch nicht angenommen
                    @if ($user->invited_at)
                        (eingeladen am {{ $user->invited_at->format('d.m.Y H:i') }}).
                    @endif
                    Sie können einen neuen Einladungs-Link generieren und per E-Mail verschicken — der alte Link wird damit ungültig.
                </p>
                <x-input-error :messages="$errors->get('resend')" class="mt-2" />
                <button type="button"
                        wire:click="resendInvitation"
                        wire:confirm="Einladung an {{ $user->email }} erneut senden? Der alte Einladungs-Link wird damit ungültig."
                        class="mt-3 inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                    <i class="fas fa-paper-plane mr-2" aria-hidden="true"></i>
                    Einladung erneut senden
                </button>
            </div>
        @endif

        {{-- Benutzer löschen --}}
        @can('delete', $user)
            <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6 border border-red-200">
                <h3 class="text-sm font-semibold text-red-800">Benutzer löschen</h3>
                <p class="text-sm text-gray-700 mt-1">
                    Diese Aktion entfernt den Benutzer dauerhaft. Bisherige Audit-Log-Einträge bleiben erhalten, der Benutzer wird dort als gelöscht angezeigt.
                </p>
                <button type="button"
                        wire:click="delete"
                        wire:confirm="Benutzer {{ $user->email }} wirklich löschen? Dies kann nicht rückgängig gemacht werden."
                        class="mt-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                    <i class="fas fa-trash mr-2" aria-hidden="true"></i>
                    Benutzer löschen
                </button>
            </div>
        @endcan

    </div>
</div>
