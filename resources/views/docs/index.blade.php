<x-layouts.docs :title="'BAI Docs'" :currentView="'home'">

    @php
        $currentType = $type ?? null;
        $currentView = $view ?? 'grid';
        $typeColors = [
            'document'     => ['bg' => 'bg-sky-500/15', 'text' => 'text-sky-400', 'border' => 'border-sky-500/20', 'dot' => 'bg-sky-400'],
            'spreadsheet'  => ['bg' => 'bg-emerald-500/15', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20', 'dot' => 'bg-emerald-400'],
            'form'         => ['bg' => 'bg-violet-500/15', 'text' => 'text-violet-400', 'border' => 'border-violet-500/20', 'dot' => 'bg-violet-400'],
            'presentation' => ['bg' => 'bg-amber-500/15', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'dot' => 'bg-amber-400'],
        ];
        $typeIcons = [
            'document'     => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'spreadsheet'  => 'M3 10h18M3 14h18M10 3v18M14 3v18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z',
            'form'         => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01',
            'presentation' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
        ];
        $typeLabels = [
            'document'     => 'Documents',
            'spreadsheet'  => 'Spreadsheets',
            'form'         => 'Forms',
            'presentation' => 'Presentations',
        ];
    @endphp

    {{-- Quick stats --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['key' => 'documents', 'label' => 'Documents', 'color' => $typeColors['document'], 'icon' => $typeIcons['document']],
            ['key' => 'spreadsheets', 'label' => 'Spreadsheets', 'color' => $typeColors['spreadsheet'], 'icon' => $typeIcons['spreadsheet']],
            ['key' => 'forms', 'label' => 'Forms', 'color' => $typeColors['form'], 'icon' => $typeIcons['form']],
            ['key' => 'presentations', 'label' => 'Presentations', 'color' => $typeColors['presentation'], 'icon' => $typeIcons['presentation']],
        ] as $stat)
            <div class="rounded-2xl border border-white/[0.08] bg-[#151520]/60 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl {{ $stat['color']['bg'] }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 {{ $stat['color']['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $stat['icon'] }}"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[24px] font-bold text-white/90 leading-none">{{ $stats[$stat['key']] ?? 0 }}</p>
                        <p class="text-[11px] text-white/40 mt-1 font-medium">{{ $stat['label'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- Type filter tabs + view toggle --}}
    <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="flex items-center gap-1 flex-wrap">
            <a href="{{ route('docs.index', array_merge(request()->except('type', 'page'), [])) }}"
               class="px-3.5 py-1.5 rounded-lg text-[12px] font-medium transition-colors {{ !$currentType ? 'bg-sky-500/15 text-sky-300 border border-sky-500/20' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.05]' }}">
                All
            </a>
            @foreach($typeLabels as $typeKey => $typeLabel)
                <a href="{{ route('docs.index', array_merge(request()->except('page'), ['type' => $typeKey])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-[12px] font-medium transition-colors {{ $currentType === $typeKey ? 'bg-sky-500/15 text-sky-300 border border-sky-500/20' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.05]' }}">
                    {{ $typeLabel }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-1 shrink-0">
            <a href="{{ route('docs.index', array_merge(request()->except('view'), ['view' => 'grid'])) }}"
               class="p-2 rounded-lg transition-colors {{ $currentView === 'grid' ? 'bg-white/[0.08] text-white/80' : 'text-white/35 hover:text-white/60 hover:bg-white/[0.04]' }}" title="Grid view">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </a>
            <a href="{{ route('docs.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
               class="p-2 rounded-lg transition-colors {{ $currentView === 'list' ? 'bg-white/[0.08] text-white/80' : 'text-white/35 hover:text-white/60 hover:bg-white/[0.04]' }}" title="List view">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </a>
        </div>
    </section>

    {{-- Folders section (only on root / no type filter) --}}
    @if(!$currentType && isset($folders) && $folders->count() > 0)
        <section class="mb-8">
            <h3 class="text-[11px] font-bold text-white/30 uppercase tracking-[0.15em] mb-3">Folders</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                @foreach($folders as $folder)
                    <a href="{{ route('docs.folders.show', $folder) }}"
                       class="group flex items-center gap-3 rounded-xl border border-white/[0.08] bg-[#151520]/60 px-4 py-3 hover:border-sky-500/20 hover:bg-[#16162e] transition-all">
                        <svg class="w-5 h-5 text-sky-400/70 shrink-0 group-hover:text-sky-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span class="text-[13px] font-medium text-white/70 truncate group-hover:text-white/90">{{ $folder->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Documents --}}
    @if(isset($documents) && $documents->count() > 0)
        <section>
            <h3 class="text-[11px] font-bold text-white/30 uppercase tracking-[0.15em] mb-4">
                {{ $currentType ? ($typeLabels[$currentType] ?? 'Documents') : 'Recent files' }}
            </h3>

            @if($currentView === 'grid')
                {{-- Grid View --}}
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($documents as $doc)
                        @php
                            $docType = $doc->type ?? 'document';
                            $color = $typeColors[$docType] ?? $typeColors['document'];
                            $icon = $typeIcons[$docType] ?? $typeIcons['document'];
                        @endphp
                        <a href="{{ $doc->getEditorRoute() }}"
                           class="group rounded-2xl border border-white/[0.08] bg-[#151520]/60 p-5 transition-all duration-200 hover:border-sky-500/20 hover:bg-[#16162e] hover:shadow-[0_8px_30px_-12px_rgba(14,165,233,0.15)]">

                            {{-- Type icon + star --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-10 h-10 rounded-xl {{ $color['bg'] }} flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/></svg>
                                </div>
                                <button type="button"
                                        onclick="event.preventDefault(); event.stopPropagation(); fetch('/api/docs/documents/{{ $doc->id }}/star', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(() => location.reload())"
                                        class="p-1.5 rounded-lg text-white/20 hover:text-amber-400 hover:bg-amber-500/10 transition-colors {{ $doc->isStarredBy(auth()->user()) ? '!text-amber-400' : '' }}"
                                        title="Toggle star">
                                    <svg class="w-4 h-4" fill="{{ $doc->isStarredBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                </button>
                            </div>

                            {{-- Title --}}
                            <h4 class="text-[14px] font-semibold text-white/85 truncate group-hover:text-sky-50 transition-colors">{{ $doc->title }}</h4>

                            {{-- Owner + meta --}}
                            <div class="flex items-center gap-2 mt-3">
                                <div class="w-6 h-6 rounded-full bg-sky-500/20 text-sky-300 text-[9px] font-bold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($doc->owner->name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="text-[11px] text-white/40 truncate flex-1">{{ $doc->owner->name ?? 'Unknown' }}</span>
                            </div>

                            {{-- Last modified --}}
                            <p class="text-[11px] text-white/25 mt-2">Modified {{ $doc->updated_at->diffForHumans() }}</p>
                        </a>
                    @endforeach
                </div>

            @else
                {{-- List View --}}
                <div class="rounded-2xl border border-white/[0.08] bg-[#151520]/40 overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/[0.06]">
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/30 uppercase tracking-wider w-10"></th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/30 uppercase tracking-wider">Title</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/30 uppercase tracking-wider hidden md:table-cell">Owner</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/30 uppercase tracking-wider hidden sm:table-cell">Modified</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/30 uppercase tracking-wider hidden lg:table-cell">Type</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.04]">
                            @foreach($documents as $doc)
                                @php
                                    $docType = $doc->type ?? 'document';
                                    $color = $typeColors[$docType] ?? $typeColors['document'];
                                    $icon = $typeIcons[$docType] ?? $typeIcons['document'];
                                @endphp
                                <tr class="hover:bg-white/[0.03] transition-colors group">
                                    <td class="px-5 py-3">
                                        <a href="{{ $doc->getEditorRoute() }}" class="block">
                                            <div class="w-8 h-8 rounded-lg {{ $color['bg'] }} flex items-center justify-center">
                                                <svg class="w-4 h-4 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/></svg>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-5 py-3">
                                        <a href="{{ $doc->getEditorRoute() }}" class="text-[13px] font-medium text-white/80 hover:text-sky-200 transition-colors truncate block max-w-xs">{{ $doc->title }}</a>
                                    </td>
                                    <td class="px-5 py-3 hidden md:table-cell">
                                        <div class="flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-sky-500/20 text-sky-300 text-[8px] font-bold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($doc->owner->name ?? 'U', 0, 2)) }}
                                            </div>
                                            <span class="text-[12px] text-white/45 truncate">{{ $doc->owner->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 hidden sm:table-cell">
                                        <span class="text-[12px] text-white/35">{{ $doc->updated_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-5 py-3 hidden lg:table-cell">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium {{ $color['bg'] }} {{ $color['text'] }} border {{ $color['border'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $color['dot'] }}"></span>
                                            {{ ucfirst($docType) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <button type="button"
                                                onclick="fetch('/api/docs/documents/{{ $doc->id }}/star', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } }).then(() => location.reload())"
                                                class="p-1 rounded-lg text-white/20 hover:text-amber-400 hover:bg-amber-500/10 transition-colors {{ $doc->isStarredBy(auth()->user()) ? '!text-amber-400' : '' }}">
                                            <svg class="w-3.5 h-3.5" fill="{{ $doc->isStarredBy(auth()->user()) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Pagination --}}
            @if($documents->hasPages())
                <div class="mt-8">
                    {{ $documents->withQueryString()->links() }}
                </div>
            @endif
        </section>

    @else
        {{-- Empty state --}}
        @php
            $emptyCreateRoutes = [
                'document'     => ['route' => route('docs.documents.create'),      'label' => 'New Document',      'icon' => $typeIcons['document'],     'color' => $typeColors['document']],
                'spreadsheet'  => ['route' => route('docs.spreadsheets.create'),   'label' => 'New Spreadsheet',   'icon' => $typeIcons['spreadsheet'],  'color' => $typeColors['spreadsheet']],
                'form'         => ['route' => route('docs.forms.create'),          'label' => 'New Form',          'icon' => $typeIcons['form'],         'color' => $typeColors['form']],
                'presentation' => ['route' => route('docs.presentations.create'),  'label' => 'New Presentation',  'icon' => $typeIcons['presentation'], 'color' => $typeColors['presentation']],
            ];
            $emptyCtx = $emptyCreateRoutes[$currentType] ?? $emptyCreateRoutes['document'];
        @endphp
        <section class="py-16 text-center">
            <div class="rounded-2xl border border-dashed border-white/[0.12] bg-white/[0.02] px-8 py-16 max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-2xl {{ $emptyCtx['color']['bg'] }} border {{ $emptyCtx['color']['border'] }} flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 {{ $emptyCtx['color']['text'] }} opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $emptyCtx['icon'] }}"/></svg>
                </div>
                <h3 class="text-[18px] font-semibold text-white/80 mb-2">No {{ $currentType ? strtolower($typeLabels[$currentType] ?? 'documents') : 'documents' }} yet</h3>
                <p class="text-[14px] text-white/40 mb-6 leading-relaxed">Create your first {{ $currentType ?? 'document' }} to get started.</p>
                <a href="{{ $emptyCtx['route'] }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-500 to-sky-600 text-[13px] font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-sky-500 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    {{ $emptyCtx['label'] }}
                </a>
            </div>
        </section>
    @endif

</x-layouts.docs>
