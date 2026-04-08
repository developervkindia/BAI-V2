<x-layouts.docs :title="'Shared with me — BAI Docs'" :currentView="'shared'">

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-white/90">Shared with me</h1>
    </div>

    @if($documents->isEmpty())
        <div class="text-center py-20">
            <svg class="w-16 h-16 mx-auto mb-4 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-white/40 text-sm">No documents shared with you yet.</p>
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
