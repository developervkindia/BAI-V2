<div class="bg-white/[0.03] border border-white/[0.07] rounded-xl px-4 py-3 flex items-start gap-3">
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $typeConfig[$change->type]['class'] ?? 'bg-white/10 text-white/50' }}">
                {{ $typeConfig[$change->type]['label'] ?? $change->type }}
            </span>
            <span class="text-[13px] font-medium text-white/78">{{ $change->title }}</span>
        </div>
        <p class="text-[12px] text-white/42 mt-1 leading-relaxed">{{ $change->description }}</p>
        <div class="flex items-center gap-3 mt-2 flex-wrap">
            @if($change->cost_impact)
                <span class="text-[11px] text-white/35">
                    <span class="text-white/22">Cost:</span> ${{ number_format($change->cost_impact, 2) }}
                </span>
            @endif
            @if($change->days_impact)
                <span class="text-[11px] text-white/35">
                    <span class="text-white/22">Days:</span> {{ $change->days_impact > 0 ? '+' : '' }}{{ $change->days_impact }}
                </span>
            @endif
            @if($change->requester)
                <span class="text-[11px] text-white/25">by {{ $change->requester->name }}</span>
            @endif
        </div>
    </div>
    @if($canEdit && $change->status === 'pending')
    <div class="flex items-center gap-1.5 shrink-0">
        <button @click="updateStatus({{ $change->id }}, 'approved')"
                class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-green-500/15 text-green-400 hover:bg-green-500/25 transition-colors">
            Approve
        </button>
        <button @click="updateStatus({{ $change->id }}, 'rejected')"
                class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-red-500/15 text-red-400 hover:bg-red-500/25 transition-colors">
            Reject
        </button>
        <button @click="deleteChange({{ $change->id }})"
                class="w-6 h-6 rounded-lg hover:bg-white/[0.06] text-white/22 hover:text-white/45 flex items-center justify-center transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </div>
    @endif
</div>
