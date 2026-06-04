@props(['alt' => 'Hey, Alter! Essen'])

<img src="{{ asset('images/heyalter-logo.png') }}"
     alt="{{ $alt }}"
     loading="lazy"
     decoding="async"
     width="239"
     height="240"
     {{ $attributes }}>
