<x-layouts.hr title="My Expenses" currentView="expenses">

<div class="p-5 lg:p-7 space-y-6" x-data="{}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">My Expense Claims</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Track your personal expense reimbursements</p>
        </div>
        <a href="{{ route('hr.expenses.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-400 text-[13px] font-semibold rounded-lg border border-cyan-500/20 hover:border-cyan-500/30 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Claim
        </a>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalClaimed = $claims->sum('total_amount');
        $pendingAmount = $claims->whereIn('status', ['draft', 'submitted'])->sum('total_amount');
        $approvedAmount = $claims->where('status', 'approved')->sum('total_amount');
        $reimbursedAmount = $claims->where('status', 'reimbursed')->sum('total_amount');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Total Claimed</p>
                    <p class="text-[28px] font-bold text-white/85 leading-tight mt-1 tabular-nums">{{ number_format($totalClaimed, 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center shrink-0 group-hover:bg-cyan-500/15 transition-colors">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Pending</p>
                    <p class="text-[28px] font-bold text-amber-400/90 leading-tight mt-1 tabular-nums">{{ number_format($pendingAmount, 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center shrink-0 group-hover:bg-amber-500/15 transition-colors">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Approved</p>
                    <p class="text-[28px] font-bold text-green-400/90 leading-tight mt-1 tabular-nums">{{ number_format($approvedAmount, 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center shrink-0 group-hover:bg-green-500/15 transition-colors">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Reimbursed</p>
                    <p class="text-[28px] font-bold text-emerald-400/90 leading-tight mt-1 tabular-nums">{{ number_format($reimbursedAmount, 2) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0 group-hover:bg-emerald-500/15 transition-colors">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Claims Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">All Claims</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Your submitted expense claims</p>
            </div>
            <span class="text-[11px] font-semibold text-cyan-400/70 bg-cyan-500/10 px-2.5 py-1 rounded-full">{{ $claims->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Title</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Amount</th>
                        <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Status</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($claims as $claim)
                        <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer"
                            onclick="window.location='{{ route('hr.expenses.show', $claim) }}'">
                            <td class="px-5 py-4">
                                <p class="text-[13px] font-medium text-white/80">{{ $claim->title }}</p>
                                <p class="text-[11px] text-white/30 mt-0.5">{{ $claim->items_count ?? $claim->items->count() ?? 0 }} items</p>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-[13px] font-semibold text-white/80 tabular-nums">{{ number_format($claim->total_amount, 2) }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'draft' => 'text-white/50 bg-white/[0.06] border-white/[0.08]',
                                        'submitted' => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
                                        'approved' => 'text-green-400 bg-green-500/10 border-green-500/20',
                                        'rejected' => 'text-red-400 bg-red-500/10 border-red-500/20',
                                        'reimbursed' => 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
                                    ];
                                    $sc = $statusColors[$claim->status] ?? $statusColors['draft'];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $sc }}">
                                    {{ $claim->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-[12px] text-white/40 tabular-nums">{{ $claim->created_at->format('M d, Y') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-white/[0.04] flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-[14px] font-medium text-white/40 mb-1">No expense claims yet</p>
                                    <p class="text-[12px] text-white/25 mb-4">Submit your first expense for reimbursement</p>
                                    <a href="{{ route('hr.expenses.create') }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-cyan-500/15 text-cyan-400 text-[12px] font-semibold rounded-lg border border-cyan-500/20 hover:bg-cyan-500/25 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        New Claim
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($claims->hasPages())
            <div class="px-5 py-4 border-t border-white/[0.06]">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] text-white/30">
                        Showing {{ $claims->firstItem() }} to {{ $claims->lastItem() }} of {{ $claims->total() }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        @if($claims->onFirstPage())
                            <span class="px-3 py-1.5 text-[12px] text-white/20 rounded-lg cursor-not-allowed">Previous</span>
                        @else
                            <a href="{{ $claims->previousPageUrl() }}" class="px-3 py-1.5 text-[12px] text-white/50 hover:text-white/70 hover:bg-white/[0.04] rounded-lg transition-colors">Previous</a>
                        @endif
                        @foreach($claims->getUrlRange(max(1, $claims->currentPage() - 2), min($claims->lastPage(), $claims->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                               class="w-8 h-8 flex items-center justify-center text-[12px] rounded-lg transition-colors {{ $page == $claims->currentPage() ? 'bg-cyan-500/15 text-cyan-400 font-semibold' : 'text-white/40 hover:text-white/60 hover:bg-white/[0.04]' }}">
                                {{ $page }}
                            </a>
                        @endforeach
                        @if($claims->hasMorePages())
                            <a href="{{ $claims->nextPageUrl() }}" class="px-3 py-1.5 text-[12px] text-white/50 hover:text-white/70 hover:bg-white/[0.04] rounded-lg transition-colors">Next</a>
                        @else
                            <span class="px-3 py-1.5 text-[12px] text-white/20 rounded-lg cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

</x-layouts.hr>
