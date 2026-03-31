<x-layouts.smartprojects :project="$project" currentView="updates" :canEdit="$canEdit">
<div class="max-w-3xl mx-auto px-4 py-4" x-data="updatesManager()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-sm font-semibold text-white/70">Weekly Updates</h2>
            <p class="text-xs text-white/30 mt-0.5">{{ $updates->count() }} update{{ $updates->count() === 1 ? '' : 's' }}</p>
        </div>
        @if($canEdit)
        <button @click="showCreate = true"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Update
        </button>
        @endif
    </div>

    {{-- Updates Timeline --}}
    @if($updates->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-10 h-10 rounded-2xl bg-white/[0.04] flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-sm text-white/30 mb-1">No updates yet</p>
            @if($canEdit)
                <button @click="showCreate = true" class="text-xs text-orange-400/70 hover:text-orange-400 transition-colors mt-2">+ Create first update</button>
            @endif
        </div>
    @else
        <div class="space-y-3">
            @foreach($updates as $update)
            <div class="bg-white/[0.03] border border-white/[0.07] rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-[13px] font-semibold text-white/82">{{ $update->title }}</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $update->period_type === 'biweekly' ? 'bg-purple-500/15 text-purple-400' : 'bg-blue-500/15 text-blue-400' }}">
                                {{ $update->period_type === 'biweekly' ? 'Bi-weekly' : 'Weekly' }}
                            </span>
                            @if($update->qa_approved_by)
                                <span class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-500/15 text-green-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    QA Approved
                                </span>
                            @endif
                            @if($update->shared_with_client_at)
                                <span class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-orange-500/15 text-orange-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    Shared
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-white/32 mt-1">
                            {{ \Carbon\Carbon::parse($update->week_start)->format('M d') }} – {{ \Carbon\Carbon::parse($update->week_end)->format('M d, Y') }}
                            @if($update->author)
                                · by {{ $update->author->name }}
                            @endif
                        </p>
                    </div>
                    @if($canEdit)
                    <div class="flex items-center gap-1 shrink-0" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="w-7 h-7 rounded-lg hover:bg-white/[0.07] text-white/25 hover:text-white/55 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute mt-2 w-44 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-2xl overflow-hidden py-1 z-20">
                            @if(!$update->qa_approved_by)
                            <button @click="qaApprove({{ $update->id }}); open=false"
                                    class="flex items-center gap-2 w-full px-3.5 py-2 text-[12px] text-white/55 hover:text-white/85 hover:bg-white/[0.05] transition-colors text-left">
                                QA Approve
                            </button>
                            @endif
                            @if(!$update->shared_with_client_at)
                            <button @click="shareWithClient({{ $update->id }}); open=false"
                                    class="flex items-center gap-2 w-full px-3.5 py-2 text-[12px] text-white/55 hover:text-white/85 hover:bg-white/[0.05] transition-colors text-left">
                                Share with Client
                            </button>
                            @endif
                            <button @click="deleteUpdate({{ $update->id }}); open=false"
                                    class="flex items-center gap-2 w-full px-3.5 py-2 text-[12px] text-red-400/70 hover:text-red-400 hover:bg-red-500/10 transition-colors text-left">
                                Delete
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-3 space-y-2 text-[12px] text-white/55 leading-relaxed">
                    <div>
                        <p class="text-[10px] font-semibold text-white/25 uppercase tracking-wider mb-1">Summary</p>
                        <p>{{ $update->summary }}</p>
                    </div>
                    @if($update->next_steps)
                    <div>
                        <p class="text-[10px] font-semibold text-white/25 uppercase tracking-wider mb-1">Next Steps</p>
                        <p>{{ $update->next_steps }}</p>
                    </div>
                    @endif
                    @if($update->blockers)
                    <div>
                        <p class="text-[10px] font-semibold text-orange-400/50 uppercase tracking-wider mb-1">Blockers</p>
                        <p class="text-orange-300/60">{{ $update->blockers }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Create Modal --}}
    @if($canEdit)
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="showCreate = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="relative bg-[#16162A] border border-white/[0.12] rounded-2xl w-full max-w-lg p-6 shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-[15px] font-bold text-white/85">New Update</h2>
                <button @click="showCreate = false" class="w-7 h-7 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/40 hover:text-white/70 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-3 max-h-[70vh] overflow-y-auto pr-1">
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Title <span class="text-red-400/80">*</span></label>
                    <input type="text" x-model="form.title" placeholder="e.g. Sprint 3 Update"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Period</label>
                        <select x-model="form.period_type" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none appearance-none">
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Bi-weekly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Week Start</label>
                        <input type="date" x-model="form.week_start"
                               class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Week End</label>
                        <input type="date" x-model="form.week_end"
                               class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Summary <span class="text-red-400/80">*</span></label>
                    <textarea x-model="form.summary" rows="4" placeholder="What was accomplished this week?"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Next Steps</label>
                    <textarea x-model="form.next_steps" rows="2" placeholder="What's planned for next week?"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Blockers</label>
                    <textarea x-model="form.blockers" rows="2" placeholder="Any blockers or risks?"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18 resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" @click="showCreate = false"
                        class="flex-1 py-2.5 rounded-xl border border-white/[0.1] text-white/40 text-[13px] hover:border-white/20 hover:text-white/60 transition-colors">
                    Cancel
                </button>
                <button type="button" @click="createUpdate()"
                        class="flex-1 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white text-[13px] font-semibold transition-colors">
                    Save Update
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
function updatesManager() {
    return {
        showCreate: false,
        form: {
            title: '',
            period_type: 'weekly',
            week_start: '',
            week_end: '',
            summary: '',
            next_steps: '',
            blockers: '',
        },
        init() {
            const now = new Date();
            const day = now.getDay();
            const mon = new Date(now); mon.setDate(now.getDate() - ((day + 6) % 7));
            const sun = new Date(mon); sun.setDate(mon.getDate() + 6);
            this.form.week_start = mon.toISOString().slice(0,10);
            this.form.week_end   = sun.toISOString().slice(0,10);
        },
        async createUpdate() {
            const res = await fetch('/api/projects/{{ $project->slug }}/weekly-updates', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(this.form),
            });
            if (res.ok) location.reload();
        },
        async qaApprove(id) {
            await fetch('/api/project-weekly-updates/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ qa_approve: true }),
            });
            location.reload();
        },
        async shareWithClient(id) {
            await fetch('/api/project-weekly-updates/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ share_with_client: true }),
            });
            location.reload();
        },
        async deleteUpdate(id) {
            if (!confirm('Delete this update?')) return;
            await fetch('/api/project-weekly-updates/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            location.reload();
        },
    };
}
</script>
</x-layouts.smartprojects>
