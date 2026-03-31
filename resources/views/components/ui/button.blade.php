@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => false,
    'loading' => false,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'gradient-primary text-white hover:gradient-primary-hover shadow-lg shadow-primary-500/25 focus:ring-primary-500 rounded-xl',
        'secondary' => 'bg-white text-primary-700 border-2 border-primary-200 hover:border-primary-400 hover:bg-primary-50 focus:ring-primary-500 rounded-xl',
        'ghost' => 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 focus:ring-gray-400 rounded-xl',
        'danger' => 'bg-danger-500 text-white hover:bg-danger-600 shadow-lg shadow-danger-500/25 focus:ring-danger-500 rounded-xl',
        'icon' => 'p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-700 focus:ring-gray-400',
    ];

    $sizes = [
        'xs' => 'text-xs px-2.5 py-1.5 gap-1',
        'sm' => 'text-sm px-3 py-2 gap-1.5',
        'md' => 'text-sm px-4 py-2.5 gap-2',
        'lg' => 'text-base px-6 py-3 gap-2',
    ];

    $sizeClass = $icon ? '' : ($sizes[$size] ?? $sizes['md']);
    $variantClass = $variants[$variant] ?? $variants['primary'];
    $classes = "$baseClasses $variantClass $sizeClass";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
