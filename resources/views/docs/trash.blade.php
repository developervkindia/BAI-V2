<x-layouts.docs :title="'Trash — BAI Docs'" :currentView="'trash'">

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-white/90">Trash</h1>
        <p class="text-[12px] text-white/30">Items in trash are deleted after 30 days</p>
    </div>

    @if($documents->isEmpty())
        <div class="text-center py-20">
            <svg class="w-16 h-16 mx-auto mb-4 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <p class="text-white/40 text-sm">Trash is empty.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach($documents as $doc)
                <div class="flex items-center justify-between bg-[#151520] rounded-xl border border-white/[0.06] px-5 py-3.5 group hover:border-red-500/20">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background: {{ $doc->getTypeColor() }}20">
                            <svg class="w-4 h-4" style="color: {{ $doc->getTypeColor() }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $doc->getTypeIcon() }}"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white/70 truncate">{{ $doc->title }}</p>
                            <p class="text-[11px] text-white/30">{{ $doc->getTypeLabel() }} · Deleted {{ $doc->deleted_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <form method="POST" action="{{ route('docs.index') }}" x-data
                              @submit.prevent="fetch('/api/docs/documents/{{ $doc->id }}/restore', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(() => location.reload())">
                            <button type="submit" class="px-3 py-1.5 text-[11px] font-medium rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20">Restore</button>
                        </form>
                        <button @click="if(confirm('Permanently delete this document?')) fetch('/api/docs/documents/{{ $doc->id }}/force', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(() => location.reload())"
                                class="px-3 py-1.5 text-[11px] font-medium rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20">
                            Delete forever
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $documents->links() }}</div>
    @endif
</div>

</x-layouts.docs>
