<x-layouts.hr title="{{ $reviewCycle->name }}" currentView="perf-cycles">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    cycle: {{ Js::from($reviewCycle) }},
    reviews: {{ Js::from($reviewCycle->reviews ?? []) }},
    typeFilter: 'all',
    statusFilter: 'all',

    get filteredReviews() {
        return this.reviews.filter(r => {
            if (this.typeFilter !== 'all' && r.review_type !== this.typeFilter) return false;
            if (this.statusFilter !== 'all' && r.status !== this.statusFilter) return false;
            return true;
        });
    },

    get totalReviews() { return this.reviews.length; },

    get submittedCount() { return this.reviews.filter(r => r.status === 'submitted').length; },

    get pendingCount() { return this.reviews.filter(r => r.status === 'pending' || r.status === 'in_progress').length; },

    get avgRating() {
        const rated = this.reviews.filter(r => r.overall_rating && r.overall_rating > 0);
        if (rated.length === 0) return 0;
        return (rated.reduce((sum, r) => sum + parseFloat(r.overall_rating), 0) / rated.length).toFixed(1);
    },

    get completionPct() {
        if (this.totalReviews === 0) return 0;
        return Math.round((this.submittedCount / this.totalReviews) * 100);
    },

    statusColor(status) {
        const colors = {
            'pending': 'text-orange-400 bg-orange-500/10',
            'in_progress': 'text-amber-400 bg-amber-500/10',
            'submitted': 'text-emerald-400 bg-emerald-500/10',
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

    typeColor(type) {
        const colors = {
            'self': 'text-cyan-400 bg-cyan-500/10',
            'manager': 'text-blue-400 bg-blue-500/10',
            'peer': 'text-violet-400 bg-violet-500/10',
        };
        return colors[type] || 'text-white/45 bg-white/[0.06]';
    },

    employeeName(review) {
        if (review.employee_profile && review.employee_profile.user) {
            return review.employee_profile.user.name;
        }
        if (review.employee_profile) {
            return review.employee_profile.user?.name || 'Employee';
        }
        return 'Unknown';
    },

    employeeInitials(review) {
        const name = this.employeeName(review);
        return name.split(' ').map(w => w.charAt(0)).slice(0, 2).join('').toUpperCase();
    },

    formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },

    formatDateShort(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    },

    renderStars(rating) {
        const r = Math.round(rating || 0);
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            stars += i <= r ? '&#9733;' : '&#9734;';
        }
        return stars;
    }
}">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-[12px]">
        <a href="{{ route('hr.performance.index') }}" class="text-white/35 hover:text-white/60 transition-colors">Performance</a>
        <svg class="w-3 h-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('hr.performance.cycles') }}" class="text-white/35 hover:text-white/60 transition-colors">Cycles</a>
        <svg class="w-3 h-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white/55 font-medium" x-text="cycle.name"></span>
    </div>

    {{-- Cycle Header --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-6">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-[22px] font-bold text-white/85 tracking-tight" x-text="cycle.name"></h1>
                    <span class="text-[11px] font-semibold capitalize px-2.5 py-0.5 rounded-full" :class="cycleStatusColor(cycle.status)" x-text="cycle.status.replace('_', ' ')"></span>
                </div>
                <div class="flex items-center flex-wrap gap-x-5 gap-y-1.5">
                    <div class="flex items-center gap-1.5 text-[12px] text-white/40">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span class="capitalize" x-text="cycle.type.replace('_', ' ')"></span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[12px] text-white/40">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="formatDate(cycle.start_date) + ' — ' + formatDate(cycle.end_date)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Total Reviews</p>
            <p class="text-[28px] font-bold text-white/85 leading-tight mt-1" x-text="totalReviews"></p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Submitted</p>
            <p class="text-[28px] font-bold text-emerald-400/90 leading-tight mt-1" x-text="submittedCount"></p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Pending</p>
            <p class="text-[28px] font-bold text-amber-400/90 leading-tight mt-1" x-text="pendingCount"></p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Avg Rating</p>
            <div class="flex items-baseline gap-1.5 mt-1">
                <p class="text-[28px] font-bold text-cyan-400/90 leading-tight" x-text="avgRating || '—'"></p>
                <span class="text-[12px] text-white/25">/5</span>
            </div>
        </div>
    </div>

    {{-- Completion Progress --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[13px] font-medium text-white/65">Completion Progress</span>
            <span class="text-[13px] font-bold tabular-nums" :class="completionPct >= 100 ? 'text-emerald-400' : completionPct >= 50 ? 'text-cyan-400' : 'text-amber-400'" x-text="completionPct + '%'"></span>
        </div>
        <div class="h-2.5 bg-white/[0.06] rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-700 ease-out"
                 :class="completionPct >= 100 ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : completionPct >= 50 ? 'bg-gradient-to-r from-cyan-500 to-cyan-400' : 'bg-gradient-to-r from-amber-500 to-amber-400'"
                 :style="'width: ' + completionPct + '%'"></div>
        </div>
        <div class="flex items-center justify-between mt-1.5">
            <span class="text-[11px] text-white/25" x-text="submittedCount + ' of ' + totalReviews + ' reviews completed'"></span>
            <span class="text-[11px] text-white/25" x-text="pendingCount + ' remaining'"></span>
        </div>
    </div>

    {{-- Filters & Reviews Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">

        {{-- Table Header / Filters --}}
        <div class="px-5 py-4 border-b border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-[14px] font-semibold text-white/85">Reviews</h2>
            <div class="flex items-center gap-3">
                {{-- Type Filter --}}
                <div class="flex items-center gap-1">
                    <span class="text-[11px] text-white/25 mr-1">Type:</span>
                    <template x-for="t in ['all', 'self', 'manager', 'peer']" :key="t">
                        <button @click="typeFilter = t"
                                :class="typeFilter === t ? 'prod-bg-muted prod-text' : 'text-white/35 hover:text-white/55 hover:bg-white/[0.04]'"
                                class="px-2 py-0.5 rounded text-[11px] font-medium capitalize transition-colors"
                                x-text="t">
                        </button>
                    </template>
                </div>
                {{-- Status Filter --}}
                <div class="flex items-center gap-1">
                    <span class="text-[11px] text-white/25 mr-1">Status:</span>
                    <template x-for="s in ['all', 'pending', 'in_progress', 'submitted']" :key="s">
                        <button @click="statusFilter = s"
                                :class="statusFilter === s ? 'prod-bg-muted prod-text' : 'text-white/35 hover:text-white/55 hover:bg-white/[0.04]'"
                                class="px-2 py-0.5 rounded text-[11px] font-medium capitalize transition-colors"
                                x-text="s === 'all' ? 'All' : s.replace('_', ' ')">
                        </button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider px-5 py-3">Employee</th>
                        <th class="text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider px-5 py-3">Review Type</th>
                        <th class="text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider px-5 py-3">Rating</th>
                        <th class="text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider px-5 py-3">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <template x-for="review in filteredReviews" :key="review.id">
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-cyan-500/15 text-cyan-400 text-[10px] font-bold flex items-center justify-center shrink-0" x-text="employeeInitials(review)"></div>
                                    <span class="text-[13px] font-medium text-white/75" x-text="employeeName(review)"></span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[11px] font-semibold capitalize px-2 py-0.5 rounded-full" :class="typeColor(review.review_type)" x-text="review.review_type"></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <template x-if="review.overall_rating && review.overall_rating > 0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[14px] text-amber-400 tracking-wider" x-html="renderStars(review.overall_rating)"></span>
                                        <span class="text-[11px] text-white/35 font-medium" x-text="parseFloat(review.overall_rating).toFixed(1)"></span>
                                    </div>
                                </template>
                                <template x-if="!review.overall_rating || review.overall_rating === 0">
                                    <span class="text-[12px] text-white/20">Not rated</span>
                                </template>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[11px] font-semibold capitalize px-2 py-0.5 rounded-full" :class="statusColor(review.status)" x-text="review.status.replace('_', ' ')"></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/40" x-text="review.submitted_at ? formatDate(review.submitted_at) : '—'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <template x-if="filteredReviews.length === 0">
            <div class="px-5 py-12 text-center">
                <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-[13px] text-white/35">No reviews match your filters</p>
            </div>
        </template>

    </div>

</div>

</x-layouts.hr>
