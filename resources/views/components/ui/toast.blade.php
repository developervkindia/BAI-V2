<!-- Toast Container - Include once in the app layout -->
<div
    x-data
    class="fixed bottom-6 right-6 z-[100] space-y-3 pointer-events-none"
>
    <template x-for="toast in $store.toast.messages" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-8"
            class="pointer-events-auto flex items-center gap-3 px-5 py-4 rounded-2xl shadow-xl text-sm font-medium min-w-[300px] max-w-md relative overflow-hidden"
            :class="{
                'bg-success-50 text-success-800 border border-success-200': toast.type === 'success',
                'bg-danger-50 text-danger-800 border border-danger-200': toast.type === 'error',
                'bg-accent-50 text-accent-800 border border-accent-200': toast.type === 'info',
            }"
        >
            <!-- Icon -->
            <template x-if="toast.type === 'success'">
                <svg class="w-5 h-5 shrink-0 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </template>
            <template x-if="toast.type === 'error'">
                <svg class="w-5 h-5 shrink-0 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </template>
            <template x-if="toast.type === 'info'">
                <svg class="w-5 h-5 shrink-0 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </template>

            <span x-text="toast.message"></span>

            <button @click="$store.toast.remove(toast.id)" class="ml-auto shrink-0 p-1 rounded-full hover:bg-black/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Progress bar -->
            <div class="absolute bottom-0 left-0 right-0 h-0.5">
                <div
                    class="h-full animate-toast-progress"
                    :class="{
                        'bg-success-400': toast.type === 'success',
                        'bg-danger-400': toast.type === 'error',
                        'bg-accent-400': toast.type === 'info',
                    }"
                ></div>
            </div>
        </div>
    </template>
</div>
