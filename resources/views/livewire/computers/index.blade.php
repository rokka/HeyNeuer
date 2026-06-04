<x-slot name="header">
    <div class="flex justify-between items-center gap-2">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Computer</h2>
        <a href="{{ route('computers.create') }}" wire:navigate
           class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 whitespace-nowrap">
            <i class="fas fa-plus sm:mr-2" aria-hidden="true"></i>
            <span class="hidden sm:inline">Neuer Computer</span>
        </a>
    </div>
</x-slot>

<div class="py-6 sm:py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mx-4 sm:mx-0 mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 border-b border-gray-200">
                <div class="sm:col-span-2 lg:col-span-1">
                    <x-input-label for="search" value="Suche" />
                    <x-text-input wire:model.live.debounce.300ms="search" id="search" class="block mt-1 w-full" placeholder="Nummer, Modell, CPU, Kommentar..." />
                </div>
                <div>
                    <x-input-label value="Status" />
                    <select wire:model.live="statusFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Alle</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label value="Geräteklasse" />
                    <select wire:model.live="classFilter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Alle</option>
                        @foreach ($deviceClasses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Desktop / Tablet: Tabelle ab sm: --}}
            <table class="min-w-full divide-y divide-gray-200 hidden sm:table">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Gerät</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">RAM / Disk</th>
                        <th class="px-6 py-3">Angelegt</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($computers as $c)
                        <tr role="link" tabindex="0"
                            @click="Livewire.navigate('{{ route('computers.edit', $c) }}')"
                            @keydown.enter.prevent="Livewire.navigate('{{ route('computers.edit', $c) }}')"
                            class="cursor-pointer hover:bg-gray-50 focus:bg-gray-100 outline-none">
                            <td class="px-6 py-3 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-3">
                                    <i class="{{ $c->device_class->icon() }} text-gray-500 text-lg w-5 text-center" aria-hidden="true" title="{{ $c->device_class->label() }}"></i>
                                    <div class="flex flex-col leading-tight">
                                        <span class="font-mono font-medium text-gray-900">{{ $c->number }}</span>
                                        <span class="text-xs text-gray-500 truncate max-w-xs" title="{{ $c->model }}">{{ $c->model }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $c->status->badgeColor() }}">
                                    {{ $c->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm">
                                {{ $c->ram_gb ? $c->ram_gb . ' GB' : '—' }} / {{ $c->disk_type->label() }} {{ $c->disk_gb ? $c->disk_gb . ' GB' : '' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $c->created_at?->format('d.m.Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Keine Computer gefunden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Smartphone: Karten-Liste unterhalb sm: --}}
            <ul class="divide-y divide-gray-200 sm:hidden">
                @forelse ($computers as $c)
                    <li role="link" tabindex="0"
                        @click="Livewire.navigate('{{ route('computers.edit', $c) }}')"
                        @keydown.enter.prevent="Livewire.navigate('{{ route('computers.edit', $c) }}')"
                        class="px-4 py-3 cursor-pointer hover:bg-gray-50 focus:bg-gray-100 outline-none">
                        <div class="flex items-start gap-3">
                            <i class="{{ $c->device_class->icon() }} text-gray-500 text-xl w-6 text-center mt-1" aria-hidden="true" title="{{ $c->device_class->label() }}"></i>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-mono font-medium text-gray-900">{{ $c->number }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-semibold rounded {{ $c->status->badgeColor() }} whitespace-nowrap">
                                        {{ $c->status->label() }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-800 truncate" title="{{ $c->model }}">{{ $c->model }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $c->ram_gb ? $c->ram_gb . ' GB' : '—' }} · {{ $c->disk_type->label() }}{{ $c->disk_gb ? ' ' . $c->disk_gb . ' GB' : '' }}
                                    · {{ $c->created_at?->format('d.m.Y') }}
                                </p>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-gray-500">Keine Computer gefunden.</li>
                @endforelse
            </ul>

            <div class="p-4">{{ $computers->links() }}</div>
        </div>
    </div>
</div>
