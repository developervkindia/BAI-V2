@props([
    'percent' => 0,
    'showLabel' => true,
    'size' => 'md',
])

@php
    $percent = min(100, max(0, $percent));
    $heights = [
        'sm' => 'h-1.5',
        'md' => 'h-2',
        'lg' => 'h-3',
    ];
    $heightClass = $heights[$size] ?? $heights['md'];
    $barColor = $percent >= 100
        ? 'bg-gradient-to-r from-success-400 to-emerald-500'
        : 'bg-gradient-to-r from-primary-500 to-secondary-500';
@endphp

<div class="flex items-center gap-2">
    @if($showLabel)
        <span class="text-xs text-gray-500 dark:text-gray-400 min-w-[2rem]">{{ $percent }}%</span>
    @endif
    <div class="flex-1 {{ $heightClass }} bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
        <div
            class="{{ $heightClass }} {{ $barColor }} rounded-full transition-all duration-500"
            style="width: {{ $percent }}%"
        ></div>
    </div>
</div>
