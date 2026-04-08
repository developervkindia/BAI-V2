<x-layouts.docs :title="'Starred — BAI Docs'" :currentView="'starred'">

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-white/90">Starred</h1>
    </div>

    @if($documents->isEmpty())
        <div class="text-center py-20">
            <svg class="w-16 h-16 mx-auto mb-4 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            <p class="text-white/40 text-sm">No starred documents yet.</p>
            <p class="text-white/25 text-xs mt-1">Star documents to access them quickly.</p>
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

</x-layouts.docs>
