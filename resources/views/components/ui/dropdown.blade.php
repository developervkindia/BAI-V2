@props([
    'align' => 'right',
    'width' => '56',
])

@php
    $alignClasses = [
        'left' => 'left-0',
        'right' => 'right-0',
    ][$align] ?? 'right-0';

    $widthClass = "w-{$width}";
@endphp

<div x-data="{ open: false }" @click.away="open = false" class="relative">
    <!-- Trigger -->
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <!-- Content -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClass }} {{ $alignClasses }} rounded-2xl bg-white dark:bg-gray-800 shadow-2xl shadow-gray-900/10 border border-gray-100 dark:border-gray-700 py-2 ring-1 ring-black/5"
        x-cloak
        @click="open = false"
    >
        {{ $slot }}
    </div>
</div>
