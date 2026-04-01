<div
    x-data="{
        open: false,
        title: '',
        message: '',
        confirmLabel: 'OK',
        cancelLabel: 'Cancel',
        variant: 'danger',
        onConfirm: null,
        onCancel: null,
        show(e) {
            this.title = e.detail.title || 'Are you sure?';
            this.message = e.detail.message || '';
            this.confirmLabel = e.detail.confirmLabel || 'OK';
            this.cancelLabel = e.detail.cancelLabel || 'Cancel';
            this.variant = e.detail.variant || 'danger';
            this.onConfirm = e.detail.onConfirm || null;
            this.onCancel = e.detail.onCancel || null;
            this.open = true;
        },
        confirm() {
            this.open = false;
            if (this.onConfirm) this.onConfirm();
            this.reset();
        },
        cancel() {
            this.open = false;
            if (this.onCancel) this.onCancel();
            this.reset();
        },
        reset() {
            this.onConfirm = null;
            this.onCancel = null;
        },
    }"
    x-on:confirm-modal.window="show($event)"
    x-on:keydown.escape.window="if (open) cancel()"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] overflow-y-auto"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
        @click="cancel()"
    ></div>

    {{-- Panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="relative w-full max-w-sm bg-[#1a1a2e] border border-white/[0.08] rounded-2xl shadow-2xl overflow-hidden"
            @click.stop
        >
            <div class="p-5">
                {{-- Icon --}}
                <div class="mb-3.5">
                    <template x-if="variant === 'danger'">
                        <div class="w-10 h-10 rounded-xl bg-red-500/15 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                    </template>
                    <template x-if="variant === 'warning'">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </template>
                    <template x-if="variant === 'info'">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </template>
                </div>

                {{-- Title --}}
                <h3 class="text-[15px] font-semibold text-white/90 leading-snug" x-text="title"></h3>

                {{-- Message --}}
                <p class="mt-1.5 text-[13px] text-white/45 leading-relaxed" x-show="message" x-text="message"></p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2.5 px-5 pb-5">
                <button
                    @click="cancel()"
                    class="flex-1 py-2.5 rounded-xl border border-white/[0.1] text-[13px] font-medium text-white/50 hover:text-white/70 hover:border-white/20 hover:bg-white/[0.04] transition-colors"
                    x-text="cancelLabel"
                ></button>
                <button
                    @click="confirm()"
                    x-bind:class="{
                        'bg-red-500 hover:bg-red-400': variant === 'danger',
                        'bg-amber-500 hover:bg-amber-400': variant === 'warning',
                        'bg-blue-500 hover:bg-blue-400': variant === 'info',
                    }"
                    class="flex-1 py-2.5 rounded-xl text-[13px] font-semibold text-white transition-colors"
                    x-text="confirmLabel"
                ></button>
            </div>
        </div>
    </div>
</div>
