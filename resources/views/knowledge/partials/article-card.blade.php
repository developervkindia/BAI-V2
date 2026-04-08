@php
    $featured = $featured ?? false;
@endphp
<a href="{{ route('knowledge.articles.show', $article) }}"
   class="group flex flex-col rounded-2xl border border-white/[0.08] bg-[#14142A]/80 shadow-[0_4px_24px_-8px_rgba(0,0,0,0.5)] overflow-hidden transition-all duration-200 hover:border-sky-500/30 hover:shadow-[0_8px_32px_-8px_rgba(56,189,248,0.12)] {{ $featured ? 'ring-1 ring-sky-500/35 bg-gradient-to-br from-sky-500/[0.08] to-violet-600/[0.06]' : '' }}">
    <div class="aspect-[16/10] bg-gradient-to-br from-sky-500/20 via-violet-600/15 to-indigo-900/40 relative overflow-hidden">
        <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 20% 80%, rgba(56,189,248,0.25), transparent 50%), radial-gradient(circle at 80% 20%, rgba(139,92,246,0.2), transparent 45%);"></div>
        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between gap-2">
            @if($article->category)
                <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-md bg-black/40 text-sky-200/90 border border-white/10 backdrop-blur-sm">{{ $article->category->name }}</span>
            @endif
            <span class="flex flex-wrap items-center gap-1.5 ml-auto shrink-0 justify-end">
                @if($article->status === 'draft')
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-200 border border-amber-500/25">Draft</span>
                @endif
                @if($article->pinned)
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-violet-500/20 text-violet-200 border border-violet-500/25">Pinned</span>
                @endif
            </span>
        </div>
    </div>
    <div class="p-4 flex flex-col flex-1">
        <h3 class="text-[15px] font-semibold text-white/90 leading-snug group-hover:text-sky-100 transition-colors line-clamp-2">{{ $article->title }}</h3>
        @if($article->excerpt)
            <p class="text-[12px] text-white/40 mt-2 line-clamp-2 leading-relaxed flex-1">{{ $article->excerpt }}</p>
        @else
            <p class="text-[12px] text-white/35 mt-2 line-clamp-2 flex-1">&nbsp;</p>
        @endif
        <div class="mt-4 pt-3 border-t border-white/[0.06] flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500/30 to-violet-600/30 border border-white/10 flex items-center justify-center text-[11px] font-bold text-sky-200 shrink-0">
                    {{ strtoupper(substr($article->author?->name ?? '?', 0, 2)) }}
                </span>
                <div class="min-w-0">
                    <p class="text-[12px] font-medium text-white/75 truncate">{{ $article->author?->name ?? 'Member' }}</p>
                    <p class="text-[10px] text-white/30">{{ $article->updated_at->diffForHumans() }}</p>
                </div>
            </div>
            <span class="text-[11px] font-semibold text-sky-400 shrink-0 group-hover:text-sky-300 flex items-center gap-0.5">
                Read
                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        </div>
    </div>
</a>
