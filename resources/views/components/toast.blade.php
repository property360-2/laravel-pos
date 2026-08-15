<div
    x-data
    x-cloak
    class="fixed top-4 right-4 z-[100] flex w-80 flex-col gap-2"
>
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="flex items-start gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg"
            :class="toast.type === 'error' ? 'bg-red-600' : 'bg-emerald-600'"
        >
            <span class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-xs">
                <span class="material-icons" x-text="toast.type === 'error' ? 'close' : 'check'"></span>
            </span>
            <span class="flex-1" x-text="toast.message"></span>
            <button @click="toast.visible = false" class="text-white/70 hover:text-white"><span class="material-icons text-lg leading-none">close</span></button>
        </div>
    </template>
</div>
