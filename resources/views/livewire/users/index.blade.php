<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Benutzer</h2>
        <a href="{{ route('users.invite') }}" wire:navigate
           class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            Neuen Benutzer einladen
        </a>
    </div>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @if ($selfRegisterLink)
            <div class="mb-4 rounded-md bg-indigo-50 border border-indigo-200 p-3 sm:p-4">
                <h3 class="text-sm font-semibold text-indigo-900 mb-1">
                    <i class="fas fa-link mr-1" aria-hidden="true"></i>
                    Selbst-Registrierungs-Link
                </h3>
                <p class="text-xs text-indigo-900 mb-2">
                    Wer diesen Link kennt, kann sich selbst ein (Nicht-Admin-)Konto anlegen.
                    Behandeln Sie ihn wie ein Passwort. Bei jeder Selbst-Registrierung werden alle Admins per E-Mail informiert.
                </p>
                <div class="flex items-center gap-2"
                     x-data="{ copied: false }">
                    <input type="text" readonly
                           value="{{ $selfRegisterLink }}"
                           class="flex-1 text-xs font-mono bg-white border border-indigo-300 rounded px-2 py-1.5"
                           x-ref="link"
                           @click="$refs.link.select()">
                    <button type="button"
                            @click="navigator.clipboard.writeText($refs.link.value); copied = true; setTimeout(() => copied = false, 1500)"
                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded hover:bg-indigo-700 whitespace-nowrap">
                        <i class="fas fa-copy mr-1" aria-hidden="true"></i>
                        <span x-show="!copied">Kopieren</span>
                        <span x-show="copied" x-cloak>Kopiert!</span>
                    </button>
                </div>
                <p class="text-xs text-indigo-700 mt-2">
                    Token rotieren mit <code class="bg-indigo-100 px-1 rounded">php artisan auth:rotate-self-register-token</code>
                </p>
            </div>
        @endif

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">E-Mail</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Rolle</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Registriert</th>
                        <th class="px-6 py-3">Letzter Login</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $u)
                        <tr role="link" tabindex="0"
                            @click="Livewire.navigate('{{ route('users.edit', $u) }}')"
                            @keydown.enter.prevent="Livewire.navigate('{{ route('users.edit', $u) }}')"
                            class="cursor-pointer hover:bg-gray-50 focus:bg-gray-100 outline-none">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $u->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $u->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($u->is_admin)
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">Administrator</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">Benutzer</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($u->hasAcceptedInvitation())
                                    <span class="text-green-700">aktiv</span>
                                @else
                                    <span class="text-amber-700">eingeladen</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $u->registered_at?->format('d.m.Y') ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $u->last_login_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Keine Benutzer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $users->links() }}</div>
        </div>
    </div>
</div>
