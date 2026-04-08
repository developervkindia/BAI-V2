{{-- Document card for grid views --}}
<div class="bg-[#151520] rounded-2xl border border-white/[0.06] hover:border-sky-500/20 hover:bg-[#191926] transition-all group relative">
    <a href="{{ $doc->getEditorRoute() }}" class="block p-5">
        {{-- Type icon --}}
        <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: {{ $doc->getTypeColor() }}15">
                <svg class="w-5 h-5" style="color: {{ $doc->getTypeColor() }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $doc->getTypeIcon() }}"/>
                </svg>
            </div>
            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full" style="background: {{ $doc->getTypeColor() }}15; color: {{ $doc->getTypeColor() }}">
                {{ $doc->getTypeLabel() }}
            </span>
        </div>

        {{-- Title --}}
        <h3 class="text-[14px] font-semibold text-white/85 truncate mb-1.5">{{ $doc->title }}</h3>

        {{-- Meta --}}
        <div class="flex items-center gap-2 text-[11px] text-white/30">
            <span>{{ $doc->owner->name ?? 'Unknown' }}</span>
            <span>&middot;</span>
            <span>{{ $doc->updated_at->diffForHumans() }}</span>
        </div>
    </a>

    {{-- Star toggle --}}
    <button @click.prevent="fetch('/api/docs/documents/{{ $doc->id }}/star', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(() => location.reload())"
            class="absolute top-4 right-4 p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white/[0.06]"
            title="Toggle star">
        @if($doc->isStarredBy(auth()->user()))
            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        @else
            <svg class="w-4 h-4 text-white/20 hover:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        @endif
    </button>
</div>
