<x-layouts.hr title="Expense Claims" currentView="expenses">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    activeFilter: 'all',
    filters: ['all', 'draft', 'submitted', 'approved', 'rejected', 'reimbursed'],
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Expense Claims</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Manage and track all expense reimbursement claims</p>
        </div>
        <a href="{{ route('hr.expenses.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-400 text-[13px] font-semibold rounded-lg border border-cyan-500/20 hover:border-cyan-500/30 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Claim
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-1 bg-[#17172A] border border-white/[0.07] rounded-xl p-1.5 overflow-x-auto">
        <template x-for="filter in filters" :key="filter">
            <a :href="'{{ route('hr.expenses.index') }}' + (filter !== 'all' ? '?status=' + filter : '')"
               :class="filter === '{{ request('status', 'all') }}'
                   ? 'bg-cyan-500/15 text-cyan-400 border-cyan-500/20'
                   : 'text-white/45 hover:text-white/65 hover:bg-white/[0.04] border-transparent'"
               class="px-4 py-2 text-[12px] font-semibold rounded-lg border capitalize transition-all duration-200 whitespace-nowrap"
               x-text="filter">
            </a>
        </template>
    </div>

    {{-- Claims Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3.5 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Title</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Employee</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Amount</th>
                        <th class="px-5 py-3.5 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Status</th>
                        <th class="px-5 py-3.5 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Date</th>
                        <th class="px-5 py-3.5 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($claims as $claim)
                        <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer group"
                            onclick="window.location='{{ route('hr.expenses.show', $claim) }}'">
                            <td class="px-5 py-4">
                                <p class="text-[13px] font-medium text-white/80 group-hover:text-white/90 transition-colors">{{ $claim->title }}</p>
                            </td>
                            <td class="px-5 py-4">
                                @php
                                    $empName = $claim->employeeProfile->user->name ?? 'Unknown';
                                    $initials = strtoupper(collect(explode(' ', $empName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                                @endphp
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-cyan-500/15 text-cyan-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <span class="text-[13px] text-white/65">{{ $empName }}</span>
                                </div>
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
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('hr.expenses.show', $claim) }}"
                                   class="inline-flex items-center gap-1 text-[12px] font-medium text-cyan-400/70 hover:text-cyan-400 transition-colors"
                                   onclick="event.stopPropagation()">
                                    <span>View</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-white/[0.04] flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                    </div>
                                    <p class="text-[14px] font-medium text-white/40 mb-1">No expense claims found</p>
                                    <p class="text-[12px] text-white/25">Create your first expense claim to get started</p>
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
                        Showing {{ $claims->firstItem() }} to {{ $claims->lastItem() }} of {{ $claims->total() }} claims
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
