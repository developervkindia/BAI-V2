<x-layouts.smartprojects currentView="clients">
<div class="max-w-5xl mx-auto px-4 py-6" x-data="clientsManager()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-sm font-semibold text-white/70">Clients</h2>
            <p class="text-xs text-white/30 mt-0.5">{{ $clients->count() }} client{{ $clients->count() === 1 ? '' : 's' }}</p>
        </div>
        <button @click="showCreate = true"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Client
        </button>
    </div>

    {{-- Grid --}}
    @if($clients->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-12 h-12 rounded-2xl bg-white/[0.04] flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-sm text-white/30 mb-1">No clients yet</p>
            <p class="text-xs text-white/18 mb-4">Add your first client to link them with projects.</p>
            <button @click="showCreate = true" class="text-xs text-orange-400/70 hover:text-orange-400 transition-colors">+ Create a client</button>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($clients as $client)
            <a href="{{ route('clients.show', $client) }}"
               class="group bg-white/[0.03] hover:bg-white/[0.06] border border-white/[0.07] hover:border-white/[0.12] rounded-xl p-4 transition-all">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-500/15 text-orange-400 text-sm font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($client->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-semibold text-white/82 truncate group-hover:text-white transition-colors">{{ $client->name }}</p>
                        @if($client->company)
                            <p class="text-[11px] text-white/38 truncate mt-0.5">{{ $client->company }}</p>
                        @endif
                        @if($client->email)
                            <p class="text-[11px] text-white/28 truncate mt-0.5">{{ $client->email }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-white/[0.06] flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-white/22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span class="text-[11px] text-white/32">{{ $client->projects_count }} project{{ $client->projects_count === 1 ? '' : 's' }}</span>
                </div>
            </a>
            @endforeach
        </div>
    @endif

    {{-- Create Modal --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="showCreate = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="relative bg-[#16162A] border border-white/[0.12] rounded-2xl w-full max-w-md p-6 shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-[15px] font-bold text-white/85">New Client</h2>
                <button @click="showCreate = false" class="w-7 h-7 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/40 hover:text-white/70 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('clients.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Name <span class="text-red-400/80">*</span></label>
                    <input type="text" name="name" required autofocus placeholder="e.g. John Smith"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Company</label>
                    <input type="text" name="company" placeholder="e.g. Acme Corp"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" placeholder="hello@example.com"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Phone</label>
                        <input type="text" name="phone" placeholder="+1 555 000 0000"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Any notes about this client…"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18 resize-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showCreate = false"
                            class="flex-1 py-2.5 rounded-xl border border-white/[0.1] text-white/40 text-[13px] hover:border-white/20 hover:text-white/60 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white text-[13px] font-semibold transition-colors">
                        Create Client
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function clientsManager() {
    return {
        showCreate: {{ session('showCreate') ? 'true' : 'false' }},
    };
}
</script>
</x-layouts.smartprojects>
