<x-layouts.smartprojects :project="$project" currentView="billing" :canEdit="$canEdit">
<div class="max-w-5xl mx-auto px-4 py-4" x-data="billingManager()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div>
            <h2 class="text-sm font-semibold text-white/70">Billing</h2>
            <p class="text-xs text-white/30 mt-0.5">
                Hourly rate: <span class="text-orange-400 font-medium">${{ number_format($project->hourly_rate ?? 0, 2) }}/hr</span>
                @if($project->client)
                    · <span class="text-white/35">{{ $project->client->name }}</span>
                @endif
            </p>
        </div>
        @if($canEdit)
        <div class="flex items-center gap-2 flex-wrap">
            <input type="date" x-model="weekStart" class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/65 text-[12px] focus:outline-none"/>
            <span class="text-white/25 text-xs">to</span>
            <input type="date" x-model="weekEnd" class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/65 text-[12px] focus:outline-none"/>
            <button @click="loadWeek()" :disabled="loading"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20 disabled:opacity-50">
                <svg class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Load Week
            </button>
        </div>
        @endif
    </div>

    {{-- Active week panel --}}
    <div x-show="activeWeek" x-cloak class="mb-6 space-y-3">

        {{-- Week header bar --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-xl px-4 py-3 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 text-orange-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-[13px] font-semibold text-white/78" x-text="activeWeek ? formatDateRange(activeWeek.week_start, activeWeek.week_end) : ''"></span>
                <template x-if="activeWeek && activeWeek.locked_at">
                    <span class="flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-500/15 text-green-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Locked
                    </span>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <template x-if="activeWeek && activeWeek.locked_at">
                    <a :href="'/projects/{{ $project->slug }}/billing/' + activeWeek.id + '/invoice'"
                       target="_blank"
                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-500/15 text-blue-400 hover:bg-blue-500/25 text-xs font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        View Invoice
                    </a>
                </template>
                <template x-if="activeWeek && !activeWeek.locked_at && {{ $canEdit ? 'true' : 'false' }}">
                    <button @click="lockWeek()"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-500/15 text-green-400 hover:bg-green-500/25 text-xs font-medium transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Lock & Generate Invoice
                    </button>
                </template>
            </div>
        </div>

        {{-- Entries: per-member with time log detail --}}
        <template x-if="activeWeek && activeWeek.entries && activeWeek.entries.length > 0">
            <div class="space-y-3">
                <template x-for="entry in activeWeek.entries" :key="entry.id">
                    <div class="bg-[#111120] border border-white/[0.07] rounded-xl overflow-hidden">
                        {{-- Member row --}}
                        <div class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-white/[0.02]"
                             @click="toggleMember(entry.user_id)">
                            <div class="w-8 h-8 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-bold flex items-center justify-center shrink-0"
                                 x-text="entry.user ? entry.user.name.slice(0,2).toUpperCase() : '?'"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-semibold text-white/80" x-text="entry.user ? entry.user.name : 'Unknown'"></p>
                                <p class="text-[11px] text-white/30 mt-0.5">
                                    <span x-text="parseFloat(entry.actual_hours).toFixed(1)"></span> hrs logged
                                </p>
                            </div>

                            {{-- Billable hrs (editable) --}}
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="text-right">
                                    <p class="text-[10px] text-white/28 mb-1">Billable Hrs</p>
                                    <template x-if="!activeWeek.locked_at && {{ $canEdit ? 'true' : 'false' }}">
                                        <input type="number" x-model.number="entry.billable_hours" min="0" step="0.5"
                                               @click.stop
                                               @change="updateEntry(entry)"
                                               class="w-20 px-2 py-1 rounded-lg bg-white/[0.07] border border-white/[0.1] text-white/82 text-right text-[13px] font-semibold focus:outline-none focus:ring-1 focus:ring-orange-500/40"/>
                                    </template>
                                    <template x-if="activeWeek.locked_at || !{{ $canEdit ? 'true' : 'false' }}">
                                        <span class="text-[13px] font-semibold text-white/82" x-text="parseFloat(entry.billable_hours).toFixed(1)"></span>
                                    </template>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-white/28 mb-1">Amount</p>
                                    <p class="text-[14px] font-bold text-orange-400"
                                       x-text="'$' + (parseFloat(entry.billable_hours) * {{ $project->hourly_rate ?? 0 }}).toFixed(2)"></p>
                                </div>
                                <svg class="w-4 h-4 text-white/20 transition-transform"
                                     :class="openMembers[entry.user_id] ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>

                        {{-- Notes row --}}
                        <template x-if="!activeWeek.locked_at && {{ $canEdit ? 'true' : 'false' }}">
                            <div class="px-4 pb-2 border-t border-white/[0.04]" x-show="openMembers[entry.user_id]">
                                <label class="block text-[10px] text-white/25 uppercase tracking-wider mt-2 mb-1">Admin Notes</label>
                                <input type="text" x-model="entry.notes" placeholder="Optional billing notes…"
                                       @change="updateEntry(entry)"
                                       class="w-full px-3 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.07] text-white/55 text-[12px] focus:outline-none focus:ring-1 focus:ring-orange-500/30 placeholder-white/18"/>
                            </div>
                        </template>

                        {{-- Time log detail rows --}}
                        <div x-show="openMembers[entry.user_id]" class="border-t border-white/[0.06]">
                            <template x-if="getLogsForUser(entry.user_id).length > 0">
                                <div>
                                    <div class="grid grid-cols-12 px-4 py-2 text-[10px] font-semibold text-white/22 uppercase tracking-wider bg-white/[0.02]">
                                        <div class="col-span-2">Date</div>
                                        <div class="col-span-5">Task</div>
                                        <div class="col-span-2 text-right">Hours</div>
                                        <div class="col-span-3 pl-3">Notes</div>
                                    </div>
                                    <template x-for="log in getLogsForUser(entry.user_id)" :key="log.id">
                                        <div class="grid grid-cols-12 px-4 py-2.5 text-[12px] border-t border-white/[0.04] hover:bg-white/[0.02] items-center">
                                            <div class="col-span-2 text-white/40" x-text="formatDay(log.logged_at)"></div>
                                            <div class="col-span-4 text-white/65 truncate pr-3" x-text="log.task ? log.task.title : '—'"></div>
                                            <div class="col-span-2 text-right">
                                                <template x-if="!activeWeek.locked_at && {{ $canEdit ? 'true' : 'false' }}">
                                                    <input type="number" :value="parseFloat(log.hours).toFixed(2)" min="0" step="0.25"
                                                           @change="updateTimeLog(log, parseFloat($event.target.value), log.notes)"
                                                           class="w-16 px-1.5 py-0.5 rounded-md bg-white/[0.06] border border-white/[0.1] text-white/75 text-right text-[12px] font-medium focus:outline-none focus:ring-1 focus:ring-orange-500/40"/>
                                                </template>
                                                <template x-if="activeWeek.locked_at || !{{ $canEdit ? 'true' : 'false' }}">
                                                    <span class="font-medium text-white/70" x-text="parseFloat(log.hours).toFixed(1) + ' h'"></span>
                                                </template>
                                            </div>
                                            <div class="col-span-2 pl-2">
                                                <template x-if="!activeWeek.locked_at && {{ $canEdit ? 'true' : 'false' }}">
                                                    <input type="text" :value="log.notes || ''" placeholder="—"
                                                           @change="updateTimeLog(log, log.hours, $event.target.value)"
                                                           class="w-full px-1.5 py-0.5 rounded-md bg-white/[0.04] border border-white/[0.07] text-white/45 text-[11px] focus:outline-none focus:ring-1 focus:ring-orange-500/30 placeholder-white/15"/>
                                                </template>
                                                <template x-if="activeWeek.locked_at || !{{ $canEdit ? 'true' : 'false' }}">
                                                    <span class="text-white/32 truncate" x-text="log.notes || '—'"></span>
                                                </template>
                                            </div>
                                            <div class="col-span-2 text-right">
                                                <template x-if="!activeWeek.locked_at && {{ $canEdit ? 'true' : 'false' }}">
                                                    <button @click="deleteTimeLog(log, entry)"
                                                            class="text-red-400/40 hover:text-red-400 transition-colors p-1" title="Delete log">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="grid grid-cols-12 px-4 py-2 border-t border-white/[0.06] bg-white/[0.02]">
                                        <div class="col-span-7 text-[11px] font-semibold text-white/35">Logged total</div>
                                        <div class="col-span-2 text-right text-[12px] font-bold text-white/60"
                                             x-text="getLogsForUser(entry.user_id).reduce((s,l) => s + parseFloat(l.hours), 0).toFixed(1) + ' h'"></div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="getLogsForUser(entry.user_id).length === 0">
                                <p class="px-4 py-3 text-[12px] text-white/25">No time logs found for this period.</p>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Grand total row --}}
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 flex items-center justify-between">
                    <div class="text-[12px] text-white/45">
                        Total billable:
                        <span class="font-semibold text-white/70" x-text="parseFloat(activeWeek.total_billable_hours || 0).toFixed(1) + ' hrs'"></span>
                    </div>
                    <div class="text-[18px] font-bold text-orange-400"
                         x-text="'$' + parseFloat(activeWeek.total_amount || 0).toFixed(2)"></div>
                </div>
            </div>
        </template>

        <template x-if="activeWeek && (!activeWeek.entries || activeWeek.entries.length === 0)">
            <div class="bg-[#111120] border border-white/[0.07] rounded-xl px-4 py-8 text-center text-[12px] text-white/28">
                No time logged this week for any team members.
            </div>
        </template>
    </div>

    {{-- Past billing weeks --}}
    @if($billingWeeks->isNotEmpty())
    <div>
        <h3 class="text-[10px] font-semibold text-white/25 uppercase tracking-widest mb-3">Past Billing Weeks</h3>
        <div class="space-y-2">
            @foreach($billingWeeks as $week)
            <div class="bg-white/[0.02] border border-white/[0.05] rounded-xl px-4 py-3 flex items-center gap-4 hover:bg-white/[0.04] transition-colors group">
                <div class="flex-1 cursor-pointer" @click="loadExistingWeek({{ $week->id }})">
                    <p class="text-[12px] text-white/60">
                        {{ \Carbon\Carbon::parse($week->week_start)->format('M d') }} – {{ \Carbon\Carbon::parse($week->week_end)->format('M d, Y') }}
                    </p>
                    <p class="text-[11px] text-white/30 mt-0.5">{{ $week->entries->count() }} member{{ $week->entries->count() === 1 ? '' : 's' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-[13px] font-semibold text-orange-400">${{ number_format($week->total_amount, 2) }}</p>
                    <p class="text-[10px] text-white/28">{{ number_format($week->total_billable_hours, 1) }} hrs</p>
                </div>
                @if($week->locked_at)
                    <a href="{{ route('projects.billing.invoice', [$project, $week]) }}" target="_blank"
                       class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-500/10 text-blue-400/70 hover:bg-blue-500/20 hover:text-blue-400 text-[11px] font-medium transition-colors opacity-0 group-hover:opacity-100 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Invoice
                    </a>
                    <svg class="w-3.5 h-3.5 text-green-400/60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script>
function billingManager() {
    return {
        loading: false,
        weekStart: '',
        weekEnd: '',
        activeWeek: null,
        timeLogs: [],
        openMembers: {},
        hourlyRate: {{ $project->hourly_rate ?? 0 }},

        init() {
            const now = new Date();
            const day = now.getDay();
            const mon = new Date(now); mon.setDate(now.getDate() - ((day + 6) % 7));
            const sun = new Date(mon); sun.setDate(mon.getDate() + 6);
            this.weekStart = mon.toISOString().slice(0,10);
            this.weekEnd   = sun.toISOString().slice(0,10);
        },

        formatDateRange(start, end) {
            const clean = d => String(d).slice(0, 10);
            const fmt = d => { const dt = new Date(clean(d) + 'T00:00:00'); return isNaN(dt) ? clean(d) : dt.toLocaleDateString('en-US', {month:'short', day:'numeric'}); };
            const fmtY = d => { const dt = new Date(clean(d) + 'T00:00:00'); return isNaN(dt) ? clean(d) : dt.toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}); };
            return fmt(start) + ' – ' + fmtY(end);
        },

        formatDay(dateStr) {
            if (!dateStr) return '—';
            // Handle both "2026-03-25" and "2026-03-25T00:00:00.000000Z" formats
            const clean = String(dateStr).slice(0, 10);
            const d = new Date(clean + 'T00:00:00');
            if (isNaN(d.getTime())) return String(dateStr).slice(0, 10);
            return d.toLocaleDateString('en-US', {month:'short', day:'numeric'});
        },

        toggleMember(userId) {
            this.openMembers[userId] = !this.openMembers[userId];
        },

        getLogsForUser(userId) {
            return this.timeLogs.filter(l => l.user_id == userId);
        },

        async loadWeek() {
            this.loading = true;
            const res = await fetch('/api/projects/{{ $project->slug }}/billing-weeks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ week_start: this.weekStart, week_end: this.weekEnd }),
            });
            this.loading = false;
            if (res.ok) {
                const data = await res.json();
                this.activeWeek = data.week;
                this.timeLogs   = data.time_logs || [];
                this.openMembers = {};
                // Auto-open all members
                (data.week.entries || []).forEach(e => { this.openMembers[e.user_id] = true; });
            }
        },

        async loadExistingWeek(weekId) {
            const res = await fetch('/api/project-billing-weeks/' + weekId + '/logs', {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            if (res.ok) {
                const data = await res.json();
                this.activeWeek = data.week;
                this.timeLogs   = data.time_logs || [];
                this.weekStart  = data.week.week_start;
                this.weekEnd    = data.week.week_end;
                this.openMembers = {};
                (data.week.entries || []).forEach(e => { this.openMembers[e.user_id] = true; });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        async updateEntry(entry) {
            const res = await fetch('/api/project-billing-entries/' + entry.id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ billable_hours: entry.billable_hours, notes: entry.notes }),
            });
            if (res.ok) {
                const data = await res.json();
                this.activeWeek = { ...this.activeWeek, ...data.week };
                // Sync entry data
                const idx = this.activeWeek.entries.findIndex(e => e.id === entry.id);
                if (idx !== -1) this.activeWeek.entries[idx] = { ...this.activeWeek.entries[idx], ...data.entry };
            }
        },

        recalcTotals() {
            if (!this.activeWeek || !this.activeWeek.entries) return;
            let totalBillable = 0;
            for (const entry of this.activeWeek.entries) {
                const userLogs = this.timeLogs.filter(l => l.user_id == entry.user_id);
                const actualHrs = userLogs.reduce((s, l) => s + parseFloat(l.hours || 0), 0);
                entry.actual_hours = parseFloat(actualHrs.toFixed(2));
                entry.billable_hours = parseFloat(actualHrs.toFixed(2));
                totalBillable += entry.billable_hours;
            }
            this.activeWeek.total_actual_hours = parseFloat(totalBillable.toFixed(2));
            this.activeWeek.total_billable_hours = parseFloat(totalBillable.toFixed(2));
            this.activeWeek.total_amount = parseFloat((totalBillable * this.hourlyRate).toFixed(2));
        },

        async updateTimeLog(log, newHours, newNotes) {
            const res = await fetch('/api/project-time-logs/' + log.id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ hours: newHours, notes: newNotes }),
            });
            if (res.ok) {
                const idx = this.timeLogs.findIndex(l => l.id === log.id);
                if (idx !== -1) {
                    this.timeLogs[idx].hours = newHours;
                    this.timeLogs[idx].notes = newNotes;
                }
                this.recalcTotals();
            }
        },

        deleteTimeLog(log, entry) {
            this.$dispatch('confirm-modal', {
                title: 'Delete Time Log',
                message: 'Delete this time log entry? This cannot be undone.',
                confirmLabel: 'Delete',
                variant: 'danger',
                onConfirm: async () => {
                    const res = await fetch('/api/project-time-logs/' + log.id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    if (res.ok) {
                        this.timeLogs = this.timeLogs.filter(l => l.id !== log.id);
                        this.recalcTotals();
                    }
                }
            });
        },

        lockWeek() {
            this.$dispatch('confirm-modal', {
                title: 'Lock Billing Week',
                message: 'Lock this billing week? No further edits will be allowed.',
                confirmLabel: 'Lock',
                variant: 'danger',
                onConfirm: async () => {
                    const res = await fetch('/api/project-billing-weeks/' + this.activeWeek.id + '/lock', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.activeWeek = data.week;
                    }
                }
            });
        },
    };
}
</script>
</x-layouts.smartprojects>
