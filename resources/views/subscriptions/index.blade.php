<x-layouts.hub>
<div class="max-w-4xl mx-auto space-y-8 pb-12">
    <div>
        <h1 class="text-[22px] font-bold text-white/88">Subscription Management</h1>
        <p class="text-[13px] text-white/40 mt-1">Manage your {{ $organization->name }} product subscriptions</p>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-[13px]">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @foreach($subscriptions as $subscription)
            @php
                $product = $subscription->product;
                if (!$product) continue;
                $details = $planDetails[$product->key] ?? [];
                $currentPlan = $details['plan'] ?? 'free';
                $features = $details['features'] ?? [];
            @endphp
            <div class="p-6 rounded-2xl border border-white/[0.08] bg-[#17172A]">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-[16px] font-bold text-white/85">{{ $product->name }}</h3>
                        <p class="text-[12px] text-white/40 mt-0.5">{{ $product->tagline }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider
                            {{ $subscription->status === 'active' ? 'bg-green-500/15 text-green-400' : ($subscription->status === 'trialing' ? 'bg-amber-500/15 text-amber-400' : 'bg-white/5 text-white/30') }}">
                            {{ $subscription->status }}
                        </span>
                        <p class="text-[18px] font-bold text-white/75 mt-1">{{ ucfirst($currentPlan) }}</p>
                    </div>
                </div>

                @if($subscription->status === 'trialing' && $subscription->trial_ends_at)
                    <div class="mt-3 p-3 rounded-lg bg-amber-500/10 border border-amber-500/15 text-[12px] text-amber-400">
                        Trial ends {{ $subscription->trial_ends_at->diffForHumans() }}
                    </div>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    @if($currentPlan === 'free')
                        <form method="POST" action="{{ route('subscriptions.start-trial') }}">
                            @csrf
                            <input type="hidden" name="product_key" value="{{ $product->key }}">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[12px] font-semibold transition-colors">
                                Start 14-Day Pro Trial
                            </button>
                        </form>
                        <form method="POST" action="{{ route('subscriptions.change-plan') }}">
                            @csrf
                            <input type="hidden" name="product_key" value="{{ $product->key }}">
                            <input type="hidden" name="plan" value="pro">
                            <button type="submit" class="px-4 py-2 rounded-lg border border-indigo-500/30 text-indigo-400 text-[12px] font-medium hover:bg-indigo-500/10 transition-colors">
                                Upgrade to Pro
                            </button>
                        </form>
                    @elseif($currentPlan === 'pro')
                        <form method="POST" action="{{ route('subscriptions.change-plan') }}">
                            @csrf
                            <input type="hidden" name="product_key" value="{{ $product->key }}">
                            <input type="hidden" name="plan" value="enterprise">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[12px] font-semibold transition-colors">
                                Upgrade to Enterprise
                            </button>
                        </form>
                        <form method="POST" action="{{ route('subscriptions.change-plan') }}">
                            @csrf
                            <input type="hidden" name="product_key" value="{{ $product->key }}">
                            <input type="hidden" name="plan" value="free">
                            <button type="submit" class="px-4 py-2 rounded-lg border border-white/10 text-white/40 text-[12px] font-medium hover:bg-white/5 transition-colors">
                                Downgrade to Free
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-center pt-4">
        <a href="{{ route('pricing') }}" class="text-[13px] text-indigo-400 hover:text-indigo-300 transition-colors">
            View full plan comparison
        </a>
        <span class="mx-2 text-white/20">|</span>
        <a href="{{ route('subscriptions.usage') }}" class="text-[13px] text-indigo-400 hover:text-indigo-300 transition-colors">
            View usage details
        </a>
    </div>
</div>
</x-layouts.hub>
