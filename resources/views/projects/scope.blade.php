<x-layouts.smartprojects :project="$project" currentView="scope" :canEdit="$canEdit">
<div class="max-w-4xl mx-auto px-4 py-4" x-data="scopeManager()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-sm font-semibold text-white/70">Scope Changes</h2>
            <p class="text-xs text-white/30 mt-0.5">Track additions, reductions, timeline and budget changes</p>
        </div>
        <button @click="showCreate = true"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Log Change
        </button>
    </div>

    @php
        $pending  = $changes->get('pending',  collect());
        $approved = $changes->get('approved', collect());
        $rejected = $changes->get('rejected', collect());
        $typeConfig = [
            'addition'  => ['label' => 'Addition',  'class' => 'bg-green-500/15 text-green-400'],
            'reduction' => ['label' => 'Reduction', 'class' => 'bg-red-500/15 text-red-400'],
            'timeline'  => ['label' => 'Timeline',  'class' => 'bg-blue-500/15 text-blue-400'],
            'budget'    => ['label' => 'Budget',    'class' => 'bg-amber-500/15 text-amber-400'],
        ];
    @endphp

    @if($pending->isEmpty() && $approved->isEmpty() && $rejected->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-10 h-10 rounded-2xl bg-white/[0.04] flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <p class="text-sm text-white/30">No scope changes logged yet</p>
            <button @click="showCreate = true" class="text-xs text-orange-400/70 hover:text-orange-400 transition-colors mt-2">+ Log first change</button>
        </div>
    @else

        {{-- Pending --}}
        @if($pending->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-[10px] font-semibold text-amber-400/60 uppercase tracking-widest mb-2">Pending ({{ $pending->count() }})</h3>
            <div class="space-y-2">
                @foreach($pending as $change)
                @include('projects._scope_change_card', ['change' => $change, 'typeConfig' => $typeConfig, 'canEdit' => $canEdit])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Approved --}}
        @if($approved->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-[10px] font-semibold text-green-400/60 uppercase tracking-widest mb-2">Approved ({{ $approved->count() }})</h3>
            <div class="space-y-2">
                @foreach($approved as $change)
                @include('projects._scope_change_card', ['change' => $change, 'typeConfig' => $typeConfig, 'canEdit' => $canEdit])
                @endforeach
            </div>
        </div>
        @endif

        {{-- Rejected --}}
        @if($rejected->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-[10px] font-semibold text-red-400/50 uppercase tracking-widest mb-2">Rejected ({{ $rejected->count() }})</h3>
            <div class="space-y-2 opacity-60">
                @foreach($rejected as $change)
                @include('projects._scope_change_card', ['change' => $change, 'typeConfig' => $typeConfig, 'canEdit' => $canEdit])
                @endforeach
            </div>
        </div>
        @endif

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
                <h2 class="text-[15px] font-bold text-white/85">Log Scope Change</h2>
                <button @click="showCreate = false" class="w-7 h-7 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/40 hover:text-white/70 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Title <span class="text-red-400/80">*</span></label>
                    <input type="text" x-model="form.title" placeholder="Brief title of the change"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Type</label>
                        <select x-model="form.type" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none appearance-none">
                            <option value="addition">Addition</option>
                            <option value="reduction">Reduction</option>
                            <option value="timeline">Timeline</option>
                            <option value="budget">Budget</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Cost Impact ($)</label>
                        <input type="number" x-model="form.cost_impact" placeholder="0.00" min="0" step="0.01"
                               class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none placeholder-white/18"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Days Impact</label>
                        <input type="number" x-model="form.days_impact" placeholder="0"
                               class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none placeholder-white/18"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Description <span class="text-red-400/80">*</span></label>
                    <textarea x-model="form.description" rows="3" placeholder="Describe the scope change in detail…"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18 resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" @click="showCreate = false"
                        class="flex-1 py-2.5 rounded-xl border border-white/[0.1] text-white/40 text-[13px] hover:border-white/20 hover:text-white/60 transition-colors">
                    Cancel
                </button>
                <button type="button" @click="createChange()"
                        class="flex-1 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white text-[13px] font-semibold transition-colors">
                    Save Change
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function scopeManager() {
    return {
        showCreate: false,
        form: { title: '', type: 'addition', description: '', cost_impact: '', days_impact: '' },
        init() {},
        async createChange() {
            const body = { ...this.form };
            if (!body.cost_impact) delete body.cost_impact;
            if (!body.days_impact) delete body.days_impact;
            const res = await fetch('/api/projects/{{ $project->slug }}/scope-changes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(body),
            });
            if (res.ok) location.reload();
        },
        async updateStatus(id, status) {
            await fetch('/api/project-scope-changes/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ status }),
            });
            location.reload();
        },
        deleteChange(id) {
            this.$dispatch('confirm-modal', {
                title: 'Delete Scope Change',
                message: 'Delete this scope change? This cannot be undone.',
                confirmLabel: 'Delete',
                variant: 'danger',
                onConfirm: async () => {
                    await fetch('/api/project-scope-changes/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    location.reload();
                }
            });
        },
    };
}
</script>
</x-layouts.smartprojects>
