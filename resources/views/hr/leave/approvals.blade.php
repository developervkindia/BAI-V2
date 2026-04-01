<x-layouts.hr title="Leave Approvals" currentView="leave-approvals">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    csrf: document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
    requests: @js($pendingRequests->map(fn($r) => [
        'id' => $r->id,
        'employee_name' => $r->employeeProfile?->user?->name ?? 'Unknown',
        'employee_initials' => strtoupper(collect(explode(' ', $r->employeeProfile?->user?->name ?? 'U'))->map(fn($w) => substr($w, 0, 1))->take(2)->join('')),
        'type_name' => $r->leaveType->name ?? 'Leave',
        'type_code' => $r->leaveType->code ?? '',
        'type_color' => $r->leaveType->color ?? '#06b6d4',
        'start_date' => $r->start_date?->format('M d, Y'),
        'end_date' => $r->end_date?->format('M d, Y'),
        'start_raw' => $r->start_date?->format('Y-m-d'),
        'end_raw' => $r->end_date?->format('Y-m-d'),
        'days' => $r->days,
        'reason' => $r->reason,
        'is_half_day' => $r->is_half_day,
        'created_at' => $r->created_at?->format('M d, Y h:i A'),
    ])),
    processing: null,
    showRejectModal: false,
    rejectingId: null,
    rejectReason: '',
    actionResults: {},

    formatDate(dateStr) {
        if (!dateStr) return '—';
        return dateStr;
    },

    approveRequest(id) {
        this.$dispatch('confirm-modal', {
            title: 'Approve Leave Request',
            message: 'Approve this leave request?',
            confirmLabel: 'Approve',
            variant: 'info',
            onConfirm: async () => {
                this.processing = id;

                try {
                    const res = await fetch(`/api/hr/leave-requests/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                        },
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        alert(data.message || 'Failed to approve request.');
                        return;
                    }

                    this.actionResults[id] = 'approved';
                    setTimeout(() => {
                        this.requests = this.requests.filter(r => r.id !== id);
                        delete this.actionResults[id];
                    }, 2000);

                } catch (err) {
                    alert('Network error. Please try again.');
                } finally {
                    this.processing = null;
                }
            }
        });
    },

    openRejectModal(id) {
        this.rejectingId = id;
        this.rejectReason = '';
        this.showRejectModal = true;
    },

    closeRejectModal() {
        this.showRejectModal = false;
        this.rejectingId = null;
        this.rejectReason = '';
    },

    async submitReject() {
        if (!this.rejectReason.trim()) {
            alert('Please provide a reason for rejection.');
            return;
        }

        const id = this.rejectingId;
        this.processing = id;

        try {
            const res = await fetch(`/api/hr/leave-requests/${id}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({
                    rejection_reason: this.rejectReason.trim(),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.message || 'Failed to reject request.');
                return;
            }

            this.closeRejectModal();
            this.actionResults[id] = 'rejected';

            // Remove from list after animation
            setTimeout(() => {
                this.requests = this.requests.filter(r => r.id !== id);
                delete this.actionResults[id];
            }, 2000);

        } catch (err) {
            alert('Network error. Please try again.');
        } finally {
            this.processing = null;
        }
    },
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Leave Approvals</h1>
            <p class="text-[13px] text-white/40 mt-0.5">
                <span x-text="requests.length"></span> pending request<span x-show="requests.length !== 1">s</span> awaiting your review
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-semibold text-amber-400/70 bg-amber-500/10 px-3 py-1.5 rounded-full flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                <span x-text="requests.length"></span> Pending
            </span>
        </div>
    </div>

    {{-- Pending Requests --}}
    <div class="space-y-4">
        <template x-for="req in requests" :key="req.id">
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden hover:border-white/[0.12] transition-all duration-200"
                 :class="{
                     'border-emerald-500/20 bg-emerald-500/[0.03]': actionResults[req.id] === 'approved',
                     'border-red-500/20 bg-red-500/[0.03]': actionResults[req.id] === 'rejected',
                 }">

                {{-- Action Result Banner --}}
                <div x-show="actionResults[req.id]" x-transition class="px-5 py-2 border-b border-white/[0.06]"
                     :class="actionResults[req.id] === 'approved' ? 'bg-emerald-500/10' : 'bg-red-500/10'">
                    <div class="flex items-center gap-2">
                        <svg x-show="actionResults[req.id] === 'approved'" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="actionResults[req.id] === 'rejected'" class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span class="text-[12px] font-semibold capitalize"
                              :class="actionResults[req.id] === 'approved' ? 'text-emerald-400' : 'text-red-400'"
                              x-text="'Request ' + actionResults[req.id]"></span>
                    </div>
                </div>

                <div class="p-5">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <div class="w-11 h-11 rounded-full flex items-center justify-center text-[13px] font-bold shrink-0"
                             :style="'background-color:' + req.type_color + '20; color:' + req.type_color"
                             x-text="req.employee_initials"></div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div>
                                    <h3 class="text-[15px] font-semibold text-white/85" x-text="req.employee_name"></h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-2 h-2 rounded-full" :style="'background-color:' + req.type_color"></div>
                                            <span class="text-[12px] text-white/55 font-medium" x-text="req.type_name"></span>
                                        </div>
                                        <span class="text-white/15">|</span>
                                        <span class="text-[12px] text-white/40 font-mono" x-text="req.type_code"></span>
                                    </div>
                                </div>

                                {{-- Days Badge --}}
                                <div class="text-center shrink-0">
                                    <div class="text-[22px] font-bold text-white/80 tabular-nums leading-tight" x-text="req.days"></div>
                                    <div class="text-[10px] text-white/30 font-medium">day<span x-show="req.days !== 1">s</span></div>
                                </div>
                            </div>

                            {{-- Date Range --}}
                            <div class="mt-3 flex items-center gap-3 flex-wrap">
                                <div class="flex items-center gap-2 bg-white/[0.04] rounded-lg px-3 py-2">
                                    <svg class="w-4 h-4 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-[12px] text-white/60 font-medium" x-text="req.start_date"></span>
                                    <template x-if="req.start_date !== req.end_date">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-3 h-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <span class="text-[12px] text-white/60 font-medium" x-text="req.end_date"></span>
                                        </span>
                                    </template>
                                    <template x-if="req.is_half_day">
                                        <span class="text-[9px] font-bold text-violet-400/80 bg-violet-500/10 px-1.5 py-0.5 rounded ml-1">HALF DAY</span>
                                    </template>
                                </div>

                                <span class="text-[11px] text-white/25">Applied <span x-text="req.created_at"></span></span>
                            </div>

                            {{-- Reason --}}
                            <div x-show="req.reason" class="mt-3 p-3 bg-white/[0.03] rounded-lg border border-white/[0.04]">
                                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-wider mb-1">Reason</p>
                                <p class="text-[13px] text-white/55 leading-relaxed" x-text="req.reason"></p>
                            </div>

                            {{-- Actions --}}
                            <div class="mt-4 flex items-center gap-2.5" x-show="!actionResults[req.id]">
                                <button @click="approveRequest(req.id)"
                                        :disabled="processing === req.id"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[12px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/25 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg x-show="processing === req.id" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <svg x-show="processing !== req.id" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Approve
                                </button>
                                <button @click="openRejectModal(req.id)"
                                        :disabled="processing === req.id"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[12px] font-semibold bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <template x-if="requests.length === 0">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-14 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-[16px] font-semibold text-white/60">All caught up!</h3>
            <p class="text-[13px] text-white/35 mt-1.5 max-w-sm mx-auto">There are no pending leave requests that need your approval right now.</p>
        </div>
    </template>

    {{-- Reject Modal --}}
    <div x-show="showRejectModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="closeRejectModal()">
        <div class="absolute inset-0 bg-black/60" @click="closeRejectModal()"></div>
        <div class="relative bg-[#1A1A2E] border border-white/[0.1] rounded-xl w-full max-w-md shadow-2xl" x-show="showRejectModal" x-transition>
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-500/15 flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-semibold text-white/85">Reject Leave Request</h3>
                </div>
                <button @click="closeRejectModal()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 uppercase tracking-wider mb-2">Reason for Rejection</label>
                    <textarea x-model="rejectReason" rows="4"
                              placeholder="Please provide a reason for rejecting this leave request..."
                              class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-red-500/40 focus:border-red-500/30 transition-colors resize-none"></textarea>
                </div>
                <div class="flex items-center gap-2.5 justify-end">
                    <button @click="closeRejectModal()"
                            class="px-4 py-2 rounded-lg border border-white/[0.08] text-[12px] font-medium text-white/50 hover:text-white/70 hover:border-white/[0.15] transition-colors">
                        Cancel
                    </button>
                    <button @click="submitReject()"
                            :disabled="processing || !rejectReason.trim()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-[12px] font-semibold bg-red-500/15 text-red-400 border border-red-500/25 hover:bg-red-500/25 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="processing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="processing ? 'Rejecting...' : 'Reject Request'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

</x-layouts.hr>
