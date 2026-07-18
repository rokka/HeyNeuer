@php($active = $active ?? 'single')

<div class="border-b border-gray-200 mb-4">
    <nav class="-mb-px flex gap-4" aria-label="Ausgabemodus">
        <a href="{{ route('distributions.create') }}" wire:navigate
           class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium
                  {{ $active === 'single' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Einzelausgabe
        </a>
        <a href="{{ route('distributions.create.bulk') }}" wire:navigate
           class="whitespace-nowrap py-2 px-1 border-b-2 text-sm font-medium
                  {{ $active === 'bulk' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            Massenausgabe
        </a>
    </nav>
</div>
