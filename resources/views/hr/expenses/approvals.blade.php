<x-layouts.hr title="Expense Approvals" currentView="expenses">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    processing: {},
    errors: {},

    async approve(claimId) {
        this.processing[claimId] = 'approving';
        this.errors[claimId] = '';

        try {
            const response = await fetch('/api/hr/expense-claims/' + claimId + '/approve', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (!response.ok) {
                this.errors[claimId] = data.message || 'Failed to approve';
                return;
            }

            // Remove card from the UI
            const card = document.getElementById('claim-' + claimId);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';
                setTimeout(() => card.remove(), 300);
            }
        } catch (err) {
            this.errors[claimId] = 'Network error';
        } finally {
            delete this.processing[claimId];
        }
    },

    rejectData: { claimId: null, reason: '' },
    showRejectModal: false,

    openReject(claimId) {
        this.rejectData = { claimId, reason: '' };
        this.showRejectModal = true;
    },

    async confirmReject() {
        const { claimId, reason } = this.rejectData;
        this.processing[claimId] = 'rejecting';

        try {
            const response = await fetch('/api/hr/expense-claims/' + claimId + '/reject', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ reason }),
            });

            const data = await response.json();

            if (!response.ok) {
                this.errors[claimId] = data.message || 'Failed to reject';
                return;
            }

            this.showRejectModal = false;

            const card = document.getElementById('claim-' + claimId);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';
                setTimeout(() => card.remove(), 300);
            }
        } catch (err) {
            this.errors[claimId] = 'Network error';
        } finally {
            delete this.processing[claimId];
        }
    },
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Pending Approvals</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Expense claims awaiting your review</p>
        </div>
        <span class="text-[12px] font-semibold text-amber-400/70 bg-amber-500/10 px-3 py-1.5 rounded-full border border-amber-500/20">
            {{ $pendingClaims->count() }} pending
        </span>
    </div>

    {{-- Claims List --}}
    @forelse($pendingClaims as $claim)
        @php
            $empName = $claim->employeeProfile->user->name ?? 'Unknown';
            $initials = strtoupper(collect(explode(' ', $empName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
            $itemCount = $claim->items->count();
        @endphp
        <div id="claim-{{ $claim->id }}"
             class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden hover:border-white/[0.12] transition-all duration-300"
             style="transition: opacity 0.3s, transform 0.3s;">

            <div class="px-6 py-5">
                <div class="flex items-start justify-between gap-4">
                    {{-- Employee & Claim Info --}}
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-11 h-11 rounded-xl bg-cyan-500/15 text-cyan-400 text-[13px] font-bold flex items-center justify-center shrink-0">
                            {{ $initials }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="text-[15px] font-semibold text-white/85">{{ $claim->title }}</h3>
                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold text-amber-400 bg-amber-500/10 rounded-full border border-amber-500/20 uppercase tracking-wider">
                                    Submitted
                                </span>
                            </div>
                            <p class="text-[13px] text-white/50 mt-0.5">{{ $empName }}</p>
                            <div class="flex items-center gap-4 mt-3">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="text-[13px] font-bold text-white/80 tabular-nums">{{ number_format($claim->total_amount, 2) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span class="text-[12px] text-white/40">{{ $itemCount }} {{ Str::plural('item', $itemCount) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-[12px] text-white/40 tabular-nums">{{ $claim->submitted_at ? $claim->submitted_at->format('M d, Y') : $claim->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('hr.expenses.show', $claim) }}"
                           class="px-3 py-2 text-[12px] font-medium text-white/40 hover:text-white/60 bg-white/[0.04] hover:bg-white/[0.06] border border-white/[0.06] rounded-lg transition-colors">
                            View
                        </a>
                        <button @click="approve({{ $claim->id }})"
                                :disabled="processing[{{ $claim->id }}]"
                                class="px-4 py-2 text-[12px] font-semibold text-white bg-green-500/80 hover:bg-green-500/90 disabled:opacity-40 rounded-lg transition-colors flex items-center gap-1.5">
                            <template x-if="processing[{{ $claim->id }}] === 'approving'">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </template>
                            <svg x-show="processing[{{ $claim->id }}] !== 'approving'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve
                        </button>
                        <button @click="openReject({{ $claim->id }})"
                                :disabled="processing[{{ $claim->id }}]"
                                class="px-4 py-2 text-[12px] font-semibold text-red-400 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 disabled:opacity-40 rounded-lg transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject
                        </button>
                    </div>
                </div>

                {{-- Error --}}
                <template x-if="errors[{{ $claim->id }}]">
                    <p class="text-[12px] text-red-400 mt-3" x-text="errors[{{ $claim->id }}]"></p>
                </template>
            </div>

            {{-- Expense Items Preview --}}
            @if($claim->items->count())
                <div class="px-6 py-3 border-t border-white/[0.04] bg-white/[0.01]">
                    <div class="flex items-center gap-4 overflow-x-auto">
                        @foreach($claim->items->take(4) as $item)
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-white/15 shrink-0"></span>
                                <span class="text-[11px] text-white/35">{{ Str::limit($item->description, 25) }}</span>
                                <span class="text-[11px] font-semibold text-white/50 tabular-nums">{{ number_format($item->amount, 2) }}</span>
                            </div>
                        @endforeach
                        @if($claim->items->count() > 4)
                            <span class="text-[11px] text-white/25 shrink-0">+{{ $claim->items->count() - 4 }} more</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @empty
        {{-- Empty State --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl px-8 py-20 text-center">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-green-500/5 flex items-center justify-center mb-5">
                    <svg class="w-8 h-8 text-green-400/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-[16px] font-medium text-white/50 mb-1">All caught up!</p>
                <p class="text-[13px] text-white/30">No expense claims are pending your approval</p>
            </div>
        </div>
    @endforelse

    {{-- Reject Modal --}}
    <template x-if="showRejectModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showRejectModal = false">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showRejectModal = false"></div>
            <div class="relative bg-[#17172A] border border-white/[0.10] rounded-2xl w-full max-w-md shadow-2xl" @click.stop>
                <div class="px-6 py-5 border-b border-white/[0.06]">
                    <h3 class="text-[16px] font-semibold text-white/85">Reject Expense Claim</h3>
                    <p class="text-[13px] text-white/40 mt-0.5">Please provide a reason for rejection</p>
                </div>
                <div class="px-6 py-5">
                    <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Rejection Reason</label>
                    <textarea x-model="rejectData.reason" rows="4" placeholder="Enter reason for rejecting this claim..."
                              class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:border-red-500/40 focus:ring-1 focus:ring-red-500/20 transition-colors resize-none"></textarea>
                </div>
                <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-end gap-3">
                    <button @click="showRejectModal = false"
                            class="px-4 py-2.5 text-[13px] font-medium text-white/45 hover:text-white/65 bg-white/[0.04] hover:bg-white/[0.06] rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button @click="confirmReject()" :disabled="!rejectData.reason.trim() || processing[rejectData.claimId]"
                            class="px-5 py-2.5 text-[13px] font-semibold text-white bg-red-500/80 hover:bg-red-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors">
                        Reject Claim
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

</x-layouts.hr>
