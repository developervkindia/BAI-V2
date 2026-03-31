<x-layouts.super-admin title="Products">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Products</h1>
            <p class="text-sm text-white/40 mt-0.5">Manage platform products and availability</p>
        </div>
    </div>

    {{-- Products Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($products ?? [] as $product)
        <div class="sa-card overflow-hidden group">
            {{-- Color Bar --}}
            <div class="h-1" style="background: {{ $product->color ?? '#ef4444' }};"></div>

            <div class="p-5">
                {{-- Header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold" style="background: {{ $product->color ?? '#ef4444' }}20; color: {{ $product->color ?? '#ef4444' }};">
                            {{ strtoupper(substr($product->name, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-sm">{{ $product->name }}</h3>
                            <p class="text-white/30 text-xs">{{ $product->slug ?? '' }}</p>
                        </div>
                    </div>
                    {{-- Color Swatch --}}
                    <div class="w-5 h-5 rounded-full border-2 border-white/10" style="background: {{ $product->color ?? '#ef4444' }};"></div>
                </div>

                {{-- Tagline --}}
                @if($product->tagline ?? $product->description ?? null)
                <p class="text-white/40 text-xs mb-4 line-clamp-2">{{ $product->tagline ?? $product->description }}</p>
                @endif

                {{-- Stats --}}
                <div class="flex items-center gap-4 mb-4 py-3 border-y border-white/[0.04]">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-white/30 font-medium">Subscriptions</p>
                        <p class="text-white font-semibold text-lg mt-0.5">{{ $product->subscriptions_count ?? $product->subscriptions->count() ?? 0 }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/[0.06]"></div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-white/30 font-medium">Status</p>
                        <p class="mt-0.5">
                            @if($product->is_available ?? true)
                                <span class="sa-badge sa-badge-green">Available</span>
                            @else
                                <span class="sa-badge sa-badge-red">Unavailable</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Availability Toggle --}}
                <form method="POST" action="{{ route('super-admin.products.toggle-availability', $product) }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 rounded-lg text-xs font-medium transition-all
                        {{ ($product->is_available ?? true)
                            ? 'bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20'
                            : 'bg-green-500/10 text-green-400 hover:bg-green-500/20 border border-green-500/20'
                        }}">
                        @if($product->is_available ?? true)
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728A9 9 0 015.636 5.636"/>
                            </svg>
                            Disable Product
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enable Product
                        @endif
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full sa-card p-12 text-center">
            <svg class="w-12 h-12 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-white/30 text-sm">No products configured</p>
        </div>
        @endforelse
    </div>

</x-layouts.super-admin>
