<x-slot name="header">
    <div class="flex justify-between items-center gap-2">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ausgabe</h2>
        <a href="{{ route('distributions.create') }}" wire:navigate
           class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 whitespace-nowrap">
            <i class="fas fa-plus sm:mr-2" aria-hidden="true"></i>
            <span class="hidden sm:inline">Neue Ausgabe</span>
        </a>
    </div>
</x-slot>

<div class="py-6 sm:py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mx-4 sm:mx-0 mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            {{-- Desktop / Tablet: Tabelle ab sm: --}}
            <table class="min-w-full divide-y divide-gray-200 hidden sm:table">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Gerät</th>
                        <th class="px-6 py-3">Kommentar</th>
                        <th class="px-6 py-3">Datum der Abgabe</th>
                        <th class="px-6 py-3">Abgegeben von</th>
                        <th class="px-6 py-3">Hash</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($distributions as $d)
                        <tr>
                            <td class="px-6 py-3 whitespace-nowrap text-sm">
                                @if ($d->computer)
                                    <div class="flex items-center gap-3">
                                        <i class="{{ $d->computer->device_class->icon() }} text-gray-500 text-lg w-5 text-center" aria-hidden="true" title="{{ $d->computer->device_class->label() }}"></i>
                                        <div class="flex flex-col leading-tight">
                                            <span class="font-mono font-medium text-gray-900">{{ $d->computer->number }}</span>
                                            <span class="text-xs text-gray-500 truncate max-w-xs" title="{{ $d->computer->model }}">{{ $d->computer->model }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">gelöscht</span>
                                @endif
                            </td>
                            @php($comment = $d->comment ?: $d->computer?->comment)
                            <td class="px-6 py-3 text-sm text-gray-700 max-w-xs truncate" title="{{ $comment }}">
                                {{ $comment ?: '—' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm">{{ $d->distributed_at?->format('d.m.Y') }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm">
                                {{ $d->user?->name ?: ($d->user?->email ?? '(gelöscht)') }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-xs font-mono text-gray-600">
                                {{ $d->recipient_hash ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Keine Ausgaben erfasst.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Smartphone: Karten-Liste unterhalb sm: --}}
            <ul class="divide-y divide-gray-200 sm:hidden">
                @forelse ($distributions as $d)
                    <li class="px-4 py-3">
                        <div class="flex items-start gap-3">
                            @if ($d->computer)
                                <i class="{{ $d->computer->device_class->icon() }} text-gray-500 text-xl w-6 text-center mt-1" aria-hidden="true" title="{{ $d->computer->device_class->label() }}"></i>
                            @else
                                <i class="fas fa-question text-gray-400 text-xl w-6 text-center mt-1" aria-hidden="true"></i>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    @if ($d->computer)
                                        <span class="font-mono font-medium text-gray-900">{{ $d->computer->number }}</span>
                                    @else
                                        <span class="text-gray-400 italic text-sm">gelöscht</span>
                                    @endif
                                    <span class="text-xs text-gray-500 whitespace-nowrap">{{ $d->distributed_at?->format('d.m.Y') }}</span>
                                </div>
                                @if ($d->computer)
                                    <p class="text-sm text-gray-800 truncate" title="{{ $d->computer->model }}">{{ $d->computer->model }}</p>
                                @endif
                                @php($comment = $d->comment ?: $d->computer?->comment)
                                @if ($comment)
                                    <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">{{ $comment }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">
                                    Abgegeben von: {{ $d->user?->name ?: ($d->user?->email ?? '(gelöscht)') }}
                                </p>
                                @if ($d->recipient_hash)
                                    <p class="text-[10px] font-mono text-gray-500 mt-1 truncate">{{ $d->recipient_hash }}</p>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-center text-sm text-gray-500">Keine Ausgaben erfasst.</li>
                @endforelse
            </ul>

            <div class="p-4">{{ $distributions->links() }}</div>
        </div>
    </div>
</div>
