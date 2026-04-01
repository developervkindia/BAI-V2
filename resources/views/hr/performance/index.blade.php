<x-layouts.hr title="Performance" currentView="performance">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    activeCycles: {{ Js::from($activeCycles) }},
    myGoals: {{ Js::from($myGoals) }},
    myReviewStatus: {{ Js::from($myReviewStatus) }},
    employee: {{ Js::from($employee) }},
    startingReview: false,
    goalFilter: 'all',

    get filteredGoals() {
        if (this.goalFilter === 'all') return this.myGoals;
        return this.myGoals.filter(g => g.status === this.goalFilter);
    },

    goalProgress(goal) {
        if (!goal.target_value || goal.target_value === 0) return 0;
        return Math.min(Math.round((goal.current_value / goal.target_value) * 100), 100);
    },

    statusColor(status) {
        const colors = {
            'not_started': 'text-white/45 bg-white/[0.06]',
            'in_progress': 'text-amber-400 bg-amber-500/10',
            'on_track': 'text-emerald-400 bg-emerald-500/10',
            'at_risk': 'text-orange-400 bg-orange-500/10',
            'behind': 'text-red-400 bg-red-500/10',
            'completed': 'text-cyan-400 bg-cyan-500/10',
            'exceeded': 'text-violet-400 bg-violet-500/10',
        };
        return colors[status] || 'text-white/45 bg-white/[0.06]';
    },

    cycleStatusColor(status) {
        const colors = {
            'draft': 'text-white/45 bg-white/[0.06]',
            'active': 'text-emerald-400 bg-emerald-500/10',
            'self_review': 'text-amber-400 bg-amber-500/10',
            'manager_review': 'text-blue-400 bg-blue-500/10',
            'calibration': 'text-violet-400 bg-violet-500/10',
            'closed': 'text-red-400 bg-red-500/10',
        };
        return colors[status] || 'text-white/45 bg-white/[0.06]';
    },

    formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },

    async startSelfReview() {
        this.startingReview = true;
        try {
            const resp = await fetch('/api/hr/reviews/start-self-review', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                },
            });
            if (resp.ok) {
                window.location.href = '{{ route('hr.performance.my-review') }}';
            } else {
                const data = await resp.json();
                alert(data.message || 'Failed to start self review');
            }
        } catch (e) {
            alert('Network error. Please try again.');
        } finally {
            this.startingReview = false;
        }
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Performance</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Track goals, review cycles, and performance reviews</p>
        </div>
        <div class="flex items-center gap-3">
            <template x-if="myReviewStatus === 'pending'">
                <button @click="startSelfReview()"
                        :disabled="startingReview"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <span x-text="startingReview ? 'Starting...' : 'Start Self Review'"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- Overview Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Active Cycles --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Active Cycles</p>
                    <p class="text-[32px] font-bold text-white/85 leading-tight mt-1" x-text="activeCycles.length"></p>
                    <p class="text-[12px] text-white/35 mt-1">Review cycles in progress</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center shrink-0 group-hover:bg-cyan-500/15 transition-colors">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
        </div>

        {{-- My Goals --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">My Goals</p>
                    <p class="text-[32px] font-bold text-emerald-400/90 leading-tight mt-1" x-text="myGoals.length"></p>
                    <p class="text-[12px] text-white/35 mt-1">Assigned performance goals</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0 group-hover:bg-emerald-500/15 transition-colors">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
        </div>

        {{-- Review Status --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Review Status</p>
                    <p class="text-[20px] font-bold leading-tight mt-2 capitalize"
                       :class="myReviewStatus === 'submitted' ? 'text-emerald-400/90' : myReviewStatus === 'in_progress' ? 'text-amber-400/90' : myReviewStatus === 'pending' ? 'text-orange-400/90' : 'text-white/40'"
                       x-text="myReviewStatus ? myReviewStatus.replace('_', ' ') : 'No Active Review'"></p>
                    <p class="text-[12px] text-white/35 mt-1">Current review cycle</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                     :class="myReviewStatus === 'submitted' ? 'bg-emerald-500/10 group-hover:bg-emerald-500/15' : myReviewStatus === 'in_progress' ? 'bg-amber-500/10 group-hover:bg-amber-500/15' : 'bg-orange-500/10 group-hover:bg-orange-500/15'">
                    <svg class="w-5 h-5" :class="myReviewStatus === 'submitted' ? 'text-emerald-400' : myReviewStatus === 'in_progress' ? 'text-amber-400' : 'text-orange-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
            </div>
        </div>

    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Active Review Cycles --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-semibold text-white/85">Active Review Cycles</h2>
                <a href="{{ route('hr.performance.cycles') }}" class="text-[12px] prod-text font-medium hover:underline">View All</a>
            </div>

            <template x-if="activeCycles.length === 0">
                <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-8 text-center">
                    <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <p class="text-[13px] text-white/30">No active review cycles</p>
                </div>
            </template>

            <template x-for="cycle in activeCycles" :key="cycle.id">
                <a :href="'{{ url('hr/performance/cycles') }}/' + cycle.id"
                   class="block bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-[14px] font-semibold text-white/85 truncate" x-text="cycle.name"></h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[11px] font-medium capitalize px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-400" x-text="cycle.type.replace('_', ' ')"></span>
                                <span class="text-[11px] font-medium capitalize px-2 py-0.5 rounded-full" :class="cycleStatusColor(cycle.status)" x-text="cycle.status.replace('_', ' ')"></span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-white/20 group-hover:text-white/40 transition-colors shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-[12px] text-white/45">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span x-text="formatDate(cycle.start_date) + ' — ' + formatDate(cycle.end_date)"></span>
                        </div>
                        <template x-if="cycle.self_review_deadline">
                            <div class="flex items-center gap-2 text-[12px] text-white/35">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Self Review by: <span class="text-white/50" x-text="formatDate(cycle.self_review_deadline)"></span></span>
                            </div>
                        </template>
                        <template x-if="cycle.manager_review_deadline">
                            <div class="flex items-center gap-2 text-[12px] text-white/35">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Manager Review by: <span class="text-white/50" x-text="formatDate(cycle.manager_review_deadline)"></span></span>
                            </div>
                        </template>
                    </div>
                </a>
            </template>
        </div>

        {{-- My Goals --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-semibold text-white/85">My Goals</h2>
                <div class="flex items-center gap-1">
                    <template x-for="f in ['all', 'in_progress', 'completed', 'at_risk']" :key="f">
                        <button @click="goalFilter = f"
                                :class="goalFilter === f ? 'prod-bg-muted prod-text' : 'text-white/35 hover:text-white/55 hover:bg-white/[0.04]'"
                                class="px-2.5 py-1 rounded-md text-[11px] font-medium capitalize transition-colors"
                                x-text="f === 'all' ? 'All' : f.replace('_', ' ')">
                        </button>
                    </template>
                </div>
            </div>

            <template x-if="filteredGoals.length === 0">
                <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-8 text-center">
                    <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <p class="text-[13px] text-white/30">No goals found</p>
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="goal in filteredGoals" :key="goal.id">
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[13px] font-semibold text-white/80 truncate" x-text="goal.title"></h4>
                                <p class="text-[11px] text-white/35 mt-0.5 line-clamp-1" x-text="goal.description"></p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-3">
                                <template x-if="goal.weightage">
                                    <span class="text-[10px] font-semibold text-white/30 bg-white/[0.04] px-1.5 py-0.5 rounded" x-text="goal.weightage + '%'"></span>
                                </template>
                                <span class="text-[10px] font-semibold capitalize px-2 py-0.5 rounded-full" :class="statusColor(goal.status)" x-text="goal.status.replace('_', ' ')"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-[6px] bg-white/[0.06] rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500"
                                     :class="goalProgress(goal) >= 100 ? 'bg-gradient-to-r from-emerald-500/80 to-emerald-400/60' : goalProgress(goal) >= 60 ? 'bg-gradient-to-r from-cyan-500/80 to-cyan-400/60' : goalProgress(goal) >= 30 ? 'bg-gradient-to-r from-amber-500/80 to-amber-400/60' : 'bg-gradient-to-r from-red-500/80 to-red-400/60'"
                                     :style="'width: ' + goalProgress(goal) + '%'"></div>
                            </div>
                            <span class="text-[11px] font-semibold tabular-nums shrink-0"
                                  :class="goalProgress(goal) >= 100 ? 'text-emerald-400' : goalProgress(goal) >= 60 ? 'text-cyan-400' : 'text-white/50'"
                                  x-text="goalProgress(goal) + '%'"></span>
                        </div>
                        <div class="flex items-center gap-4 mt-2.5">
                            <div class="flex items-center gap-1.5 text-[11px] text-white/30">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                <span x-text="(goal.current_value || 0) + ' / ' + (goal.target_value || 0)"></span>
                            </div>
                            <template x-if="goal.goal_type">
                                <span class="text-[10px] text-white/25 capitalize" x-text="goal.goal_type.replace('_', ' ')"></span>
                            </template>
                            <template x-if="goal.due_date">
                                <div class="flex items-center gap-1 text-[11px] text-white/30">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span x-text="'Due: ' + formatDate(goal.due_date)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

</div>

</x-layouts.hr>
