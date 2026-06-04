<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Startseite</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <p class="text-sm text-gray-500">Computer gesamt</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCount }}</p>
            </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Angelegte Computer (letzte 12 Wochen)</h3>
            <div
                wire:ignore
                x-data="dashboardChart({{ Js::from($chartLabels) }}, {{ Js::from($chartData) }})"
                x-init="render()"
                class="relative h-72"
            >
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        <div class="bg-white shadow sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Zuletzt angelegte Computer</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-2">Nummer</th>
                            <th class="px-4 py-2">Klasse</th>
                            <th class="px-4 py-2">Modell</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Angelegt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($latest as $c)
                            <tr role="link" tabindex="0"
                                @click="Livewire.navigate('{{ route('computers.edit', $c) }}')"
                                @keydown.enter.prevent="Livewire.navigate('{{ route('computers.edit', $c) }}')"
                                class="cursor-pointer hover:bg-gray-50 focus:bg-gray-100 outline-none">
                                <td class="px-4 py-2 text-sm font-mono">{{ $c->number }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <i class="{{ $c->device_class->icon() }} text-gray-500 w-4 mr-1.5" aria-hidden="true"></i>
                                    {{ $c->device_class->label() }}
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $c->model }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $c->status->badgeColor() }}">
                                        {{ $c->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $c->created_at?->format('d.m.Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500">Noch keine Computer angelegt.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
