@props(['show' => 'false', 'maxWidth' => 'max-w-lg'])

<div
    x-show="{{ $show }}"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="{{ $show }} = false"
>
    <div
        class="fixed inset-0 bg-slate-900/50 transition-opacity"
        x-show="{{ $show }}"
        x-transition.opacity
        @click="{{ $show }} = false"
    ></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div
            x-show="{{ $show }}"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full {{ $maxWidth }} rounded-xl bg-white text-left shadow-xl"
        >
            {{ $slot }}
        </div>
    </div>
</div>
