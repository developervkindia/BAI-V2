@props([
    'label' => null,
    'error' => null,
    'type' => 'text',
])

<div>
    @if($label)
        <label {{ $attributes->whereStartsWith('for') }} class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-xl border-2 px-4 py-3 text-gray-800 dark:text-gray-100 dark:bg-gray-800 placeholder-gray-400 transition-all duration-200 focus:outline-none focus:ring-4 ' .
                ($error
                    ? 'border-danger-400 focus:border-danger-500 focus:ring-danger-500/20'
                    : 'border-gray-200 dark:border-gray-600 focus:border-primary-500 focus:ring-primary-500/20')
        ]) }}
    />

    @if($error)
        <p class="mt-1 text-sm text-danger-500">{{ $error }}</p>
    @endif
</div>
