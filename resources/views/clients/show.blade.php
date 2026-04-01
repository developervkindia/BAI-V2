<x-layouts.smartprojects currentView="clients">
<div class="max-w-5xl mx-auto px-4 py-6" x-data="clientShow()">

    {{-- Header --}}
    <div class="flex items-start gap-4 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-orange-500/15 text-orange-400 text-xl font-bold flex items-center justify-center shrink-0">
            {{ strtoupper(substr($client->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-[18px] font-bold text-white/88">{{ $client->name }}</h1>
                @if($client->company)
                    <span class="px-2 py-0.5 rounded-full bg-white/[0.06] text-white/40 text-[11px]">{{ $client->company }}</span>
                @endif
            </div>
            <div class="flex items-center gap-4 mt-1.5 flex-wrap">
                @if($client->email)
                    <a href="mailto:{{ $client->email }}" class="flex items-center gap-1 text-[12px] text-white/38 hover:text-orange-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $client->email }}
                    </a>
                @endif
                @if($client->phone)
                    <span class="flex items-center gap-1 text-[12px] text-white/38">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $client->phone }}
                    </span>
                @endif
            </div>
        </div>
        <button @click="showEdit = true"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/45 hover:text-white/70 text-xs font-medium transition-colors border border-white/[0.08]">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Edit
        </button>
    </div>

    @if($client->notes)
        <div class="bg-white/[0.03] border border-white/[0.07] rounded-xl px-4 py-3 mb-6 text-[13px] text-white/50 leading-relaxed">
            {{ $client->notes }}
        </div>
    @endif

    {{-- Projects --}}
    <div class="mb-8">
        <h3 class="text-[11px] font-semibold text-white/28 uppercase tracking-widest mb-3">Projects ({{ $client->projects->count() }})</h3>
        @if($client->projects->isEmpty())
            <p class="text-sm text-white/25 py-4">No projects linked to this client yet.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($client->projects as $proj)
                <a href="{{ route('projects.overview', $proj) }}"
                   class="group bg-white/[0.03] hover:bg-white/[0.06] border border-white/[0.07] hover:border-white/[0.12] rounded-xl p-3.5 transition-all flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/20"
                          style="background: {{ $proj->color ?? '#F97316' }};"></span>
                    <span class="text-[13px] font-medium text-white/72 group-hover:text-white/88 truncate transition-colors">{{ $proj->name }}</span>
                </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Billing History --}}
    @if($billingWeeks->isNotEmpty())
    <div>
        <h3 class="text-[11px] font-semibold text-white/28 uppercase tracking-widest mb-3">Locked Billing Weeks</h3>
        <div class="space-y-2">
            @foreach($billingWeeks as $week)
            <div class="bg-white/[0.03] border border-white/[0.07] rounded-xl px-4 py-3 flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] text-white/60">
                        <span class="font-medium text-white/75">{{ $week->project->name }}</span>
                        <span class="text-white/30 mx-1.5">·</span>
                        {{ \Carbon\Carbon::parse($week->week_start)->format('M d') }} – {{ \Carbon\Carbon::parse($week->week_end)->format('M d, Y') }}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-[13px] font-semibold text-orange-400">${{ number_format($week->total_amount, 2) }}</p>
                    <p class="text-[10px] text-white/28">{{ number_format($week->total_billable_hours, 1) }} hrs</p>
                </div>
                <svg class="w-3.5 h-3.5 text-green-400/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Edit Modal --}}
    <div x-show="showEdit" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="showEdit = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="relative bg-[#16162A] border border-white/[0.12] rounded-2xl w-full max-w-md p-6 shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-[15px] font-bold text-white/85">Edit Client</h2>
                <button @click="showEdit = false" class="w-7 h-7 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/40 hover:text-white/70 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Name <span class="text-red-400/80">*</span></label>
                    <input type="text" name="name" required value="{{ $client->name }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Company</label>
                    <input type="text" name="company" value="{{ $client->company }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ $client->email }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ $client->phone }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none resize-none">{{ $client->notes }}</textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showEdit = false"
                            class="flex-1 py-2.5 rounded-xl border border-white/[0.1] text-white/40 text-[13px] hover:border-white/20 hover:text-white/60 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white text-[13px] font-semibold transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>

            {{-- Delete --}}
            <form method="POST" action="{{ route('clients.destroy', $client) }}" class="mt-3"
                  x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Delete Client', message: 'Delete this client? Projects will be unlinked.', confirmLabel: 'Delete', variant: 'danger', onConfirm: () => $el.submit() })">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-2 rounded-xl text-red-400/70 hover:text-red-400 hover:bg-red-500/10 text-[12px] transition-colors">
                    Delete Client
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function clientShow() {
    return { showEdit: false };
}
</script>
</x-layouts.smartprojects>
