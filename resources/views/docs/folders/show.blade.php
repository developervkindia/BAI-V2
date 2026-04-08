<x-layouts.docs :title="$folder->name . ' — BAI Docs'" :currentView="'folder'">

<div class="space-y-6">
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-1.5 text-[12px] text-white/40">
        <a href="{{ route('docs.index') }}" class="hover:text-white/70">Docs</a>
        @foreach($breadcrumbs as $crumb)
            <span>/</span>
            @if($loop->last)
                <span class="text-white/70 font-medium">{{ $crumb->name }}</span>
            @else
                <a href="{{ route('docs.folders.show', $crumb) }}" class="hover:text-white/70">{{ $crumb->name }}</a>
            @endif
        @endforeach
    </nav>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: {{ $folder->color ?? '#0EA5E9' }}20">
                <svg class="w-5 h-5" style="color: {{ $folder->color ?? '#0EA5E9' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-white/90">{{ $folder->name }}</h1>
        </div>
    </div>

    {{-- Subfolders --}}
    @if($subfolders->isNotEmpty())
        <div>
            <h3 class="text-[11px] font-semibold text-white/30 uppercase tracking-wider mb-3">Folders</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                @foreach($subfolders as $subfolder)
                    <a href="{{ route('docs.folders.show', $subfolder) }}" class="flex items-center gap-3 bg-[#151520] rounded-xl border border-white/[0.06] px-4 py-3 hover:border-sky-500/20 hover:bg-[#191926] transition-all">
                        <svg class="w-5 h-5 text-sky-400/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span class="text-[13px] text-white/70 truncate">{{ $subfolder->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Documents --}}
    <div>
        @if($subfolders->isNotEmpty())
            <h3 class="text-[11px] font-semibold text-white/30 uppercase tracking-wider mb-3">Files</h3>
        @endif

        @if($documents->isEmpty())
            <div class="text-center py-16">
                <p class="text-white/30 text-sm">This folder is empty.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($documents as $doc)
                    @include('docs.partials.document-card', ['doc' => $doc])
                @endforeach
            </div>
            <div class="mt-6">{{ $documents->links() }}</div>
        @endif
    </div>
</div>

</x-layouts.docs>
