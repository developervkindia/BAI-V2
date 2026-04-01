@php
    $reports = $allEmployees->filter(fn($e) => $e->reporting_manager_id == $employee->id);
    $hasReports = $reports->count() > 0;
    $depthPadding = $depth * 32;
@endphp

<div class="relative" style="padding-left: {{ $depthPadding }}px">
    {{-- Connector line from parent --}}
    @if($depth > 0)
        <div class="absolute top-5 left-0 h-px bg-white/[0.08]" style="width: {{ $depthPadding }}px"></div>
        <div class="absolute top-0 left-0 w-px bg-white/[0.08]" style="height: 20px; left: {{ $depthPadding - 32 }}px"></div>
    @endif

    {{-- Node Card --}}
    <div class="flex items-start gap-2 mb-1">
        {{-- Expand/Collapse Toggle --}}
        @if($hasReports)
            <button @click="toggle({{ $employee->id }})"
                    class="mt-2 w-5 h-5 rounded flex items-center justify-center bg-white/[0.06] border border-white/[0.08] hover:bg-white/[0.1] text-white/40 hover:text-white/65 transition-all shrink-0">
                <svg :class="isExpanded({{ $employee->id }}) ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        @else
            <div class="w-5 shrink-0"></div>
        @endif

        {{-- Employee Card --}}
        <a href="{{ route('hr.people.show', $employee) }}"
           class="flex items-center gap-3 bg-[#17172A] border border-white/[0.07] rounded-xl px-4 py-3 hover:border-white/[0.14] hover:bg-[#1a1a30] transition-all group cursor-pointer min-w-[240px]">
            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-full prod-bg-muted prod-text text-xs font-bold flex items-center justify-center shrink-0">
                {{ strtoupper(substr($employee->user->name ?? 'E', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white/80 group-hover:text-white truncate transition-colors">
                    {{ $employee->user->name ?? 'Employee' }}
                </p>
                <p class="text-[11px] text-white/40 truncate">
                    {{ $employee->hrDesignation->name ?? $employee->designation ?? 'No designation' }}
                </p>
            </div>
            <div class="text-right shrink-0">
                <span class="text-[10px] text-white/30">{{ $employee->department ?? '' }}</span>
                @if($hasReports)
                    <p class="text-[10px] prod-text mt-0.5">{{ $reports->count() }} report{{ $reports->count() > 1 ? 's' : '' }}</p>
                @endif
            </div>
        </a>
    </div>

    {{-- Reports (children) --}}
    @if($hasReports)
        <div x-show="isExpanded({{ $employee->id }})" x-collapse class="relative">
            {{-- Vertical connecting line --}}
            <div class="absolute left-[{{ $depthPadding + 10 }}px] top-0 w-px bg-white/[0.08]"
                 style="left: {{ $depthPadding + 10 }}px; height: calc(100% - 16px)"></div>

            @foreach($reports as $report)
                @include('hr.people._org-node', ['employee' => $report, 'allEmployees' => $allEmployees, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
