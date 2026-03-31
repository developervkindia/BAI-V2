<x-layouts.super-admin title="Audit Log">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Audit Log</h1>
            <p class="text-sm text-white/40 mt-0.5">Track all administrative actions on the platform</p>
        </div>
    </div>

    {{-- Audit Log List --}}
    <div class="sa-card overflow-hidden">
        <div class="divide-y divide-white/[0.04]">
            @forelse($auditLogs ?? [] as $log)
            <div class="px-5 py-4 hover:bg-white/[0.02] transition-colors">
                <div class="flex items-start gap-4">
                    {{-- Timeline dot --}}
                    <div class="mt-1.5 shrink-0">
                        <div class="w-2.5 h-2.5 rounded-full
                            @if(str_contains($log->action ?? '', 'delete') || str_contains($log->action ?? '', 'deactivate'))
                                bg-red-500
                            @elseif(str_contains($log->action ?? '', 'create') || str_contains($log->action ?? '', 'activate'))
                                bg-green-500
                            @elseif(str_contains($log->action ?? '', 'impersonat'))
                                bg-yellow-500
                            @else
                                bg-blue-500
                            @endif
                        "></div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm text-white/80">
                                    <span class="font-medium text-white">{{ $log->admin->name ?? $log->admin_name ?? 'System' }}</span>
                                    <span class="text-white/50">{{ $log->description ?? $log->action ?? 'performed an action' }}</span>
                                </p>
                                @if($log->target_type ?? $log->target ?? null)
                                <p class="text-xs text-white/30 mt-1">
                                    Target:
                                    <span class="text-white/40">
                                        {{ $log->target_type ?? '' }}
                                        @if($log->target_id ?? null) #{{ $log->target_id }} @endif
                                        @if($log->target_name ?? null) ({{ $log->target_name }}) @endif
                                    </span>
                                </p>
                                @endif
                                @if($log->metadata ?? $log->details ?? null)
                                <p class="text-xs text-white/20 mt-0.5 font-mono">
                                    {{ is_array($log->metadata ?? $log->details ?? null) ? json_encode($log->metadata ?? $log->details) : ($log->metadata ?? $log->details ?? '') }}
                                </p>
                                @endif
                            </div>
                            <span class="text-[11px] text-white/25 whitespace-nowrap shrink-0">
                                {{ ($log->created_at ?? now())->format('M d, Y \a\t g:i A') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-5 py-16 text-center">
                <svg class="w-12 h-12 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <p class="text-white/30 text-sm">No audit log entries yet</p>
                <p class="text-white/15 text-xs mt-1">Admin actions will appear here</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if(method_exists($auditLogs ?? collect(), 'links'))
        <div class="px-5 py-4 border-t border-white/[0.06]">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>

</x-layouts.super-admin>
