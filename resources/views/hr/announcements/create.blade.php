<x-layouts.hr title="New Announcement" currentView="announcements">

<div class="p-5 lg:p-7 space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('hr.announcements.index') }}" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">New Announcement</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Create a company-wide announcement</p>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 space-y-1">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[13px] text-red-400">{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('hr.announcements.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Main Form Card --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-6 space-y-5">

            {{-- Title --}}
            <div>
                <label for="title" class="block text-[12px] font-semibold text-white/50 mb-1.5">Title <span class="text-red-400">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Announcement title" required
                       class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08]"/>
            </div>

            {{-- Body --}}
            <div>
                <label for="body" class="block text-[12px] font-semibold text-white/50 mb-1.5">Body <span class="text-red-400">*</span></label>
                <textarea id="body" name="body" rows="10" placeholder="Write your announcement content here..." required
                          class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] resize-y leading-relaxed">{{ old('body') }}</textarea>
                <p class="text-[11px] text-white/20 mt-1">You can use plain text with line breaks. Content will be displayed as-is.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Type --}}
                <div>
                    <label for="type" class="block text-[12px] font-semibold text-white/50 mb-1.5">Type <span class="text-red-400">*</span></label>
                    <select id="type" name="type" required
                            class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer">
                        <option value="general" {{ old('type') === 'general' ? 'selected' : '' }}>General</option>
                        <option value="policy" {{ old('type') === 'policy' ? 'selected' : '' }}>Policy</option>
                        <option value="event" {{ old('type') === 'event' ? 'selected' : '' }}>Event</option>
                        <option value="holiday" {{ old('type') === 'holiday' ? 'selected' : '' }}>Holiday</option>
                        <option value="urgent" {{ old('type') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                {{-- Published At --}}
                <div>
                    <label for="published_at" class="block text-[12px] font-semibold text-white/50 mb-1.5">Publish Date</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40"/>
                </div>

                {{-- Is Pinned --}}
                <div x-data="{ pinned: {{ old('is_pinned') ? 'true' : 'false' }} }">
                    <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Pin Announcement</label>
                    <input type="hidden" name="is_pinned" :value="pinned ? '1' : '0'">
                    <button type="button" @click="pinned = !pinned"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] w-full">
                        <div class="relative w-9 h-5 rounded-full transition-colors"
                             :class="pinned ? 'bg-cyan-500' : 'bg-white/[0.12]'">
                            <div class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                                 :class="pinned ? 'translate-x-4' : 'translate-x-0.5'"></div>
                        </div>
                        <span class="text-[13px] text-white/60" x-text="pinned ? 'Pinned' : 'Not Pinned'"></span>
                    </button>
                </div>
            </div>

            {{-- Target Departments --}}
            <div x-data="{ departments: {{ json_encode(old('target_departments', [])) }}, newDept: '' }">
                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Target Departments <span class="text-[10px] text-white/25 font-normal">(optional - leave empty for all)</span></label>
                <div class="flex items-center gap-2 mb-2">
                    <input type="text" x-model="newDept" placeholder="Department name" @keydown.enter.prevent="if (newDept.trim()) { departments.push(newDept.trim()); newDept = ''; }"
                           class="flex-1 px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08]"/>
                    <button type="button" @click="if (newDept.trim()) { departments.push(newDept.trim()); newDept = ''; }"
                            class="px-3 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[12px] text-white/50 hover:text-white/70 hover:bg-white/[0.10] transition-colors font-medium">
                        Add
                    </button>
                </div>
                <template x-for="(dept, idx) in departments" :key="idx">
                    <div class="inline-flex items-center gap-1.5 mr-2 mb-2 px-2.5 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20">
                        <input type="hidden" name="target_departments[]" :value="dept">
                        <span class="text-[11px] text-cyan-400 font-medium" x-text="dept"></span>
                        <button type="button" @click="departments.splice(idx, 1)" class="text-cyan-400/50 hover:text-cyan-400 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('hr.announcements.index') }}"
               class="px-5 py-2.5 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/70 hover:bg-white/[0.06] transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="flex items-center gap-2 px-6 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Publish Announcement
            </button>
        </div>

    </form>

</div>

</x-layouts.hr>