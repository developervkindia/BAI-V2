<x-layouts.hr title="Announcements" currentView="announcements">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    deleting: null,

    async togglePin(id) {
        try {
            const res = await fetch('/api/hr/announcements/' + id + '/pin', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) throw new Error('Failed to toggle pin');
            window.location.reload();
        } catch (e) {
            alert(e.message);
        }
    },

    deleteAnnouncement(id) {
        this.$dispatch('confirm-modal', {
            title: 'Delete Announcement',
            message: 'Are you sure you want to delete this announcement? This action cannot be undone.',
            confirmLabel: 'Delete',
            variant: 'danger',
            onConfirm: async () => {
                this.deleting = id;
                try {
                    const res = await fetch('/api/hr/announcements/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to delete announcement');
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                } finally {
                    this.deleting = null;
                }
            }
        });
    }
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Announcements</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Company-wide announcements and updates</p>
        </div>
        <a href="{{ route('hr.announcements.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Announcement
        </a>
    </div>

    @php
        $pinned = $announcements->getCollection()->where('is_pinned', true);
        $unpinned = $announcements->getCollection()->where('is_pinned', false);
        $typeColors = [
            'general' => 'text-blue-400 bg-blue-500/10',
            'policy' => 'text-purple-400 bg-purple-500/10',
            'event' => 'text-emerald-400 bg-emerald-500/10',
            'holiday' => 'text-amber-400 bg-amber-500/10',
            'urgent' => 'text-red-400 bg-red-500/10',
        ];
    @endphp

    {{-- Pinned Announcements --}}
    @if($pinned->count() > 0)
        <div class="space-y-3">
            <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                Pinned
            </h2>
            @foreach($pinned as $ann)
                @php $tc = $typeColors[$ann->type] ?? 'text-white/50 bg-white/[0.06]'; @endphp
                <div class="bg-[#17172A] border border-cyan-500/15 rounded-xl overflow-hidden hover:border-cyan-500/25 transition-colors">
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                                    <span class="text-[10px] font-semibold {{ $tc }} px-2 py-0.5 rounded-full uppercase">{{ $ann->type }}</span>
                                </div>
                                <a href="{{ route('hr.announcements.show', $ann) }}" class="block group">
                                    <h3 class="text-[15px] font-semibold text-white/85 group-hover:text-white transition-colors">{{ $ann->title }}</h3>
                                    <p class="text-[13px] text-white/45 mt-1.5 line-clamp-2">{{ Str::limit(strip_tags($ann->body), 200) }}</p>
                                </a>
                                <div class="flex items-center gap-3 mt-3 text-[11px] text-white/30">
                                    <span>{{ $ann->creator->name ?? 'System' }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $ann->published_at ? \Carbon\Carbon::parse($ann->published_at)->diffForHumans() : 'Draft' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button @click="togglePin({{ $ann->id }})" title="Unpin"
                                        class="p-1.5 rounded-lg hover:bg-white/[0.06] text-cyan-400 hover:text-cyan-300 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                                </button>
                                <button @click="deleteAnnouncement({{ $ann->id }})" :disabled="deleting === {{ $ann->id }}" title="Delete"
                                        class="p-1.5 rounded-lg hover:bg-red-500/10 text-white/25 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- All Announcements --}}
    <div class="space-y-3">
        @if($pinned->count() > 0)
            <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest">All Announcements</h2>
        @endif

        @forelse($unpinned as $ann)
            @php $tc = $typeColors[$ann->type] ?? 'text-white/50 bg-white/[0.06]'; @endphp
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden hover:border-white/[0.12] hover:bg-[#1D1D35] transition-all">
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                <span class="text-[10px] font-semibold {{ $tc }} px-2 py-0.5 rounded-full uppercase">{{ $ann->type }}</span>
                            </div>
                            <a href="{{ route('hr.announcements.show', $ann) }}" class="block group">
                                <h3 class="text-[15px] font-semibold text-white/85 group-hover:text-white transition-colors">{{ $ann->title }}</h3>
                                <p class="text-[13px] text-white/45 mt-1.5 line-clamp-2">{{ Str::limit(strip_tags($ann->body), 200) }}</p>
                            </a>
                            <div class="flex items-center gap-3 mt-3 text-[11px] text-white/30">
                                <span>{{ $ann->creator->name ?? 'System' }}</span>
                                <span>&middot;</span>
                                <span>{{ $ann->published_at ? \Carbon\Carbon::parse($ann->published_at)->diffForHumans() : 'Draft' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <button @click="togglePin({{ $ann->id }})" title="Pin"
                                    class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/20 hover:text-white/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                            </button>
                            <button @click="deleteAnnouncement({{ $ann->id }})" :disabled="deleting === {{ $ann->id }}" title="Delete"
                                    class="p-1.5 rounded-lg hover:bg-red-500/10 text-white/20 hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            @if($pinned->count() === 0)
                <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-16 text-center">
                    <svg class="w-12 h-12 text-white/10 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    <p class="text-[15px] text-white/35 font-medium">No announcements yet</p>
                    <p class="text-[12px] text-white/20 mt-1 mb-4">Create your first announcement to share with the team</p>
                    <a href="{{ route('hr.announcements.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Announcement
                    </a>
                </div>
            @endif
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($announcements->hasPages())
        <div class="flex justify-center pt-2">
            {{ $announcements->links() }}
        </div>
    @endif

</div>

</x-layouts.hr>