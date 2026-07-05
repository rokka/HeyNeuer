<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Neue Ausgabe</h2>
</x-slot>

<div class="py-6 sm:py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-4">
                <i class="fas fa-shield-alt text-gray-400 mr-1" aria-hidden="true"></i>
                Vor-/Nachname und Geburtsdatum werden <strong>nicht</strong> gespeichert.
                Es wird nur ein nicht-umkehrbarer Hash daraus gebildet, um Doppelausgaben zu erkennen.
            </p>

            <form wire:submit="save" class="space-y-4">
                @if ($errors->has('recipient'))
                    <div class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800">
                        <i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i>
                        {{ $errors->first('recipient') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="first_name" value="Vorname" :required="true" />
                        <x-text-input wire:model="first_name" id="first_name" type="text" class="mt-1 block w-full" required autofocus autocomplete="off" />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="last_name" value="Nachname" :required="true" />
                        <x-text-input wire:model="last_name" id="last_name" type="text" class="mt-1 block w-full" required autocomplete="off" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="birthdate" value="Geburtsdatum" :required="true" />
                    <x-text-input wire:model="birthdate" id="birthdate" type="date" class="mt-1 block w-full" required autocomplete="off" />
                    <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
                </div>

                <div x-data="qrScanner" @qr-scanned.window="document.getElementById('computer_number_input').value = $event.detail.code; document.getElementById('computer_number_input').dispatchEvent(new Event('input'))">
                    <x-input-label for="computer_number_input" value="Computernummer" :required="true" />
                    <p class="text-xs text-gray-500 mb-1">
                        Eingabe als <code class="bg-gray-100 px-1 rounded">HA-E-1234</code> oder nur als Zahl <code class="bg-gray-100 px-1 rounded">1234</code>.
                    </p>
                    <div class="flex gap-2">
                        <button type="button" @click="open()"
                                class="mt-1 inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700 whitespace-nowrap"
                                title="QR-Code scannen">
                            <i class="fas fa-qrcode mr-1" aria-hidden="true"></i>
                            Scannen
                        </button>
                        <x-text-input wire:model="computer_number_input" id="computer_number_input" type="text"
                                      class="mt-1 block w-full font-mono" required autocomplete="off" placeholder="HA-E-1234" />
                    </div>
                    <x-input-error :messages="$errors->get('computer_number_input')" class="mt-2" />

                    {{-- QR-Scanner-Overlay --}}
                    <div x-show="active" x-cloak
                         class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4"
                         @keydown.escape.window="close()">
                        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold">QR-Code scannen</h3>
                                <button type="button" @click="close()" class="text-gray-500 hover:text-gray-700" aria-label="Schließen">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-600 mb-2">Bitte den Aufkleber des Computers vor die Kamera halten.</p>
                            <div x-ref="reader" id="qr-reader" class="w-full"></div>
                            <p x-show="error" x-text="error" class="text-sm text-red-600 mt-2"></p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('distributions.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">Abbrechen</a>
                    <x-primary-button>Ausgabe erfassen</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
