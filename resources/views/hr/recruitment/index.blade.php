<x-layouts.hr title="Job Postings" currentView="recruitment">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    showCreateModal: false,
    form: {
        title: '',
        department_id: '',
        employment_type: 'full_time',
        location: '',
        description: '',
        requirements: '',
        positions: 1,
    },
    submitting: false,
    errors: {},

    async createPosting() {
        this.submitting = true;
        this.errors = {};

        try {
            const response = await fetch('/api/hr/job-postings', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.form),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    this.errors = data.errors;
                } else {
                    this.errors = { general: [data.message || 'Failed to create posting'] };
                }
                return;
            }

            window.location.reload();
        } catch (err) {
            this.errors = { general: ['Network error. Please try again.'] };
        } finally {
            this.submitting = false;
        }
    },

    resetForm() {
        this.form = { title: '', department_id: '', employment_type: 'full_time', location: '', description: '', requirements: '', positions: 1 };
        this.errors = {};
    },
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Job Postings</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Manage open positions and recruitment pipeline</p>
        </div>
        <button @click="resetForm(); showCreateModal = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-400 text-[13px] font-semibold rounded-lg border border-cyan-500/20 hover:border-cyan-500/30 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Posting
        </button>
    </div>

    {{-- Job Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($postings as $posting)
            @php
                $statusColors = [
                    'draft' => 'text-white/50 bg-white/[0.06] border-white/[0.08]',
                    'open' => 'text-green-400 bg-green-500/10 border-green-500/20',
                    'on_hold' => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
                    'closed' => 'text-red-400 bg-red-500/10 border-red-500/20',
                ];
                $sc = $statusColors[$posting->status] ?? $statusColors['draft'];

                $typeColors = [
                    'full_time' => 'text-cyan-400 bg-cyan-500/10 border-cyan-500/20',
                    'part_time' => 'text-violet-400 bg-violet-500/10 border-violet-500/20',
                    'contract' => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
                    'intern' => 'text-pink-400 bg-pink-500/10 border-pink-500/20',
                ];
                $tc = $typeColors[$posting->employment_type] ?? 'text-white/50 bg-white/[0.06] border-white/[0.08]';
                $typeLabel = str_replace('_', ' ', $posting->employment_type);
                $candidatesCount = $posting->candidates_count ?? 0;
            @endphp
            <a href="{{ route('hr.recruitment.show-posting', $posting) }}"
               class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group block">

                <div class="flex items-start justify-between gap-3 mb-3">
                    <h3 class="text-[15px] font-semibold text-white/85 group-hover:text-white/95 transition-colors leading-snug">{{ $posting->title }}</h3>
                    <span class="inline-flex px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded-full border shrink-0 {{ $sc }}">
                        {{ str_replace('_', ' ', $posting->status) }}
                    </span>
                </div>

                <div class="space-y-2.5 mb-4">
                    @if($posting->department)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-white/25 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="text-[12px] text-white/50">{{ $posting->department->name }}</span>
                        </div>
                    @endif
                    @if($posting->location)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-white/25 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-[12px] text-white/50">{{ $posting->location }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-wrap mb-4">
                    <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-md border capitalize {{ $tc }}">
                        {{ $typeLabel }}
                    </span>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-white/[0.05]">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-[12px] text-white/40">
                            <span class="text-white/65 font-semibold">{{ $candidatesCount }}</span> candidates
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-[12px] text-white/40">
                            <span class="text-white/65 font-semibold">{{ $posting->positions }}</span> {{ Str::plural('position', $posting->positions) }}
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <div class="bg-[#17172A] border border-white/[0.07] rounded-xl px-8 py-20 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl bg-white/[0.04] flex items-center justify-center mb-5">
                            <svg class="w-8 h-8 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-[16px] font-medium text-white/50 mb-1">No job postings yet</p>
                        <p class="text-[13px] text-white/30 mb-5">Create your first job posting to start recruiting</p>
                        <button @click="resetForm(); showCreateModal = true"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-cyan-500/15 text-cyan-400 text-[12px] font-semibold rounded-lg border border-cyan-500/20 hover:bg-cyan-500/25 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Posting
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($postings->hasPages())
        <div class="flex items-center justify-between">
            <span class="text-[12px] text-white/30">
                Showing {{ $postings->firstItem() }} to {{ $postings->lastItem() }} of {{ $postings->total() }}
            </span>
            <div class="flex items-center gap-1.5">
                @if($postings->onFirstPage())
                    <span class="px-3 py-1.5 text-[12px] text-white/20 rounded-lg cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $postings->previousPageUrl() }}" class="px-3 py-1.5 text-[12px] text-white/50 hover:text-white/70 hover:bg-white/[0.04] rounded-lg transition-colors">Previous</a>
                @endif
                @foreach($postings->getUrlRange(max(1, $postings->currentPage() - 2), min($postings->lastPage(), $postings->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}"
                       class="w-8 h-8 flex items-center justify-center text-[12px] rounded-lg transition-colors {{ $page == $postings->currentPage() ? 'bg-cyan-500/15 text-cyan-400 font-semibold' : 'text-white/40 hover:text-white/60 hover:bg-white/[0.04]' }}">
                        {{ $page }}
                    </a>
                @endforeach
                @if($postings->hasMorePages())
                    <a href="{{ $postings->nextPageUrl() }}" class="px-3 py-1.5 text-[12px] text-white/50 hover:text-white/70 hover:bg-white/[0.04] rounded-lg transition-colors">Next</a>
                @else
                    <span class="px-3 py-1.5 text-[12px] text-white/20 rounded-lg cursor-not-allowed">Next</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Create Modal --}}
    <template x-if="showCreateModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showCreateModal = false">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCreateModal = false"></div>
            <div class="relative bg-[#17172A] border border-white/[0.10] rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto" @click.stop>

                <div class="px-6 py-5 border-b border-white/[0.06] sticky top-0 bg-[#17172A] z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-[16px] font-semibold text-white/85">Create Job Posting</h3>
                            <p class="text-[13px] text-white/40 mt-0.5">Add a new open position</p>
                        </div>
                        <button @click="showCreateModal = false" class="w-8 h-8 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Form --}}
                <div class="px-6 py-5 space-y-5">
                    {{-- General Error --}}
                    <template x-if="errors.general">
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
                            <template x-for="err in errors.general" :key="err">
                                <p class="text-[12px] text-red-400" x-text="err"></p>
                            </template>
                        </div>
                    </template>

                    {{-- Title --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Job Title</label>
                        <input type="text" x-model="form.title" placeholder="e.g., Senior Software Engineer"
                               class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        <template x-if="errors.title">
                            <p class="text-[12px] text-red-400 mt-1" x-text="errors.title[0]"></p>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Department --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Department</label>
                            <select x-model="form.department_id"
                                    class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/80 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors appearance-none">
                                <option value="" class="bg-[#17172A]">Select department</option>
                                @foreach(\App\Models\HrDepartment::where('organization_id', auth()->user()->organization_id ?? 0)->get() as $dept)
                                    <option value="{{ $dept->id }}" class="bg-[#17172A]">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <template x-if="errors.department_id">
                                <p class="text-[12px] text-red-400 mt-1" x-text="errors.department_id[0]"></p>
                            </template>
                        </div>

                        {{-- Employment Type --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Employment Type</label>
                            <select x-model="form.employment_type"
                                    class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/80 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors appearance-none">
                                <option value="full_time" class="bg-[#17172A]">Full Time</option>
                                <option value="part_time" class="bg-[#17172A]">Part Time</option>
                                <option value="contract" class="bg-[#17172A]">Contract</option>
                                <option value="intern" class="bg-[#17172A]">Intern</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Location --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Location</label>
                            <input type="text" x-model="form.location" placeholder="e.g., Bangalore, Remote"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>

                        {{-- Positions --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">No. of Positions</label>
                            <input type="number" x-model.number="form.positions" min="1" placeholder="1"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Job Description</label>
                        <textarea x-model="form.description" rows="4" placeholder="Describe the role and responsibilities..."
                                  class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors resize-none"></textarea>
                    </div>

                    {{-- Requirements --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Requirements</label>
                        <textarea x-model="form.requirements" rows="3" placeholder="Required skills, experience, qualifications..."
                                  class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors resize-none"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-end gap-3 sticky bottom-0 bg-[#17172A]">
                    <button @click="showCreateModal = false"
                            class="px-5 py-2.5 text-[13px] font-medium text-white/45 hover:text-white/65 bg-white/[0.04] hover:bg-white/[0.06] rounded-lg border border-white/[0.06] transition-colors">
                        Cancel
                    </button>
                    <button @click="createPosting()" :disabled="submitting"
                            class="px-6 py-2.5 text-[13px] font-semibold text-white bg-cyan-500/80 hover:bg-cyan-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2">
                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="submitting ? 'Creating...' : 'Create Posting'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

</x-layouts.hr>
