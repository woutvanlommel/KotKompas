@props(['variant' => 'light'])

@php
    $tone = $variant === 'dark'
        ? 'bg-secondary-300 text-primary-900'
        : 'bg-secondary-600 text-white';
@endphp

{{-- "Uitgelicht" marker for featured (betalende) koten. --}}
<span {{ $attributes->class(['inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[0.6rem] font-semibold uppercase tracking-[0.12em] shadow-sm', $tone]) }}>
    <svg class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.8 6.2 20.9l1.1-6.5L2.6 9.3l6.5-.9L12 2.5z"/>
    </svg>
    Uitgelicht
</span>
