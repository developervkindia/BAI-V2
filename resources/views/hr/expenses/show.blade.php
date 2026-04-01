<x-layouts.hr title="Expense Claim Details" currentView="expenses">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    status: '{{ $expenseClaim->status }}',
    submitting: false,
    showRejectModal: false,
    rejectReason: '',
    actionError: '',

    async performAction(action, body = {}) {
        this.submitting = true;
        this.actionError = '';

        try {
            const response = await fetch('/api/hr/expense-claims/{{ $expenseClaim->id }}/' + action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(body),
            });

            const data = await response.json();

            if (!response.ok) {
                this.actionError = data.message || 'Action failed';
                return;
            }

            this.status = data.status || action.replace('ed', '');
            window.location.reload();
        } catch (err) {
            this.actionError = 'Network error. Please try again.';
        } finally {
            this.submitting = false;
        }
    },

    submitClaim()  { this.performAction('submit'); },
    approveClaim() { this.performAction('approve'); },
    rejectClaim()  { this.performAction('reject', { reason: this.rejectReason }); this.showRejectModal = false; },
    reimburseClaim() { this.performAction('reimburse'); },
}">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('hr.expenses.index') }}"
           class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] flex items-center justify-center transition-colors">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-[22px] font-bold text-white/85 tracking-tight">{{ $expenseClaim->title }}</h1>
                @php
                    $statusColors = [
                        'draft' => 'text-white/50 bg-white/[0.06] border-white/[0.08]',
                        'submitted' => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
                        'approved' => 'text-green-400 bg-green-500/10 border-green-500/20',
                        'rejected' => 'text-red-400 bg-red-500/10 border-red-500/20',
                        'reimbursed' => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
                    ];
                    $sc = $statusColors[$expenseClaim->status] ?? $statusColors['draft'];
                @endphp
                <span class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $sc }}">
                    {{ $expenseClaim->status }}
                </span>
            </div>
            <p class="text-[13px] text-white/40 mt-0.5">Claim #{{ $expenseClaim->id }}</p>
        </div>
    </div>

    {{-- Action Error --}}
    <template x-if="actionError">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-3.5">
            <p class="text-[13px] text-red-400" x-text="actionError"></p>
        </div>
    </template>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Total Amount</p>
            <p class="text-[26px] font-bold text-white/85 leading-tight mt-1 tabular-nums">{{ number_format($expenseClaim->total_amount, 2) }}</p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Employee</p>
            <p class="text-[15px] font-semibold text-white/75 mt-2">{{ $expenseClaim->employeeProfile->user->name ?? 'Unknown' }}</p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Submitted</p>
            <p class="text-[15px] font-semibold text-white/75 mt-2 tabular-nums">{{ $expenseClaim->submitted_at ? $expenseClaim->submitted_at->format('M d, Y') : 'Not submitted' }}</p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Items</p>
            <p class="text-[15px] font-semibold text-white/75 mt-2">{{ $expenseClaim->items->count() }} line items</p>
        </div>
    </div>

    {{-- Rejection Reason --}}
    @if($expenseClaim->status === 'rejected' && $expenseClaim->rejection_reason)
        <div class="bg-red-500/5 border border-red-500/15 rounded-xl px-5 py-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-[12px] font-semibold text-red-400 uppercase tracking-wider">Rejection Reason</p>
                    <p class="text-[13px] text-white/60 mt-1">{{ $expenseClaim->rejection_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Line Items Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06]">
            <h2 class="text-[14px] font-semibold text-white/85">Line Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">#</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Category</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Description</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Amount</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Date</th>
                        <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @foreach($expenseClaim->items as $idx => $item)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/30 font-medium">{{ $idx + 1 }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[13px] text-white/65 font-medium">{{ $item->category->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[13px] text-white/60">{{ $item->description }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-[13px] font-semibold text-white/80 tabular-nums">{{ number_format($item->amount, 2) }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/45 tabular-nums">{{ $item->expense_date ? $item->expense_date->format('M d, Y') : '---' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($item->receipt_path)
                                    <a href="{{ Storage::url($item->receipt_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-[11px] text-cyan-400/70 hover:text-cyan-400 font-medium transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        View
                                    </a>
                                @else
                                    <span class="text-[11px] text-white/20">---</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-white/[0.08] bg-white/[0.02]">
                        <td colspan="3" class="px-5 py-4">
                            <span class="text-[13px] font-semibold text-white/65">Total</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="text-[16px] font-bold text-white/90 tabular-nums">{{ number_format($expenseClaim->total_amount, 2) }}</span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Approver Info --}}
    @if($expenseClaim->approver)
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-green-500/15 text-green-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($expenseClaim->approver->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-[13px] font-medium text-white/70">{{ $expenseClaim->status === 'approved' || $expenseClaim->status === 'reimbursed' ? 'Approved' : 'Reviewed' }} by {{ $expenseClaim->approver->name }}</p>
                    @if($expenseClaim->approved_at)
                        <p class="text-[11px] text-white/35">{{ $expenseClaim->approved_at->format('M d, Y \a\t h:i A') }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex items-center justify-end gap-3">
        @if($expenseClaim->status === 'draft')
            <button @click="submitClaim()" :disabled="submitting"
                    class="px-6 py-2.5 text-[13px] font-semibold text-white bg-cyan-500/80 hover:bg-cyan-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2">
                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                Submit for Approval
            </button>
        @endif

        @if($expenseClaim->status === 'submitted')
            <button @click="approveClaim()" :disabled="submitting"
                    class="px-5 py-2.5 text-[13px] font-semibold text-white bg-green-500/80 hover:bg-green-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Approve
            </button>
            <button @click="showRejectModal = true"
                    class="px-5 py-2.5 text-[13px] font-semibold text-red-400 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reject
            </button>
        @endif

        @if($expenseClaim->status === 'approved')
            <button @click="reimburseClaim()" :disabled="submitting"
                    class="px-5 py-2.5 text-[13px] font-semibold text-white bg-emerald-500/80 hover:bg-emerald-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Mark Reimbursed
            </button>
        @endif
    </div>

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
                    <textarea x-model="rejectReason" rows="4" placeholder="Enter reason for rejecting this claim..."
                              class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:border-red-500/40 focus:ring-1 focus:ring-red-500/20 transition-colors resize-none"></textarea>
                </div>
                <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-end gap-3">
                    <button @click="showRejectModal = false"
                            class="px-4 py-2.5 text-[13px] font-medium text-white/45 hover:text-white/65 bg-white/[0.04] hover:bg-white/[0.06] rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button @click="rejectClaim()" :disabled="submitting || !rejectReason.trim()"
                            class="px-5 py-2.5 text-[13px] font-semibold text-white bg-red-500/80 hover:bg-red-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors">
                        Reject Claim
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

</x-layouts.hr>
