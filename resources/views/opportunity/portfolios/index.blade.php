<x-layouts.opportunity title="Portfolios" currentView="portfolios">

<div class="px-6 py-6 max-w-5xl mx-auto" x-data="{ showCreate: false, newName: '', newColor: '#14B8A6' }">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-[20px] font-bold text-white/90">Portfolios</h1>
        <button @click="showCreate = true" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold hover:bg-teal-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New portfolio
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($portfolios ?? [] as $portfolio)
            <a href="{{ route('opportunity.portfolios.show', $portfolio) }}" class="bg-[#111122] border border-white/[0.07] rounded-2xl p-5 hover:border-teal-500/20 transition-all">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: {{ $portfolio->color }}22">
                        <svg class="w-5 h-5" style="color: {{ $portfolio->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[14px] font-semibold text-white/80">{{ $portfolio->name }}</h3>
                        <p class="text-[11px] text-white/30">{{ $portfolio->projects_count ?? 0 }} projects</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 rounded-full bg-teal-500/20 text-teal-400 text-[8px] font-bold flex items-center justify-center">{{ strtoupper(substr($portfolio->owner->name ?? '', 0, 2)) }}</div>
                    <span class="text-[11px] text-white/30">{{ $portfolio->owner->name ?? '' }}</span>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-16">
                <h3 class="text-[15px] text-white/50 mb-2">No portfolios yet</h3>
                <p class="text-[13px] text-white/25 mb-4">Group related projects together for a high-level view</p>
                <button @click="showCreate = true" class="px-4 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold">Create portfolio</button>
            </div>
        @endforelse
    </div>

    {{-- Create modal --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showCreate = false">
        <div class="absolute inset-0 bg-black/70"></div>
        <form method="POST" action="{{ route('opportunity.portfolios.store') }}" class="relative bg-[#1A1A2E] border border-white/[0.1] rounded-2xl w-full max-w-md p-6 shadow-2xl" x-transition>
            @csrf
            <h2 class="text-[18px] font-bold text-white/90 mb-4">New portfolio</h2>
            <div class="space-y-4">
                <div><label class="block text-[12px] text-white/40 mb-1.5">Name</label><input type="text" name="name" required class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/85 text-[14px] focus:ring-1 focus:ring-teal-500/40 focus:outline-none"/></div>
                <div>
                    <label class="block text-[12px] text-white/40 mb-1.5">Color</label>
                    <div class="flex gap-2"><input type="hidden" name="color" x-model="newColor"/>
                        @foreach(['#14B8A6','#3B82F6','#8B5CF6','#EC4899','#F59E0B','#EF4444'] as $c)
                            <button type="button" @click="newColor='{{ $c }}'" class="w-7 h-7 rounded-lg border-2" :class="newColor==='{{ $c }}'?'border-white/60 scale-110':'border-transparent'" style="background:{{ $c }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showCreate = false" class="px-4 py-2 text-[13px] text-white/50">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold hover:bg-teal-400">Create</button>
            </div>
        </form>
    </div>
</div>

</x-layouts.opportunity>
