@props([
    'label' => null,
    'error' => null,
    'name' => null,
    'options' => [],
    'placeholder' => null,
])

@php
$id = $attributes->get('id') ?? $name ?? 'select-'.md5($label ?? uniqid());
@endphp

<div {{ $attributes->only(['class']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        {{ $attributes->except(['class', 'id'])->merge([
            'class' => 'block w-full rounded-lg border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset bg-white '.($error
                ? 'ring-red-300 focus:ring-red-500'
                : 'ring-slate-300 focus:ring-indigo-600'),
        ]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>

    @if ($error)
        <p class="mt-1.5 text-xs text-red-600">{{ $error }}</p>
    @endif
</div>
