<x-layouts.org-management :organization="$organization" activeTab="members">

<div x-data="{ activeTab: 'overview' }">

    {{-- Back + Header --}}
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('users.index', $organization) }}" class="text-white/30 hover:text-white/55 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span class="text-[11px] text-white/25 uppercase tracking-wider">Member Profile</span>
    </div>

    {{-- User Header Card --}}
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500/25 to-violet-600/25 flex items-center justify-center text-indigo-400 text-[20px] font-bold flex-shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-[20px] font-semibold text-white/85">{{ $user->name }}</h1>
                    <p class="text-white/40 text-[13px] mt-0.5">{{ $user->email }}</p>
                    <div class="flex items-center gap-2 mt-2.5">
                        @if($user->system_role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium
                                {{ $user->system_role === 'admin' ? 'bg-violet-500/15 text-violet-400' : 'bg-blue-500/15 text-blue-400' }}">
                                {{ ucfirst($user->system_role) }}
                            </span>
                        @endif
                        @foreach($user->roles ?? [] as $role)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-500/15 text-indigo-400">
                                {{ $role->name }}
                            </span>
                        @endforeach
                        @php
                            $status = $user->status ?? 'active';
                            $statusClasses = match($status) {
                                'active' => 'bg-green-500/15 text-green-400',
                                'inactive' => 'bg-red-500/15 text-red-400',
                                'on_leave' => 'bg-amber-500/15 text-amber-400',
                                default => 'bg-white/10 text-white/50',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[11px] font-medium {{ $statusClasses }}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                            {{ str_replace('_', ' ', ucfirst($status)) }}
                        </span>
                    </div>
                </div>
            </div>
            <a href="{{ route('users.edit', [$organization, $user]) }}"
               class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-white/[0.07] mb-6">
        <nav class="flex gap-1 -mb-px">
            @foreach(['overview' => 'Overview', 'education' => 'Education', 'experience' => 'Experience', 'documents' => 'Documents', 'assets' => 'Assets', 'skills' => 'Skills'] as $tab => $label)
                <button @click="activeTab = '{{ $tab }}'"
                        :class="activeTab === '{{ $tab }}' ? 'text-indigo-400 border-indigo-400' : 'text-white/40 border-transparent hover:text-white/60'"
                        class="px-4 py-3 text-[13px] font-medium border-b-2 transition">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab: Overview --}}
    <div x-show="activeTab === 'overview'" x-cloak>
        @php $profile = $user->employeeProfiles->first(); @endphp
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <h3 class="text-[14px] font-semibold text-white/85 mb-4">Employment Information</h3>
                <div class="space-y-3">
                    @foreach([
                        'Employee ID' => $profile->employee_id ?? '-',
                        'Designation' => $profile->designation ?? '-',
                        'Department' => $profile->department ?? '-',
                        'Date of Joining' => $profile->date_of_joining ? \Carbon\Carbon::parse($profile->date_of_joining)->format('M d, Y') : '-',
                        'Employment Type' => ucfirst($profile->employment_type ?? '-'),
                        'Reporting Manager' => $profile->manager->user->name ?? '-',
                        'Work Location' => $profile->work_location ?? '-',
                        'Shift' => $profile->shift ?? '-',
                    ] as $label => $value)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-[12px] text-white/35">{{ $label }}</span>
                            <span class="text-[13px] text-white/65 font-medium">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <h3 class="text-[14px] font-semibold text-white/85 mb-4">Personal Information</h3>
                <div class="space-y-3">
                    @foreach([
                        'Phone' => $profile->phone ?? '-',
                        'Personal Email' => $profile->personal_email ?? '-',
                        'Date of Birth' => $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('M d, Y') : '-',
                        'Gender' => ucfirst($profile->gender ?? '-'),
                        'Marital Status' => ucfirst($profile->marital_status ?? '-'),
                        'Blood Group' => $profile->blood_group ?? '-',
                        'Nationality' => $profile->nationality ?? '-',
                    ] as $label => $value)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-[12px] text-white/35">{{ $label }}</span>
                            <span class="text-[13px] text-white/65 font-medium">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <h3 class="text-[14px] font-semibold text-white/85 mb-4">Emergency Contact</h3>
                <div class="space-y-3">
                    @foreach([
                        'Contact Name' => $profile->emergency_contact_name ?? '-',
                        'Relationship' => $profile->emergency_contact_relationship ?? '-',
                        'Phone' => $profile->emergency_contact_phone ?? '-',
                    ] as $label => $value)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-[12px] text-white/35">{{ $label }}</span>
                            <span class="text-[13px] text-white/65 font-medium">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <h3 class="text-[14px] font-semibold text-white/85 mb-4">Address & Banking</h3>
                <div class="space-y-3">
                    <div class="py-1.5">
                        <span class="text-[12px] text-white/35 block mb-1">Current Address</span>
                        <span class="text-[13px] text-white/65">{{ $profile->current_address ?? '-' }}</span>
                    </div>
                    <div class="py-1.5">
                        <span class="text-[12px] text-white/35 block mb-1">Permanent Address</span>
                        <span class="text-[13px] text-white/65">{{ $profile->permanent_address ?? '-' }}</span>
                    </div>
                    @foreach([
                        'Bank Name' => $profile->bank_name ?? '-',
                        'Account Number' => $profile->account_number ? '****' . substr($profile->account_number, -4) : '-',
                        'IFSC Code' => $profile->ifsc_code ?? '-',
                        'Branch' => $profile->bank_branch ?? '-',
                    ] as $label => $value)
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-[12px] text-white/35">{{ $label }}</span>
                            <span class="text-[13px] text-white/65 font-medium">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Tab: Education --}}
    <div x-show="activeTab === 'education'" x-cloak>
        <div class="space-y-4">
            @forelse($user->employeeProfiles->first()->education ?? [] as $edu)
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                    <h4 class="text-[14px] font-semibold text-white/80">{{ $edu->degree }}</h4>
                    <p class="text-[13px] text-white/50 mt-0.5">{{ $edu->institution }}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-[12px] text-white/35">{{ $edu->start_year }} - {{ $edu->end_year ?? 'Present' }}</span>
                        @if($edu->grade)
                            <span class="text-[12px] text-indigo-400/70 bg-indigo-500/10 px-2 py-0.5 rounded-md">Grade: {{ $edu->grade }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-10 text-center">
                    <p class="text-white/30 text-[13px]">No education records found.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Tab: Experience --}}
    <div x-show="activeTab === 'experience'" x-cloak>
        <div class="space-y-4">
            @forelse($user->employeeProfiles->first()->experience ?? [] as $exp)
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                    <h4 class="text-[14px] font-semibold text-white/80">{{ $exp->title }}</h4>
                    <p class="text-[13px] text-white/50 mt-0.5">{{ $exp->company }}</p>
                    <span class="text-[12px] text-white/35 mt-1 block">
                        {{ \Carbon\Carbon::parse($exp->start_date)->format('M Y') }} -
                        {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : 'Present' }}
                    </span>
                    @if($exp->description)
                        <p class="text-[12px] text-white/40 mt-2 leading-relaxed">{{ $exp->description }}</p>
                    @endif
                </div>
            @empty
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-10 text-center">
                    <p class="text-white/30 text-[13px]">No experience records found.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Tab: Documents --}}
    <div x-show="activeTab === 'documents'" x-cloak>
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl overflow-hidden">
            @forelse($user->employeeProfiles->first()->documents ?? [] as $doc)
                <div class="flex items-center justify-between px-5 py-4 {{ !$loop->last ? 'border-b border-white/[0.04]' : '' }}">
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-violet-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[13px] font-medium text-white/70">{{ $doc->name }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-cyan-500/15 text-cyan-400 uppercase">{{ $doc->type }}</span>
                            </div>
                            <div class="flex items-center gap-3 mt-0.5">
                                @if($doc->document_number)
                                    <span class="text-[12px] text-white/30">{{ Str::mask($doc->document_number, '*', 0, -4) }}</span>
                                @endif
                                @if($doc->expiry_date)
                                    <span class="text-[12px] {{ \Carbon\Carbon::parse($doc->expiry_date)->isPast() ? 'text-red-400/60' : 'text-white/30' }}">
                                        Expires: {{ \Carbon\Carbon::parse($doc->expiry_date)->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($doc->file_path)
                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 border border-white/[0.08] text-white/40 hover:text-white/65 hover:border-white/[0.15] rounded-lg px-3 py-1.5 text-[11px] font-medium transition">
                            Download
                        </a>
                    @endif
                </div>
            @empty
                <div class="p-10 text-center">
                    <p class="text-white/30 text-[13px]">No documents uploaded.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Tab: Assets --}}
    <div x-show="activeTab === 'assets'" x-cloak>
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl overflow-hidden">
            @forelse($user->employeeProfiles->first()->assets ?? [] as $asset)
                <div class="flex items-center justify-between px-5 py-4 {{ !$loop->last ? 'border-b border-white/[0.04]' : '' }}">
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[13px] font-medium text-white/70">{{ $asset->name }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-500/15 text-amber-400 uppercase">{{ $asset->type }}</span>
                            </div>
                            <div class="flex items-center gap-3 mt-0.5">
                                @if($asset->asset_tag)
                                    <span class="text-[12px] text-white/30">Tag: {{ $asset->asset_tag }}</span>
                                @endif
                                @if($asset->serial_number)
                                    <span class="text-[12px] text-white/30">S/N: {{ $asset->serial_number }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[12px] text-white/35">
                            Assigned: {{ $asset->assigned_date ? \Carbon\Carbon::parse($asset->assigned_date)->format('M d, Y') : '-' }}
                        </div>
                        @if($asset->return_date)
                            <div class="text-[12px] text-white/25">
                                Returned: {{ \Carbon\Carbon::parse($asset->return_date)->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-10 text-center">
                    <p class="text-white/30 text-[13px]">No assets assigned.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Tab: Skills --}}
    <div x-show="activeTab === 'skills'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <h3 class="text-[14px] font-semibold text-white/85 mb-4">Skills</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($user->employeeProfiles->first()->skills ?? [] as $skill)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-500/10 text-indigo-400 text-[12px] font-medium border border-indigo-500/15">
                            {{ $skill->name }}
                            @if($skill->proficiency_level)
                                <span class="ml-1.5 text-[10px] text-indigo-400/50">{{ $skill->proficiency_level }}</span>
                            @endif
                        </span>
                    @empty
                        <p class="text-white/30 text-[13px]">No skills listed.</p>
                    @endforelse
                </div>
            </div>
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <h3 class="text-[14px] font-semibold text-white/85 mb-4">Certifications</h3>
                <div class="space-y-3">
                    @forelse($user->employeeProfiles->first()->skills->where("category", "certification") ?? [] as $cert)
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                            <div>
                                <div class="text-[13px] font-medium text-white/70">{{ $cert->name }}</div>
                                <div class="text-[12px] text-white/35 mt-0.5">{{ $cert->issuer }}</div>
                                <div class="text-[11px] text-white/25 mt-0.5">
                                    {{ $cert->issued_date ? \Carbon\Carbon::parse($cert->issued_date)->format('M Y') : '' }}
                                    @if($cert->expiry_date) - {{ \Carbon\Carbon::parse($cert->expiry_date)->format('M Y') }} @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-white/30 text-[13px]">No certifications listed.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

</x-layouts.org-management>
