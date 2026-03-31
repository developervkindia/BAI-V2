@props([
    'src' => null,
    'name' => '',
    'size' => 'md',
    'online' => false,
])

@php
    $sizes = [
        'xs' => 'w-6 h-6 text-[10px]',
        'sm' => 'w-8 h-8 text-xs',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-14 h-14 text-lg',
        'xl' => 'w-20 h-20 text-2xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
    $onlineDot = [
        'xs' => 'w-1.5 h-1.5 border',
        'sm' => 'w-2 h-2 border-[1.5px]',
        'md' => 'w-2.5 h-2.5 border-2',
        'lg' => 'w-3 h-3 border-2',
        'xl' => 'w-4 h-4 border-2',
    ][$size] ?? 'w-2.5 h-2.5 border-2';
@endphp

<div class="relative inline-flex shrink-0">
    @if($src)
        <img
            src="{{ $src }}"
            alt="{{ $name }}"
            {{ $attributes->merge(['class' => "$sizeClass rounded-full object-cover ring-2 ring-white dark:ring-gray-800"]) }}
        />
    @else
        <div {{ $attributes->merge(['class' => "$sizeClass rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white font-bold flex items-center justify-center ring-2 ring-white dark:ring-gray-800"]) }}>
            {{ $initials }}
        </div>
    @endif

    @if($online)
        <span class="absolute bottom-0 right-0 {{ $onlineDot }} bg-success-400 border-white dark:border-gray-800 rounded-full"></span>
    @endif
</div>
