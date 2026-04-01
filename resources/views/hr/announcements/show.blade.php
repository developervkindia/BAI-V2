<x-layouts.hr title="{{ $announcement->title }}" currentView="announcements">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    deleting: false,

    async togglePin() {
        try {
            const res = await fetch('/api/hr/announcements/{{ $announcement->id }}/pin', {
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

    deleteAnnouncement() {
        this.$dispatch('confirm-modal', {
            title: 'Delete Announcement',
            message: 'Are you sure you want to delete this announcement? This action cannot be undone.',
            confirmLabel: 'Delete',
            variant: 'danger',
            onConfirm: async () => {
                this.deleting = true;
                try {
                    const res = await fetch('/api/hr/announcements/{{ $announcement->id }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to delete announcement');
                    window.location.href = '{{ route('hr.announcements.index') }}';
                } catch (e) {
                    alert(e.message);
                    this.deleting = false;
                }
            }
        });
    }
}">

    {{-- Breadcrumb / Back --}}
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('hr.announcements.index') }}" class="flex items-center gap-2 text-[13px] text-white/40 hover:text-white/65 transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Announcements
        </a>
        <div class="flex items-center gap-2">
            <button @click="togglePin()"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium transition-colors {{ $announcement->is_pinned ? 'bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 hover:bg-cyan-500/20' : 'bg-white/[0.06] border border-white/[0.08] text-white/40 hover:text-white/60 hover:bg-white/[0.10]' }}">
                <svg class="w-3.5 h-3.5" fill="{{ $announcement->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                {{ $announcement->is_pinned ? 'Pinned' : 'Pin' }}
            </button>
            <button @click="deleteAnnouncement()" :disabled="deleting"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-[12px] font-medium hover:bg-red-500/20 transition-colors disabled:opacity-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span x-text="deleting ? 'Deleting...' : 'Delete'"></span>
            </button>
        </div>
    </div>

    {{-- Announcement Content --}}
    <div class="max-w-4xl mx-auto">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-white/[0.06]">
                <div class="flex items-center gap-2.5 mb-3 flex-wrap">
                    @php
                        $typeColors = [
                            'general' => 'text-blue-400 bg-blue-500/10',
                            'policy' => 'text-purple-400 bg-purple-500/10',
                            'event' => 'text-emerald-400 bg-emerald-500/10',
                            'holiday' => 'text-amber-400 bg-amber-500/10',
                            'urgent' => 'text-red-400 bg-red-500/10',
                        ];
                        $tc = $typeColors[$announcement->type] ?? 'text-white/50 bg-white/[0.06]';
                    @endphp
                    <span class="text-[10px] font-semibold {{ $tc }} px-2.5 py-0.5 rounded-full uppercase">{{ $announcement->type }}</span>
                    @if($announcement->is_pinned)
                        <span class="flex items-center gap-1 text-[10px] font-semibold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                            Pinned
                        </span>
                    @endif
                </div>

                <h1 class="text-[24px] font-bold text-white/90 tracking-tight leading-tight">{{ $announcement->title }}</h1>

                <div class="flex items-center gap-4 mt-3 text-[12px] text-white/35">
                    <div class="flex items-center gap-2">
                        @php
                            $authorName = $announcement->creator->name ?? 'System';
                            $authorInitials = strtoupper(collect(explode(' ', $authorName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                        @endphp
                        <div class="w-6 h-6 rounded-full bg-cyan-500/15 text-cyan-400 text-[9px] font-bold flex items-center justify-center">
                            {{ $authorInitials }}
                        </div>
                        <span class="text-white/50 font-medium">{{ $authorName }}</span>
                    </div>
                    <span>&middot;</span>
                    <span>{{ $announcement->published_at ? \Carbon\Carbon::parse($announcement->published_at)->format('F j, Y \a\t g:i A') : 'Not published' }}</span>
                </div>
            </div>

            {{-- Body Content --}}
            <div class="px-6 py-6">
                <div class="prose prose-invert prose-sm max-w-none text-white/65 leading-relaxed">
                    {!! nl2br(e($announcement->body)) !!}
                </div>
            </div>

            {{-- Target Departments (if any) --}}
            @php
                $targetDepts = $announcement->target_departments;
                if (is_string($targetDepts)) $targetDepts = json_decode($targetDepts, true);
            @endphp
            @if(!empty($targetDepts) && is_array($targetDepts))
                <div class="px-6 py-4 border-t border-white/[0.06]">
                    <p class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-2">Target Departments</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($targetDepts as $dept)
                            <span class="text-[11px] text-white/50 bg-white/[0.06] px-2.5 py-1 rounded-full font-medium">{{ $dept }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>

</x-layouts.hr>