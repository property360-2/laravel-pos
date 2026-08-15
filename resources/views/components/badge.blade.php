@props(['variant' => 'default'])

@php
$variants = [
    'default' => 'bg-slate-100 text-slate-700',
    'success' => 'bg-emerald-100 text-emerald-700',
    'warning' => 'bg-amber-100 text-amber-700',
    'danger' => 'bg-red-100 text-red-700',
    'info' => 'bg-sky-100 text-sky-700',
    'primary' => 'bg-indigo-100 text-indigo-700',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium '.$variants[$variant]]) }}>
    {{ $slot }}
</span>
