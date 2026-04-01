<x-layouts.hr title="Engagement" currentView="engagement">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    showRecognitionModal: false,
    recognitionForm: {
        employee_profile_id: '',
        type: 'shoutout',
        title: '',
        description: ''
    },
    submitting: false,
    successMsg: '',
    errorMsg: '',

    async submitRecognition() {
        this.submitting = true;
        this.errorMsg = '';
        this.successMsg = '';
        try {
            const res = await fetch('/api/hr/recognitions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.recognitionForm)
            });
            if (!res.ok) {
                const data = await res.json();
                throw new Error(data.message || 'Failed to submit recognition');
            }
            this.successMsg = 'Recognition submitted successfully!';
            this.recognitionForm = { employee_profile_id: '', type: 'shoutout', title: '', description: '' };
            setTimeout(() => { this.showRecognitionModal = false; this.successMsg = ''; window.location.reload(); }, 1500);
        } catch (e) {
            this.errorMsg = e.message;
        } finally {
            this.submitting = false;
        }
    }
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Engagement</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Stay connected with your team</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="showRecognitionModal = true"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Give Recognition
            </button>
            <a href="{{ route('hr.announcements.create') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.07] border border-white/[0.10] text-white/70 text-[13px] font-semibold hover:bg-white/[0.12] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                New Announcement
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: Activity Feed (2/3 width) --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Pinned Announcements --}}
            @php
                $pinned = $announcements->where('is_pinned', true);
            @endphp
            @if($pinned->count() > 0)
                <div class="space-y-3">
                    <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                        Pinned
                    </h2>
                    @foreach($pinned as $pinAnn)
                        <div class="bg-[#17172A] border border-cyan-500/20 rounded-xl p-5 hover:border-cyan-500/30 transition-colors">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2H5V5zm0 4h10v7a2 2 0 01-2 2H7a2 2 0 01-2-2V9z"/></svg>
                                        @php
                                            $typeColors = [
                                                'general' => 'text-blue-400 bg-blue-500/10',
                                                'policy' => 'text-purple-400 bg-purple-500/10',
                                                'event' => 'text-emerald-400 bg-emerald-500/10',
                                                'holiday' => 'text-amber-400 bg-amber-500/10',
                                                'urgent' => 'text-red-400 bg-red-500/10',
                                            ];
                                            $tc = $typeColors[$pinAnn->type] ?? 'text-white/50 bg-white/[0.06]';
                                        @endphp
                                        <span class="text-[10px] font-semibold {{ $tc }} px-2 py-0.5 rounded-full uppercase">{{ $pinAnn->type }}</span>
                                    </div>
                                    <h3 class="text-[15px] font-semibold text-white/85">{{ $pinAnn->title }}</h3>
                                    <p class="text-[13px] text-white/50 mt-1.5 line-clamp-2">{{ Str::limit(strip_tags($pinAnn->body), 200) }}</p>
                                    <div class="flex items-center gap-3 mt-3 text-[11px] text-white/30">
                                        <span>{{ $pinAnn->creator->name ?? 'System' }}</span>
                                        <span>&middot;</span>
                                        <span>{{ $pinAnn->published_at ? \Carbon\Carbon::parse($pinAnn->published_at)->diffForHumans() : 'Draft' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Activity Feed --}}
            <div class="space-y-3">
                <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest">Activity Feed</h2>

                @php
                    $feedItems = collect();

                    foreach ($announcements->where('is_pinned', false) as $ann) {
                        $feedItems->push((object)[
                            'type' => 'announcement',
                            'date' => $ann->published_at ?? $ann->created_at,
                            'data' => $ann,
                        ]);
                    }

                    foreach ($recognitions as $rec) {
                        $feedItems->push((object)[
                            'type' => 'recognition',
                            'date' => $rec->created_at,
                            'data' => $rec,
                        ]);
                    }

                    $feedItems = $feedItems->sortByDesc('date');
                @endphp

                @forelse($feedItems as $item)
                    @if($item->type === 'announcement')
                        @php $ann = $item->data; @endphp
                        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-white/[0.12] transition-colors">
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4.5 h-4.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        @php
                                            $tc2 = $typeColors[$ann->type] ?? 'text-white/50 bg-white/[0.06]';
                                        @endphp
                                        <span class="text-[10px] font-semibold {{ $tc2 }} px-2 py-0.5 rounded-full uppercase">{{ $ann->type }}</span>
                                    </div>
                                    <h3 class="text-[14px] font-semibold text-white/85">{{ $ann->title }}</h3>
                                    <p class="text-[13px] text-white/50 mt-1 line-clamp-2">{{ Str::limit(strip_tags($ann->body), 180) }}</p>
                                    <div class="flex items-center gap-3 mt-2.5 text-[11px] text-white/30">
                                        <span>{{ $ann->creator->name ?? 'System' }}</span>
                                        <span>&middot;</span>
                                        <span>{{ $ann->published_at ? \Carbon\Carbon::parse($ann->published_at)->diffForHumans() : '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        @php $rec = $item->data; @endphp
                        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-white/[0.12] transition-colors">
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-lg {{ $rec->type === 'badge' ? 'bg-amber-500/10' : ($rec->type === 'award' ? 'bg-purple-500/10' : 'bg-cyan-500/10') }} flex items-center justify-center shrink-0 mt-0.5">
                                    @if($rec->type === 'badge')
                                        <svg class="w-4.5 h-4.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    @elseif($rec->type === 'award')
                                        <svg class="w-4.5 h-4.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    @else
                                        <svg class="w-4.5 h-4.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] text-white/65">
                                        <span class="font-semibold text-white/80">{{ $rec->recognizer->name ?? 'Someone' }}</span>
                                        gave a
                                        <span class="font-semibold {{ $rec->type === 'badge' ? 'text-amber-400' : ($rec->type === 'award' ? 'text-purple-400' : 'text-cyan-400') }}">{{ $rec->type }}</span>
                                        to
                                        <span class="font-semibold text-white/80">{{ $rec->employeeProfile->user->name ?? 'someone' }}</span>
                                    </p>
                                    <h4 class="text-[14px] font-semibold text-white/85 mt-1">{{ $rec->title }}</h4>
                                    @if($rec->description)
                                        <p class="text-[13px] text-white/45 mt-1 line-clamp-2">{{ $rec->description }}</p>
                                    @endif
                                    <div class="flex items-center gap-3 mt-2.5 text-[11px] text-white/30">
                                        <span>{{ \Carbon\Carbon::parse($rec->created_at)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-12 text-center">
                        <svg class="w-10 h-10 text-white/15 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <p class="text-[14px] text-white/35 font-medium">No activity yet</p>
                        <p class="text-[12px] text-white/25 mt-1">Start by giving a recognition or creating an announcement</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-5">

            {{-- Upcoming Birthdays --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-pink-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.75 1.75 0 003 15.546M12 4v3m-2-1h4m-6 4h8l1 3H5l1-3z"/></svg>
                        </div>
                        <h3 class="text-[13px] font-semibold text-white/85">Birthdays</h3>
                    </div>
                    <a href="{{ route('hr.engagement.birthdays') }}" class="text-[11px] font-medium text-cyan-400/70 hover:text-cyan-400 transition-colors">View all</a>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($upcomingBirthdays->take(5) as $bd)
                        @php
                            $bdName = $bd->user->name ?? ($bd->first_name . ' ' . $bd->last_name);
                            $bdInitials = strtoupper(collect(explode(' ', $bdName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                            $bdDate = \Carbon\Carbon::parse($bd->date_of_birth);
                            $nextBd = $bdDate->copy()->year(now()->year);
                            if ($nextBd->isPast() && !$nextBd->isToday()) $nextBd->addYear();
                            $daysUntil = now()->startOfDay()->diffInDays($nextBd->startOfDay(), false);
                        @endphp
                        <div class="px-5 py-3 flex items-center gap-3 hover:bg-white/[0.02] transition-colors">
                            <div class="w-8 h-8 rounded-full bg-pink-500/15 text-pink-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                {{ $bdInitials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-medium text-white/75 truncate">{{ $bdName }}</p>
                                <p class="text-[10px] text-white/30">{{ $bdDate->format('M d') }}</p>
                            </div>
                            @if($daysUntil === 0)
                                <span class="text-[9px] font-bold text-pink-400 bg-pink-500/15 px-1.5 py-0.5 rounded-full shrink-0">TODAY</span>
                            @else
                                <span class="text-[10px] text-white/25 shrink-0">{{ $daysUntil }}d</span>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center">
                            <p class="text-[12px] text-white/25">No birthdays this week</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Upcoming Anniversaries --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <h3 class="text-[13px] font-semibold text-white/85">Anniversaries</h3>
                    </div>
                    <a href="{{ route('hr.engagement.anniversaries') }}" class="text-[11px] font-medium text-cyan-400/70 hover:text-cyan-400 transition-colors">View all</a>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($upcomingAnniversaries->take(5) as $anniv)
                        @php
                            $aName = $anniv->user->name ?? ($anniv->first_name . ' ' . $anniv->last_name);
                            $aInitials = strtoupper(collect(explode(' ', $aName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                            $joinDate = \Carbon\Carbon::parse($anniv->joining_date);
                            $years = $joinDate->diffInYears(now());
                            $nextAnniv = $joinDate->copy()->year(now()->year);
                            if ($nextAnniv->isPast() && !$nextAnniv->isToday()) { $nextAnniv->addYear(); $years = $joinDate->diffInYears($nextAnniv); }
                            $daysUntilAnniv = now()->startOfDay()->diffInDays($nextAnniv->startOfDay(), false);
                        @endphp
                        <div class="px-5 py-3 flex items-center gap-3 hover:bg-white/[0.02] transition-colors">
                            <div class="w-8 h-8 rounded-full bg-amber-500/15 text-amber-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                {{ $aInitials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] font-medium text-white/75 truncate">{{ $aName }}</p>
                                <p class="text-[10px] text-white/30">{{ $years }} {{ Str::plural('year', $years) }}</p>
                            </div>
                            @if($daysUntilAnniv === 0)
                                <span class="text-[9px] font-bold text-amber-400 bg-amber-500/15 px-1.5 py-0.5 rounded-full shrink-0">TODAY</span>
                            @else
                                <span class="text-[10px] text-white/25 shrink-0">{{ $daysUntilAnniv }}d</span>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center">
                            <p class="text-[12px] text-white/25">No anniversaries this week</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 space-y-3">
                <h3 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest">Quick Links</h3>
                <a href="{{ route('hr.engagement.birthdays') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/[0.04] transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-pink-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.75 1.75 0 003 15.546M12 4v3m-2-1h4m-6 4h8l1 3H5l1-3z"/></svg>
                    </div>
                    <span class="text-[13px] text-white/60 group-hover:text-white/80 font-medium">All Birthdays</span>
                </a>
                <a href="{{ route('hr.engagement.anniversaries') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/[0.04] transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <span class="text-[13px] text-white/60 group-hover:text-white/80 font-medium">All Anniversaries</span>
                </a>
                <a href="{{ route('hr.surveys.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/[0.04] transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                    </div>
                    <span class="text-[13px] text-white/60 group-hover:text-white/80 font-medium">Surveys</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Give Recognition Modal --}}
    <template x-if="showRecognitionModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showRecognitionModal = false"></div>
            <div class="relative bg-[#1A1A2E] border border-white/[0.10] rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-5"
                 @click.stop @keydown.escape.window="showRecognitionModal = false">

                <div class="flex items-center justify-between">
                    <h2 class="text-[18px] font-bold text-white/85">Give Recognition</h2>
                    <button @click="showRecognitionModal = false" class="p-1.5 rounded-lg hover:bg-white/[0.07] text-white/35 hover:text-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Success/Error Messages --}}
                <template x-if="successMsg">
                    <div class="flex items-center gap-2 p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[13px] text-emerald-400" x-text="successMsg"></span>
                    </div>
                </template>
                <template x-if="errorMsg">
                    <div class="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 border border-red-500/20">
                        <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-[13px] text-red-400" x-text="errorMsg"></span>
                    </div>
                </template>

                {{-- Employee ID --}}
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Employee Profile ID</label>
                    <input type="number" x-model="recognitionForm.employee_profile_id" placeholder="Enter employee profile ID"
                           class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08]"/>
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Recognition Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="t in ['badge', 'award', 'shoutout']" :key="t">
                            <button type="button" @click="recognitionForm.type = t"
                                    :class="recognitionForm.type === t ? 'border-cyan-500/50 bg-cyan-500/10 text-cyan-400' : 'border-white/[0.08] bg-white/[0.04] text-white/50 hover:bg-white/[0.07]'"
                                    class="px-3 py-2 rounded-lg border text-[12px] font-semibold capitalize transition-colors text-center">
                                <span x-text="t"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Title</label>
                    <input type="text" x-model="recognitionForm.title" placeholder="e.g. Outstanding Performance"
                           class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08]"/>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Description</label>
                    <textarea x-model="recognitionForm.description" rows="3" placeholder="Why are you recognizing this person?"
                              class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] resize-none"></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button @click="showRecognitionModal = false"
                            class="px-4 py-2 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/70 hover:bg-white/[0.06] transition-colors">
                        Cancel
                    </button>
                    <button @click="submitRecognition()"
                            :disabled="submitting || !recognitionForm.employee_profile_id || !recognitionForm.title"
                            :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                            class="px-5 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-40">
                        <span x-show="!submitting">Submit Recognition</span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

</x-layouts.hr>