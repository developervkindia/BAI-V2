{{-- Impersonation indicator — fixed bottom-right corner, icon-only with expand on hover --}}
@if(session('super_admin_impersonating'))
<div x-data="{ expanded: false }"
     @mouseenter="expanded = true"
     @mouseleave="expanded = false"
     class="fixed bottom-4 right-4 z-[200] flex items-center gap-2">

    {{-- Pill: icon always visible, text on hover --}}
    <div class="flex items-center gap-2 bg-red-950/90 backdrop-blur-md border border-red-500/30 rounded-full shadow-lg shadow-red-900/20 transition-all duration-200"
         :class="expanded ? 'pl-3 pr-1.5 py-1.5' : 'p-2'">

        {{-- Lock icon --}}
        <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>

        {{-- Expanded content --}}
        <template x-if="expanded">
            <div class="flex items-center gap-2 animate-fade-in">
                <span class="text-[11px] text-red-300/80 font-medium whitespace-nowrap">
                    as <strong class="text-red-200">{{ auth()->user()->name }}</strong>
                </span>
                <form method="POST" action="{{ route('super-admin.stop-impersonating') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-semibold bg-red-500/20 text-red-300 hover:bg-red-500/30 hover:text-red-200 transition-colors whitespace-nowrap">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Stop
                    </button>
                </form>
            </div>
        </template>
    </div>
</div>
@endif
