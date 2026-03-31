@props([
    'color' => 'gray',
    'size' => 'md',
    'dot' => false,
])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
        'violet' => 'bg-primary-100 text-primary-700',
        'fuchsia' => 'bg-secondary-100 text-secondary-700',
        'cyan' => 'bg-accent-100 text-accent-700',
        'lime' => 'bg-success-100 text-success-700',
        'amber' => 'bg-sunny-100 text-sunny-700',
        'rose' => 'bg-danger-100 text-danger-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'pink' => 'bg-pink-100 text-pink-700',
        'green' => 'bg-emerald-100 text-emerald-700',
        'red' => 'bg-red-100 text-red-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'black' => 'bg-gray-800 text-white',
    ];

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[10px]',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];

    $colorClass = $colors[$color] ?? $colors['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-semibold rounded-full $colorClass $sizeClass"]) }}>
    @if($dot)
        <span class="mr-1.5 w-1.5 h-1.5 rounded-full bg-current"></span>
    @endif
    {{ $slot }}
</span>
