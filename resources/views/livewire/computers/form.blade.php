<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        @if ($computer)
            Computer bearbeiten — <span class="font-mono">{{ $computer->number }}</span>
        @else
            Neuen Computer anlegen
        @endif
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow sm:rounded-lg">
            <form wire:submit="save" class="p-6 space-y-6">
                <p class="text-sm text-gray-500">
                    @if ($computer)
                        Die Nummer wird automatisch vergeben und kann nicht geändert werden.
                    @else
                        Die eindeutige Nummer wird automatisch beim Speichern vergeben.
                    @endif
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="device_class" value="Geräteklasse" :required="true" />
                        <select wire:model="device_class" id="device_class" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach ($deviceClasses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('device_class')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" :required="true" />
                        <select wire:model="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="model" value="Modell" :required="true" />
                    <x-text-input wire:model="model" id="model" type="text" class="mt-1 block w-full" placeholder="z.B. Dell Precision 5520" required />
                    <x-input-error :messages="$errors->get('model')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="has_webcam" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        Web-Cam integriert
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="has_wifi" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        WLAN integriert
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="cpu_model" value="CPU-Modell" />
                        <x-text-input wire:model="cpu_model" id="cpu_model" type="text" class="mt-1 block w-full" placeholder="z.B. Intel Core i7-7820HQ" />
                        <x-input-error :messages="$errors->get('cpu_model')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="ram_gb" value="Arbeitsspeicher (GB)" />
                        <x-text-input wire:model="ram_gb" id="ram_gb" type="number" min="0" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('ram_gb')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="disk_type" value="Festplattentyp" :required="true" />
                        <select wire:model="disk_type" id="disk_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @foreach ($diskTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('disk_type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="disk_gb" value="Festplattengröße (GB)" />
                        <x-text-input wire:model="disk_gb" id="disk_gb" type="number" min="0" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('disk_gb')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="comment" value="Kommentar" />
                    <textarea wire:model="comment" id="comment" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                    <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        @if ($computer)
                            @can('delete', $computer)
                                <button type="button"
                                        wire:click="delete"
                                        wire:confirm="Diesen Computer wirklich löschen? Dies kann nicht rückgängig gemacht werden."
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                    <i class="fas fa-trash mr-2" aria-hidden="true"></i>
                                    Löschen
                                </button>
                            @endcan
                        @endif
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('computers.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900">Abbrechen</a>
                        <x-primary-button>Speichern</x-primary-button>
                    </div>
                </div>
            </form>
        </div>

        @if ($computer && $activities->isNotEmpty())
            <div class="bg-white shadow sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Historie</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($activities as $a)
                            <li class="border-b border-gray-100 pb-2">
                                <div class="text-gray-500">
                                    {{ $a->created_at->format('d.m.Y H:i') }}
                                    — {{ \App\Models\Computer::activityDescription($a) }}
                                    @if ($a->causer)
                                        von {{ $a->causer->name }}
                                    @endif
                                </div>
                                @if ($a->attribute_changes && $a->attribute_changes->has('attributes'))
                                    <div class="text-xs text-gray-700 mt-1">
                                        @foreach (($a->attribute_changes['attributes'] ?? []) as $key => $val)
                                            <div>
                                                <span class="font-medium">{{ \App\Models\Computer::fieldLabel($key) }}:</span>
                                                @if (isset($a->attribute_changes['old'][$key]) && $a->attribute_changes['old'][$key] !== $val)
                                                    <span class="line-through text-red-600">{{ \App\Models\Computer::formatActivityValue($key, $a->attribute_changes['old'][$key]) }}</span>
                                                    →
                                                @endif
                                                <span class="text-green-700">{{ \App\Models\Computer::formatActivityValue($key, $val) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
