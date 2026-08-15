@props([
    'label' => null,
    'error' => null,
    'type' => 'text',
    'name' => null,
])

@php
$id = $attributes->get('id') ?? $name ?? 'field-'.md5($label ?? uniqid());
@endphp

<div {{ $attributes->only(['class']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
    @endif

    <input
        id="{{ $id }}"
        type="{{ $type }}"
        name="{{ $name }}"
        @if ($error) aria-invalid="true" @endif
        {{ $attributes->except(['class', 'id'])->merge([
            'class' => 'block w-full rounded-lg border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset sm:text-sm '.($error
                ? 'ring-red-300 focus:ring-red-500 bg-red-50'
                : 'ring-slate-300 focus:ring-indigo-600 bg-white'),
        ]) }}
    />

    @if ($error)
        <p class="mt-1.5 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
