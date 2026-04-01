<x-layouts.hr title="{{ $employee->user->name ?? 'Employee' }}" currentView="people">

<div class="p-5 lg:p-7 space-y-6" x-data="{ activeTab: 'personal' }">

    {{-- Back Link --}}
    <a href="{{ route('hr.people.index') }}" class="inline-flex items-center gap-1.5 text-sm text-white/40 hover:text-white/65 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Directory
    </a>

    {{-- Employee Header --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-6">
        <div class="flex flex-col sm:flex-row items-start gap-5">
            {{-- Large Avatar --}}
            <div class="w-20 h-20 rounded-2xl prod-bg-muted prod-text text-2xl font-bold flex items-center justify-center shrink-0">
                {{ strtoupper(substr($employee->user->name ?? 'E', 0, 2)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                    <h1 class="text-2xl font-bold text-white/90">{{ $employee->user->name ?? 'Employee' }}</h1>
                    @php
                        $statusColors = [
                            'active'   => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                            'inactive' => 'bg-red-500/15 text-red-400 border-red-500/20',
                            'on_leave' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                        ];
                        $sColor = $statusColors[$employee->status] ?? $statusColors['active'];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium border {{ $sColor }} w-fit">
                        {{ ucfirst(str_replace('_', ' ', $employee->status)) }}
                    </span>
                </div>
                <p class="text-sm text-white/55 mt-1">{{ $employee->hrDesignation->name ?? $employee->designation ?? 'No designation' }}</p>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 mt-3 text-xs text-white/40">
                    @if($employee->department)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $employee->department }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        {{ $employee->employee_id }}
                    </span>
                    @if($employee->user)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $employee->user->email }}
                        </span>
                    @endif
                    @if($employee->phone)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $employee->phone }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="border-b border-white/[0.07] -mx-5 lg:-mx-7 px-5 lg:px-7">
        <nav class="flex gap-0 overflow-x-auto scrollbar-none">
            @foreach(['personal' => 'Personal', 'employment' => 'Employment', 'education' => 'Education', 'experience' => 'Experience', 'documents' => 'Documents', 'assets' => 'Assets', 'skills' => 'Skills'] as $key => $label)
                <button @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'text-cyan-400 border-cyan-400' : 'text-white/40 border-transparent hover:text-white/60 hover:border-white/20'"
                        class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap -mb-px">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab Content --}}

    {{-- PERSONAL TAB --}}
    <div x-show="activeTab === 'personal'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Basic Info --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                <h3 class="text-sm font-semibold text-white/70 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 prod-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Basic Information
                </h3>
                <div class="space-y-3">
                    @foreach([
                        'Date of Birth' => $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('M d, Y') : '-',
                        'Gender' => $employee->gender ? ucfirst($employee->gender) : '-',
                        'Blood Group' => $employee->blood_group ?? '-',
                        'Marital Status' => $employee->marital_status ? ucfirst($employee->marital_status) : '-',
                        'Nationality' => $employee->nationality ?? '-',
                    ] as $label => $value)
                        <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04] last:border-0">
                            <span class="text-xs text-white/35">{{ $label }}</span>
                            <span class="text-sm text-white/70">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Address --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                <h3 class="text-sm font-semibold text-white/70 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 prod-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Address
                </h3>
                <div class="space-y-3">
                    @foreach([
                        'Address Line 1' => $employee->address_line_1 ?? '-',
                        'Address Line 2' => $employee->address_line_2 ?? '-',
                        'City' => $employee->city ?? '-',
                        'State' => $employee->state ?? '-',
                        'Country' => $employee->country ?? '-',
                        'Postal Code' => $employee->postal_code ?? '-',
                    ] as $label => $value)
                        <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04] last:border-0">
                            <span class="text-xs text-white/35">{{ $label }}</span>
                            <span class="text-sm text-white/70">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Emergency Contact --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                <h3 class="text-sm font-semibold text-white/70 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 prod-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Emergency Contact
                </h3>
                <div class="space-y-3">
                    @foreach([
                        'Contact Name' => $employee->emergency_contact_name ?? '-',
                        'Phone' => $employee->emergency_contact_phone ?? '-',
                        'Relation' => $employee->emergency_contact_relation ? ucfirst($employee->emergency_contact_relation) : '-',
                    ] as $label => $value)
                        <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04] last:border-0">
                            <span class="text-xs text-white/35">{{ $label }}</span>
                            <span class="text-sm text-white/70">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bank / ID Details --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                <h3 class="text-sm font-semibold text-white/70 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 prod-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Bank & ID Details
                </h3>
                <div class="space-y-3">
                    @foreach([
                        'Bank Name' => $employee->bank_name ?? '-',
                        'Account Number' => $employee->bank_account_number ? '****' . substr($employee->bank_account_number, -4) : '-',
                        'IFSC Code' => $employee->ifsc_code ?? '-',
                        'PAN Number' => $employee->pan_number ?? '-',
                        'Aadhar Number' => $employee->aadhar_number ? '****-****-' . substr($employee->aadhar_number, -4) : '-',
                    ] as $label => $value)
                        <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04] last:border-0">
                            <span class="text-xs text-white/35">{{ $label }}</span>
                            <span class="text-sm text-white/70 font-mono">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- EMPLOYMENT TAB --}}
    <div x-show="activeTab === 'employment'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                <h3 class="text-sm font-semibold text-white/70 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 prod-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Employment Details
                </h3>
                <div class="space-y-3">
                    @foreach([
                        'Employee ID' => $employee->employee_id ?? '-',
                        'Joining Date' => $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('M d, Y') : '-',
                        'Probation End' => $employee->probation_end_date ? \Carbon\Carbon::parse($employee->probation_end_date)->format('M d, Y') : '-',
                        'Confirmation Date' => $employee->confirmation_date ? \Carbon\Carbon::parse($employee->confirmation_date)->format('M d, Y') : '-',
                        'Department' => $employee->department ?? '-',
                        'Designation' => $employee->hrDesignation->name ?? $employee->designation ?? '-',
                        'Reporting Manager' => $employee->reportingManager->user->name ?? '-',
                    ] as $label => $value)
                        <div class="flex justify-between items-center py-1.5 border-b border-white/[0.04] last:border-0">
                            <span class="text-xs text-white/35">{{ $label }}</span>
                            <span class="text-sm text-white/70">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                <h3 class="text-sm font-semibold text-white/70 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 prod-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Employment Timeline
                </h3>
                <div class="relative pl-6 space-y-5">
                    <div class="absolute left-2 top-1 bottom-1 w-px bg-white/[0.08]"></div>

                    @if($employee->joining_date)
                        <div class="relative">
                            <div class="absolute -left-[18px] top-1 w-2.5 h-2.5 rounded-full bg-cyan-500 ring-2 ring-[#17172A]"></div>
                            <p class="text-sm text-white/75 font-medium">Joined</p>
                            <p class="text-xs text-white/35 mt-0.5">{{ \Carbon\Carbon::parse($employee->joining_date)->format('M d, Y') }}</p>
                        </div>
                    @endif

                    @if($employee->probation_end_date)
                        <div class="relative">
                            <div class="absolute -left-[18px] top-1 w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-[#17172A]"></div>
                            <p class="text-sm text-white/75 font-medium">Probation End</p>
                            <p class="text-xs text-white/35 mt-0.5">{{ \Carbon\Carbon::parse($employee->probation_end_date)->format('M d, Y') }}</p>
                        </div>
                    @endif

                    @if($employee->confirmation_date)
                        <div class="relative">
                            <div class="absolute -left-[18px] top-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-[#17172A]"></div>
                            <p class="text-sm text-white/75 font-medium">Confirmed</p>
                            <p class="text-xs text-white/35 mt-0.5">{{ \Carbon\Carbon::parse($employee->confirmation_date)->format('M d, Y') }}</p>
                        </div>
                    @endif

                    @if($employee->status === 'active')
                        <div class="relative">
                            <div class="absolute -left-[18px] top-1 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-[#17172A] animate-pulse"></div>
                            <p class="text-sm text-white/75 font-medium">Currently Active</p>
                            <p class="text-xs text-white/35 mt-0.5">Present</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- EDUCATION TAB --}}
    <div x-show="activeTab === 'education'" x-cloak>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            @if($employee->education && $employee->education->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/[0.07]">
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Institution</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Degree</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Field of Study</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Period</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.05]">
                            @foreach($employee->education as $edu)
                                <tr class="hover:bg-white/[0.02]">
                                    <td class="px-5 py-3.5 text-sm text-white/75 font-medium">{{ $edu->institution }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/55">{{ $edu->degree }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/55">{{ $edu->field_of_study ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/45">
                                        {{ $edu->start_date ? \Carbon\Carbon::parse($edu->start_date)->format('M Y') : '?' }}
                                        &mdash;
                                        {{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('M Y') : 'Present' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-white/55">{{ $edu->grade ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-14">
                    <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    <p class="text-sm text-white/30">No education records found</p>
                </div>
            @endif
        </div>
    </div>

    {{-- EXPERIENCE TAB --}}
    <div x-show="activeTab === 'experience'" x-cloak>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            @if($employee->experience && $employee->experience->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/[0.07]">
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Company</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Designation</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Period</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.05]">
                            @foreach($employee->experience as $exp)
                                <tr class="hover:bg-white/[0.02]">
                                    <td class="px-5 py-3.5 text-sm text-white/75 font-medium">{{ $exp->company }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/55">{{ $exp->designation }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/45 whitespace-nowrap">
                                        {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('M Y') : '?' }}
                                        &mdash;
                                        {{ $exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : 'Present' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-white/45 max-w-xs truncate">{{ $exp->description ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-14">
                    <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <p class="text-sm text-white/30">No experience records found</p>
                </div>
            @endif
        </div>
    </div>

    {{-- DOCUMENTS TAB --}}
    <div x-show="activeTab === 'documents'" x-cloak>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            @if($employee->documents && $employee->documents->count() > 0)
                <div class="divide-y divide-white/[0.05]">
                    @foreach($employee->documents as $doc)
                        <div class="flex items-center justify-between px-5 py-4 hover:bg-white/[0.02] transition-colors">
                            <div class="flex items-center gap-3.5">
                                <div class="w-9 h-9 rounded-lg bg-white/[0.06] flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5 text-white/35" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm text-white/75 font-medium">{{ $doc->name }}</p>
                                    <p class="text-xs text-white/35 mt-0.5">
                                        {{ $doc->type ?? 'Document' }}
                                        @if($doc->uploaded_at)
                                            &middot; Uploaded {{ \Carbon\Carbon::parse($doc->uploaded_at)->format('M d, Y') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @if($doc->file_path)
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                   class="px-3 py-1.5 rounded-lg text-xs font-medium prod-text prod-bg-muted hover:opacity-80 transition-opacity">
                                    View
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-14">
                    <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm text-white/30">No documents uploaded</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ASSETS TAB --}}
    <div x-show="activeTab === 'assets'" x-cloak>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            @if($employee->assets && $employee->assets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/[0.07]">
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Asset Name</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Type</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Serial Number</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Assigned</th>
                                <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/[0.05]">
                            @foreach($employee->assets as $asset)
                                <tr class="hover:bg-white/[0.02]">
                                    <td class="px-5 py-3.5 text-sm text-white/75 font-medium">{{ $asset->name }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/55">{{ $asset->type ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/45 font-mono">{{ $asset->serial_number ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-sm text-white/45">{{ $asset->assigned_at ? \Carbon\Carbon::parse($asset->assigned_at)->format('M d, Y') : '-' }}</td>
                                    <td class="px-5 py-3.5">
                                        @php
                                            $assetStatusColor = match($asset->status ?? 'assigned') {
                                                'assigned' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                                'returned' => 'bg-white/[0.08] text-white/45 border-white/[0.1]',
                                                'damaged'  => 'bg-red-500/15 text-red-400 border-red-500/20',
                                                default    => 'bg-white/[0.08] text-white/45 border-white/[0.1]',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $assetStatusColor }}">
                                            {{ ucfirst($asset->status ?? 'assigned') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-14">
                    <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <p class="text-sm text-white/30">No assets assigned</p>
                </div>
            @endif
        </div>
    </div>

    {{-- SKILLS TAB --}}
    <div x-show="activeTab === 'skills'" x-cloak>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            @if($employee->skills && $employee->skills->count() > 0)
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($employee->skills as $skill)
                        @php
                            $profLevel = $skill->proficiency_level ?? 'intermediate';
                            $profColors = [
                                'beginner'     => ['bg-blue-500/15 text-blue-400 border-blue-500/20', 'w-1/4'],
                                'intermediate' => ['bg-amber-500/15 text-amber-400 border-amber-500/20', 'w-2/4'],
                                'advanced'     => ['bg-emerald-500/15 text-emerald-400 border-emerald-500/20', 'w-3/4'],
                                'expert'       => ['bg-cyan-500/15 text-cyan-400 border-cyan-500/20', 'w-full'],
                            ];
                            $pColor = $profColors[$profLevel] ?? $profColors['intermediate'];
                        @endphp
                        <div class="bg-white/[0.03] border border-white/[0.06] rounded-lg p-3.5">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-sm text-white/75 font-medium">{{ $skill->name }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $pColor[0] }}">
                                    {{ ucfirst($profLevel) }}
                                </span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-white/[0.06] overflow-hidden">
                                <div class="h-full rounded-full prod-bg {{ $pColor[1] }} transition-all"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-14">
                    <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <p class="text-sm text-white/30">No skills recorded</p>
                </div>
            @endif
        </div>
    </div>

</div>

</x-layouts.hr>
