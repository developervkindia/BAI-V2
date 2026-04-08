<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Choose Your Plan — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body { background: #0D0D18; }
        .plan-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .plan-card:hover { transform: translateY(-4px); }
        .plan-card.is-featured { transform: translateY(-8px); }
        .plan-card.is-featured:hover { transform: translateY(-12px); }
    </style>
</head>
<body class="antialiased min-h-screen font-sans" x-data="onboardingPlans()">

    <div class="min-h-screen py-10 px-4">
        <div class="max-w-5xl mx-auto">

            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto mb-4" style="box-shadow: 0 8px 24px rgba(99,102,241,0.25);">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-1.5">Start your plan for {{ $organization->name }}</h1>
                <p class="text-[15px]" style="color: rgba(255,255,255,0.4);">Pick a product below and choose the plan that fits. You can change anytime.</p>
            </div>

            {{-- Promo Code --}}
            <div class="max-w-sm mx-auto mb-8">
                <div class="flex items-center gap-2 p-1 rounded-xl" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                    <input type="text"
                           x-model="promoCode"
                           placeholder="Enter promo code"
                           class="flex-1 px-3 py-2 bg-transparent text-white text-sm focus:outline-none" style="color: white;" placeholder="Enter promo code">
                    <button @click="applyPromo()" type="button"
                            class="px-4 py-2 rounded-lg text-white text-xs font-semibold shrink-0 transition-colors" style="background: #6366f1;">
                        Apply
                    </button>
                </div>
                <div x-show="promoStatus === 'valid'" x-cloak class="mt-2 text-xs text-center flex items-center justify-center gap-1" style="color: #4ade80;">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Promo applied — select Pro for free!
                </div>
                <div x-show="promoStatus === 'invalid'" x-cloak class="mt-2 text-xs text-center" style="color: #f87171;">Invalid promo code.</div>
            </div>

            <form method="POST" action="{{ route('onboarding.select-plans') }}">
                @csrf
                <input type="hidden" name="promo_code" :value="promoCode">

                {{-- Product Tabs --}}
                <div class="flex items-center justify-center gap-2 mb-8 flex-wrap">
                    @foreach($products as $product)
                        <button type="button"
                                @click="activeProduct = '{{ $product->key }}'"
                                class="px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-all"
                                :style="activeProduct === '{{ $product->key }}'
                                    ? 'background: rgba(99,102,241,0.15); color: #818cf8; border: 1px solid rgba(99,102,241,0.3);'
                                    : 'background: transparent; color: rgba(255,255,255,0.45); border: 1px solid rgba(255,255,255,0.08);'">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $product->color ?? '#6366f1' }};"></span>
                            {{ $product->name }}
                            <span x-show="selectedPlans['{{ $product->key }}'] === 'pro' && promoStatus === 'valid'"
                                  x-cloak
                                  class="text-[10px] px-1.5 py-0.5 rounded-full font-bold" style="background: rgba(74,222,128,0.15); color: #4ade80;">PRO</span>
                        </button>
                    @endforeach
                </div>

                {{-- Plan Cards per Product --}}
                @foreach($products as $product)
                    @php $productPlans = $plans[$product->key] ?? []; @endphp
                    @if(empty($productPlans)) @continue @endif

                    <div x-show="activeProduct === '{{ $product->key }}'" x-transition.opacity.duration.200ms>

                        <div class="text-center mb-6">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full" style="border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03);">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $product->color ?? '#6366f1' }};"></span>
                                <span class="text-xs font-semibold" style="color: rgba(255,255,255,0.5);">{{ $product->name }}</span>
                            </div>
                            @if($product->tagline)
                                <p class="text-sm mt-1" style="color: rgba(255,255,255,0.3);">{{ $product->tagline }}</p>
                            @endif
                        </div>

                        <div class="grid md:grid-cols-3 gap-5 items-start max-w-4xl mx-auto">
                            @foreach(['free' => 'Free', 'pro' => 'Pro', 'enterprise' => 'Enterprise'] as $planKey => $planName)
                                @php
                                    $features = $productPlans[$planKey] ?? [];
                                    $isFeatured = $planKey === 'pro';
                                    $isEnterprise = $planKey === 'enterprise';
                                @endphp

                                <label class="plan-card block {{ $isFeatured ? 'is-featured' : '' }} {{ $isEnterprise ? 'pointer-events-none' : 'cursor-pointer' }}">
                                    @if(!$isEnterprise)
                                        <input type="radio" name="plans[{{ $product->key }}]" value="{{ $planKey }}" class="hidden"
                                               x-model="selectedPlans['{{ $product->key }}']" {{ $planKey === 'free' ? 'checked' : '' }}>
                                    @endif

                                    <div class="rounded-2xl overflow-hidden h-full transition-all"
                                         :style="selectedPlans['{{ $product->key }}'] === '{{ $planKey }}'
                                             ? 'background: linear-gradient(to bottom, rgba(99,102,241,0.12), #15152A); border: 2px solid rgba(99,102,241,0.4); box-shadow: 0 8px 32px rgba(99,102,241,0.1);'
                                             : 'background: #15152A; border: 1px solid rgba(255,255,255,0.08); {{ $isEnterprise ? "opacity: 0.5;" : "" }}'">

                                        @if($isFeatured)
                                            <div style="height: 3px; background: linear-gradient(90deg, #6366f1, #a855f7, #6366f1);"></div>
                                        @endif

                                        <div class="p-6 text-center">

                                            {{-- Plan Label --}}
                                            <p class="text-xs font-bold uppercase tracking-widest mb-5" style="color: {{ $isFeatured ? '#818cf8' : 'rgba(255,255,255,0.3)' }};">
                                                {{ $planName }}
                                                @if($isFeatured)
                                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] normal-case tracking-normal font-semibold" style="background: rgba(99,102,241,0.2); color: #a5b4fc;">Popular</span>
                                                @endif
                                            </p>

                                            {{-- Price Circle --}}
                                            <div class="w-[130px] h-[130px] rounded-full flex flex-col items-center justify-center mx-auto mb-5"
                                                 style="{{ $isFeatured
                                                     ? 'background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.1)); border: 2px solid rgba(99,102,241,0.25);'
                                                     : 'background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);' }}">
                                                @if($planKey === 'free')
                                                    <span class="text-3xl font-extrabold text-white">$0</span>
                                                    <span class="text-[11px] font-medium" style="color: rgba(255,255,255,0.3);">forever</span>
                                                @elseif($planKey === 'pro')
                                                    <template x-if="promoStatus === 'valid'">
                                                        <div class="text-center">
                                                            <span class="text-lg line-through font-bold" style="color: rgba(255,255,255,0.2);">$12</span>
                                                            <span class="block text-2xl font-extrabold" style="color: #4ade80;">FREE</span>
                                                            <span class="text-[10px] font-medium" style="color: rgba(74,222,128,0.6);">with promo</span>
                                                        </div>
                                                    </template>
                                                    <template x-if="promoStatus !== 'valid'">
                                                        <div class="text-center">
                                                            <span class="text-3xl font-extrabold text-white">$12</span>
                                                            <span class="block text-[11px] font-medium" style="color: rgba(255,255,255,0.3);">/user/month</span>
                                                        </div>
                                                    </template>
                                                @else
                                                    <span class="text-lg font-bold" style="color: rgba(255,255,255,0.6);">Custom</span>
                                                    <span class="text-[11px] font-medium" style="color: rgba(255,255,255,0.25);">Contact us</span>
                                                @endif
                                            </div>

                                            {{-- Features --}}
                                            <div class="space-y-2.5 text-left mb-6">
                                                @foreach($features as $featureName => $featureValue)
                                                    <div class="flex items-center gap-2.5 text-[13px]">
                                                        @if(is_bool($featureValue))
                                                            @if($featureValue)
                                                                <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background: rgba(99,102,241,0.15);">
                                                                    <svg class="w-2.5 h-2.5" style="color: #818cf8;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                                </div>
                                                                <span style="color: rgba(255,255,255,0.55);">{{ str_replace('_', ' ', ucfirst($featureName)) }}</span>
                                                            @else
                                                                <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background: rgba(255,255,255,0.04);">
                                                                    <svg class="w-2.5 h-2.5" style="color: rgba(255,255,255,0.15);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                                </div>
                                                                <span style="color: rgba(255,255,255,0.25);">{{ str_replace('_', ' ', ucfirst($featureName)) }}</span>
                                                            @endif
                                                        @else
                                                            <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0" style="background: rgba(99,102,241,0.15);">
                                                                <svg class="w-2.5 h-2.5" style="color: #818cf8;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                            </div>
                                                            <span style="color: rgba(255,255,255,0.55);">
                                                                {{ str_replace('_', ' ', ucfirst($featureName)) }}
                                                                <span style="color: rgba(255,255,255,0.3);">({{ $featureValue === null ? 'Unlimited' : $featureValue }})</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Button --}}
                                            @if($isEnterprise)
                                                <div class="py-2.5 rounded-xl text-[13px] font-semibold text-center" style="border: 1px solid rgba(255,255,255,0.08); color: rgba(255,255,255,0.25);">
                                                    Contact Sales
                                                </div>
                                            @else
                                                <div class="py-2.5 rounded-xl text-[13px] font-semibold text-center transition-all"
                                                     :style="selectedPlans['{{ $product->key }}'] === '{{ $planKey }}'
                                                         ? 'background: #6366f1; color: white; box-shadow: 0 4px 16px rgba(99,102,241,0.25);'
                                                         : 'border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.4);'">
                                                    <span x-text="selectedPlans['{{ $product->key }}'] === '{{ $planKey }}' ? 'Selected' : 'Select {{ $planName }}'"></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Summary & Actions --}}
                <div class="max-w-4xl mx-auto mt-10 pt-6" style="border-top: 1px solid rgba(255,255,255,0.06);">
                    <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
                        @foreach($products as $product)
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $product->color ?? '#6366f1' }};"></span>
                                <span class="text-xs" style="color: rgba(255,255,255,0.4);">{{ $product->name }}:</span>
                                <span class="text-xs font-semibold"
                                      :style="selectedPlans['{{ $product->key }}'] === 'pro' ? 'color: #818cf8;' : 'color: rgba(255,255,255,0.6);'"
                                      x-text="selectedPlans['{{ $product->key }}'] === 'pro' ? 'Pro' : 'Free'"></span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-center">
                        <button type="submit" class="px-10 py-2.5 rounded-xl text-white text-sm font-bold transition-all" style="background: linear-gradient(135deg, #6366f1, #7c3aed); box-shadow: 0 4px 16px rgba(99,102,241,0.25);">
                            Continue to Dashboard
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function onboardingPlans() {
            return {
                promoCode: '{{ $promoCode }}',
                promoStatus: '',
                savedPromo: '{{ $promoCode }}',
                activeProduct: '{{ $products->first()?->key ?? '' }}',
                selectedPlans: {
                    @foreach($products as $product)
                        '{{ $product->key }}': 'free',
                    @endforeach
                },
                applyPromo() {
                    if (this.promoCode && this.promoCode === this.savedPromo) {
                        this.promoStatus = 'valid';
                    } else if (this.promoCode) {
                        this.promoStatus = 'invalid';
                    } else {
                        this.promoStatus = '';
                    }
                },
                init() {
                    if (this.savedPromo) {
                        this.applyPromo();
                    }
                }
            }
        }
    </script>
</body>
</html>
