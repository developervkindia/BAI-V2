<x-layouts.hr title="{{ $jobPosting->title }}" currentView="recruitment">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    showAddCandidate: false,
    candidateForm: {
        hr_job_posting_id: {{ $jobPosting->id }},
        name: '',
        email: '',
        phone: '',
        source: '',
        experience_years: '',
        expected_ctc: '',
        current_company: '',
        current_designation: '',
        notes: '',
    },
    submitting: false,
    errors: {},

    async addCandidate() {
        this.submitting = true;
        this.errors = {};

        try {
            const response = await fetch('/api/hr/candidates', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.candidateForm),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    this.errors = data.errors;
                } else {
                    this.errors = { general: [data.message || 'Failed to add candidate'] };
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
        this.candidateForm = {
            hr_job_posting_id: {{ $jobPosting->id }},
            name: '', email: '', phone: '', source: '', experience_years: '', expected_ctc: '', current_company: '', current_designation: '', notes: '',
        };
        this.errors = {};
    },
}">

    {{-- Page Header --}}
    <div class="flex items-start gap-4">
        <a href="{{ route('hr.recruitment.index') }}"
           class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] flex items-center justify-center transition-colors mt-1">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-[22px] font-bold text-white/85 tracking-tight">{{ $jobPosting->title }}</h1>
                @php
                    $statusColors = [
                        'draft' => 'text-white/50 bg-white/[0.06] border-white/[0.08]',
                        'open' => 'text-green-400 bg-green-500/10 border-green-500/20',
                        'on_hold' => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
                        'closed' => 'text-red-400 bg-red-500/10 border-red-500/20',
                    ];
                    $sc = $statusColors[$jobPosting->status] ?? $statusColors['draft'];
                @endphp
                <span class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $sc }}">
                    {{ str_replace('_', ' ', $jobPosting->status) }}
                </span>
            </div>
            <div class="flex items-center gap-4 mt-2 flex-wrap">
                @if($jobPosting->department)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span class="text-[13px] text-white/50">{{ $jobPosting->department->name }}</span>
                    </div>
                @endif
                @php
                    $typeLabel = str_replace('_', ' ', $jobPosting->employment_type);
                @endphp
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-[13px] text-white/50 capitalize">{{ $typeLabel }}</span>
                </div>
                @if($jobPosting->location)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-[13px] text-white/50">{{ $jobPosting->location }}</span>
                    </div>
                @endif
                @if($jobPosting->salary_range_min || $jobPosting->salary_range_max)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-[13px] text-white/50 tabular-nums">
                            @if($jobPosting->salary_range_min && $jobPosting->salary_range_max)
                                {{ number_format($jobPosting->salary_range_min) }} - {{ number_format($jobPosting->salary_range_max) }}
                            @elseif($jobPosting->salary_range_min)
                                From {{ number_format($jobPosting->salary_range_min) }}
                            @else
                                Up to {{ number_format($jobPosting->salary_range_max) }}
                            @endif
                        </span>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <span class="text-[13px] text-white/50">{{ $jobPosting->positions }} {{ Str::plural('position', $jobPosting->positions) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('hr.recruitment.pipeline', $jobPosting) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/[0.04] hover:bg-white/[0.06] text-white/60 hover:text-white/80 text-[13px] font-medium rounded-lg border border-white/[0.06] transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                View Pipeline
            </a>
        </div>
        <button @click="resetForm(); showAddCandidate = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-400 text-[13px] font-semibold rounded-lg border border-cyan-500/20 hover:border-cyan-500/30 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Add Candidate
        </button>
    </div>

    {{-- Candidates Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">Candidates</h2>
                <p class="text-[12px] text-white/35 mt-0.5">{{ $jobPosting->candidates->count() }} total candidates</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Name</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Email</th>
                        <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Stage</th>
                        <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Experience</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Expected CTC</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Source</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Applied</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($jobPosting->candidates as $candidate)
                        @php
                            $stageColors = [
                                'applied' => 'text-white/50 bg-white/[0.06] border-white/[0.08]',
                                'screening' => 'text-amber-400 bg-amber-500/10 border-amber-500/20',
                                'interview' => 'text-blue-400 bg-blue-500/10 border-blue-500/20',
                                'assessment' => 'text-purple-400 bg-purple-500/10 border-purple-500/20',
                                'offer' => 'text-cyan-400 bg-cyan-500/10 border-cyan-500/20',
                                'hired' => 'text-green-400 bg-green-500/10 border-green-500/20',
                                'rejected' => 'text-red-400 bg-red-500/10 border-red-500/20',
                            ];
                            $sgc = $stageColors[$candidate->stage] ?? $stageColors['applied'];
                            $initials = strtoupper(collect(explode(' ', $candidate->name))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                        @endphp
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-cyan-500/15 text-cyan-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <span class="text-[13px] font-medium text-white/80">{{ $candidate->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/50">{{ $candidate->email }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border {{ $sgc }}">
                                    {{ $candidate->stage }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-[12px] text-white/55 tabular-nums">
                                    {{ $candidate->experience_years ? $candidate->experience_years . ' yrs' : '---' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-[12px] text-white/55 tabular-nums">
                                    {{ $candidate->expected_ctc ? number_format($candidate->expected_ctc) : '---' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/45 capitalize">{{ $candidate->source ?? '---' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/40 tabular-nums">{{ $candidate->created_at->format('M d, Y') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-14 h-14 rounded-2xl bg-white/[0.04] flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-[14px] font-medium text-white/40 mb-1">No candidates yet</p>
                                    <p class="text-[12px] text-white/25">Add your first candidate to this posting</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Candidate Modal --}}
    <template x-if="showAddCandidate">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showAddCandidate = false">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showAddCandidate = false"></div>
            <div class="relative bg-[#17172A] border border-white/[0.10] rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto" @click.stop>

                <div class="px-6 py-5 border-b border-white/[0.06] sticky top-0 bg-[#17172A] z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-[16px] font-semibold text-white/85">Add Candidate</h3>
                            <p class="text-[13px] text-white/40 mt-0.5">Add a new candidate to {{ $jobPosting->title }}</p>
                        </div>
                        <button @click="showAddCandidate = false" class="w-8 h-8 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- General Error --}}
                    <template x-if="errors.general">
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
                            <template x-for="err in errors.general" :key="err">
                                <p class="text-[12px] text-red-400" x-text="err"></p>
                            </template>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Full Name</label>
                            <input type="text" x-model="candidateForm.name" placeholder="Candidate's full name"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                            <template x-if="errors.name">
                                <p class="text-[12px] text-red-400 mt-1" x-text="errors.name[0]"></p>
                            </template>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Email</label>
                            <input type="email" x-model="candidateForm.email" placeholder="candidate@email.com"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                            <template x-if="errors.email">
                                <p class="text-[12px] text-red-400 mt-1" x-text="errors.email[0]"></p>
                            </template>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Phone</label>
                            <input type="tel" x-model="candidateForm.phone" placeholder="+91 98765 43210"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Source</label>
                            <select x-model="candidateForm.source"
                                    class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/80 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors appearance-none">
                                <option value="" class="bg-[#17172A]">Select source</option>
                                <option value="linkedin" class="bg-[#17172A]">LinkedIn</option>
                                <option value="referral" class="bg-[#17172A]">Referral</option>
                                <option value="job_portal" class="bg-[#17172A]">Job Portal</option>
                                <option value="campus" class="bg-[#17172A]">Campus</option>
                                <option value="direct" class="bg-[#17172A]">Direct</option>
                                <option value="agency" class="bg-[#17172A]">Agency</option>
                                <option value="other" class="bg-[#17172A]">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Experience (Years)</label>
                            <input type="number" x-model.number="candidateForm.experience_years" placeholder="0" min="0" step="0.5"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Expected CTC</label>
                            <input type="number" x-model.number="candidateForm.expected_ctc" placeholder="0" min="0"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Current Company</label>
                            <input type="text" x-model="candidateForm.current_company" placeholder="Company name"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Current Designation</label>
                            <input type="text" x-model="candidateForm.current_designation" placeholder="Job title"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Notes</label>
                        <textarea x-model="candidateForm.notes" rows="3" placeholder="Additional notes about the candidate..."
                                  class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors resize-none"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-end gap-3 sticky bottom-0 bg-[#17172A]">
                    <button @click="showAddCandidate = false"
                            class="px-5 py-2.5 text-[13px] font-medium text-white/45 hover:text-white/65 bg-white/[0.04] hover:bg-white/[0.06] rounded-lg border border-white/[0.06] transition-colors">
                        Cancel
                    </button>
                    <button @click="addCandidate()" :disabled="submitting"
                            class="px-6 py-2.5 text-[13px] font-semibold text-white bg-cyan-500/80 hover:bg-cyan-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2">
                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="submitting ? 'Adding...' : 'Add Candidate'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

</x-layouts.hr>
