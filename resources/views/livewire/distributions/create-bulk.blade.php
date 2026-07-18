<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Massenausgabe</h2>
</x-slot>

<div class="py-6 sm:py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6">
            @include('livewire.distributions.tabs', ['active' => 'bulk'])

            <p class="text-sm text-gray-600 mb-4">
                <i class="fas fa-info-circle text-gray-400 mr-1" aria-hidden="true"></i>
                Computer nacheinander scannen oder eintippen. Beim Speichern werden alle erfassten Computer auf <strong>Ausgeliefert</strong> gesetzt.
            </p>

            @if (session('status') && ! $errors->any())
                <div class="rounded-md bg-green-50 border border-green-200 p-3 text-sm text-green-800 mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @error('numbers')
                <div class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800 mb-4">
                    <i class="fas fa-exclamation-triangle mr-1" aria-hidden="true"></i>
                    {{ $message }}
                </div>
            @enderror

            <form wire:submit.prevent="addNumber" class="space-y-4">
                <div x-data="qrScanner"
                     @qr-scanned.window="$wire.set('computer_number_input', $event.detail.code).then(() => $wire.call('addNumber'))">
                    <x-input-label for="computer_number_input" value="Computernummer" />
                    <p class="text-xs text-gray-500 mb-1">
                        Eingabe als <code class="bg-gray-100 px-1 rounded">HA-E-1234</code> oder nur als Zahl <code class="bg-gray-100 px-1 rounded">1234</code>. Enter oder „Hinzufügen“ zum Übernehmen.
                    </p>
                    <div class="flex gap-2">
                        <button type="button" @click="open()"
                                class="mt-1 inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700 whitespace-nowrap"
                                title="QR-Code scannen">
                            <i class="fas fa-qrcode mr-1" aria-hidden="true"></i>
                            Scannen
                        </button>
                        <x-text-input wire:model="computer_number_input" id="computer_number_input" type="text"
                                      class="mt-1 block w-full font-mono" autocomplete="off" placeholder="HA-E-1234" autofocus />
                        <x-primary-button class="mt-1 whitespace-nowrap">
                            <i class="fas fa-plus mr-1" aria-hidden="true"></i>
                            Hinzufügen
                        </x-primary-button>
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
            </form>

            <div class="mt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    Erfasste Computer
                    <span class="text-gray-500 font-normal">({{ count($numbers) }})</span>
                </h3>

                @if (empty($numbers))
                    <p class="text-sm text-gray-500 italic">Noch keine Computer erfasst.</p>
                @else
                    <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                        @foreach ($numbers as $index => $number)
                            <li class="flex items-center justify-between px-3 py-2">
                                <span class="font-mono text-sm">{{ $number }}</span>
                                <button type="button" wire:click="removeNumber({{ $index }})"
                                        class="text-red-600 hover:text-red-800 text-sm"
                                        title="Entfernen">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                    Entfernen
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mt-6">
                <x-input-label for="comment" value="Kommentar (optional)" />
                <p class="text-xs text-gray-500 mb-1">Wird auf allen Ausgaben dieser Charge gespeichert.</p>
                <textarea wire:model="comment" id="comment" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                          placeholder="z.B. Verteilaktion 2026-07-17, Schule XYZ"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 mt-4 border-t border-gray-100">
                <a href="{{ route('distributions.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">Abbrechen</a>
                <x-primary-button wire:click="save" :disabled="empty($numbers)">
                    Ausgabe erfassen
                </x-primary-button>
            </div>
        </div>
    </div>
</div>
