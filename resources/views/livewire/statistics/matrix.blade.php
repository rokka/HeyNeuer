<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Statistik</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3"></th>
                        @foreach ($classes as $c)
                            <th class="px-4 py-3 text-center">
                                <a href="{{ route('computers.index', ['classFilter' => $c->value]) }}"
                                   wire:navigate
                                   class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                    <i class="{{ $c->icon() }} mr-1" aria-hidden="true"></i>
                                    {{ $c->label() }}
                                </a>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-center bg-gray-100">Summe</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($statuses as $s)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium">
                                <a href="{{ route('computers.index', ['statusFilter' => $s->value]) }}"
                                   wire:navigate
                                   class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                    {{ $s->label() }}
                                </a>
                            </td>
                            @foreach ($classes as $c)
                                <td class="px-0 py-0 text-sm text-center">
                                    <a href="{{ route('computers.index', ['statusFilter' => $s->value, 'classFilter' => $c->value]) }}"
                                       wire:navigate
                                       class="block px-4 py-3 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900">
                                        {{ $matrix[$s->value][$c->value] ?? 0 }}
                                    </a>
                                </td>
                            @endforeach
                            <td class="px-0 py-0 text-sm text-center font-semibold bg-gray-50">
                                <a href="{{ route('computers.index', ['statusFilter' => $s->value]) }}"
                                   wire:navigate
                                   class="block px-4 py-3 text-indigo-700 hover:bg-indigo-100 hover:text-indigo-900">
                                    {{ $rowTotals[$s->value] ?? 0 }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-100">
                        <td class="px-4 py-3"></td>
                        @foreach ($classes as $c)
                            <td class="px-0 py-0 text-sm text-center font-semibold">
                                <a href="{{ route('computers.index', ['classFilter' => $c->value]) }}"
                                   wire:navigate
                                   class="block px-4 py-3 text-indigo-700 hover:bg-indigo-200 hover:text-indigo-900">
                                    {{ $colTotals[$c->value] ?? 0 }}
                                </a>
                            </td>
                        @endforeach
                        <td class="px-0 py-0 text-sm text-center font-bold">
                            <a href="{{ route('computers.index') }}"
                               wire:navigate
                               class="block px-4 py-3 text-indigo-700 hover:bg-indigo-200 hover:text-indigo-900">
                                {{ $grandTotal }}
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
