<x-layouts.guest>
<div class="min-h-screen bg-[#0D0D18] py-16 px-4">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Simple, transparent pricing</h1>
            <p class="text-lg text-white/50">Choose the plan that fits your team. Upgrade or downgrade anytime.</p>
        </div>

        @foreach($products as $product)
            @php $productPlans = $plans[$product->key] ?? []; @endphp
            @if(empty($productPlans)) @continue @endif

            <div class="mb-16">
                <h2 class="text-xl font-bold text-white/80 mb-6">{{ $product->name }}</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach(['free' => 'Free', 'pro' => 'Pro', 'enterprise' => 'Enterprise'] as $planKey => $planName)
                        @php $features = $productPlans[$planKey] ?? []; @endphp
                        <div class="p-6 rounded-2xl border {{ $planKey === 'pro' ? 'border-indigo-500/40 bg-[#1A1A30]' : 'border-white/[0.08] bg-[#15152A]' }}">
                            <h3 class="text-lg font-bold {{ $planKey === 'pro' ? 'text-indigo-400' : 'text-white/80' }}">{{ $planName }}</h3>
                            <p class="text-[28px] font-bold text-white mt-2">
                                @if($planKey === 'free')
                                    $0
                                @elseif($planKey === 'pro')
                                    $12
                                @else
                                    Custom
                                @endif
                                @if($planKey !== 'enterprise')
                                    <span class="text-[13px] text-white/35 font-normal">/user/month</span>
                                @endif
                            </p>

                            <div class="mt-6 space-y-3">
                                @foreach($features as $featureName => $featureValue)
                                    <div class="flex items-center gap-2 text-[13px]">
                                        @if(is_bool($featureValue))
                                            @if($featureValue)
                                                <svg class="w-4 h-4 text-green-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-white/20 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            @endif
                                        @else
                                            <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        @endif
                                        <span class="text-white/60">
                                            {{ str_replace('_', ' ', ucfirst($featureName)) }}
                                            @if(!is_bool($featureValue) && $featureValue !== null)
                                                <span class="text-white/40">({{ $featureValue === null ? 'Unlimited' : $featureValue }})</span>
                                            @elseif(!is_bool($featureValue) && $featureValue === null)
                                                <span class="text-white/40">(Unlimited)</span>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-8">
                                @if($planKey === 'free')
                                    <span class="block text-center py-2.5 rounded-lg bg-white/5 text-white/40 text-[13px] font-medium">Current Plan</span>
                                @elseif($planKey === 'enterprise')
                                    <a href="mailto:sales@bai.app" class="block text-center py-2.5 rounded-lg border border-white/20 text-white/70 text-[13px] font-medium hover:bg-white/5 transition-colors">Contact Sales</a>
                                @else
                                    <a href="{{ auth()->check() ? route('subscriptions.index') : route('register') }}" class="block text-center py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[13px] font-semibold transition-colors">
                                        {{ auth()->check() ? 'Upgrade Now' : 'Get Started' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
</x-layouts.guest>
