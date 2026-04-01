<x-layouts.hub>
<div class="max-w-4xl mx-auto space-y-8 pb-12">
    <div>
        <h1 class="text-[22px] font-bold text-white/88">Usage Dashboard</h1>
        <p class="text-[13px] text-white/40 mt-1">Monitor your {{ $organization->name }} resource usage against plan limits</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($usage as $productKey => $metrics)
            @foreach($metrics as $metricName => $metric)
                @php
                    $current = $metric['current'] ?? 0;
                    $limit = $metric['limit'];
                    $percentage = $limit ? min(100, round(($current / $limit) * 100)) : 0;
                    $isNearLimit = $limit && $percentage >= 80;
                    $isUnlimited = $limit === null;
                @endphp
                <div class="p-5 rounded-xl border border-white/[0.06] bg-[#17172A]">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-semibold text-white/35 uppercase tracking-wider">
                            {{ ucfirst($productKey) }} / {{ str_replace('_', ' ', ucfirst($metricName)) }}
                        </span>
                        @if($isNearLimit)
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-400">NEAR LIMIT</span>
                        @endif
                    </div>
                    <p class="text-[24px] font-bold text-white/80">
                        {{ $current }}
                        <span class="text-[14px] text-white/30 font-normal">/ {{ $isUnlimited ? '∞' : $limit }}</span>
                    </p>
                    @if(!$isUnlimited && $limit > 0)
                        <div class="mt-3 h-1.5 rounded-full bg-white/[0.06] overflow-hidden">
                            <div class="h-full rounded-full transition-all {{ $isNearLimit ? 'bg-amber-500' : 'bg-indigo-500' }}"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>

    <div class="text-center pt-4">
        <a href="{{ route('subscriptions.index') }}" class="text-[13px] text-indigo-400 hover:text-indigo-300 transition-colors">
            Back to Subscription Management
        </a>
    </div>
</div>
</x-layouts.hub>
