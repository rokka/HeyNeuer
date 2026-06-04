@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}@if ($required)<span class="text-red-600 ms-1" aria-hidden="true">*</span><span class="sr-only">(Pflichtfeld)</span>@endif
</label>
