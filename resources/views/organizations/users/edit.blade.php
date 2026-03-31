<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit {{ $user->name }} - {{ $organization->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#0D0D18] text-white antialiased">

    {{-- Top Bar --}}
    <div class="border-b border-white/[0.07] bg-[#0B0B12]">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('users.show', [$organization, $user]) }}"
                   class="flex items-center gap-2 text-white/40 hover:text-white/70 transition text-[13px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Profile
                </a>
                <div class="w-px h-5 bg-white/[0.07]"></div>
                <span class="text-white/25 text-[12px] font-medium uppercase tracking-wider">{{ $organization->name }}</span>
            </div>
        </div>
    </div>

    {{-- Page Content --}}
    <div class="max-w-5xl mx-auto px-6 py-8" x-data="{ activeTab: 'basic' }">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-orange-500/25 to-orange-600/25 flex items-center justify-center text-orange-400 text-[18px] font-bold flex-shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-[20px] font-semibold text-white/85">Edit {{ $user->name }}</h1>
                <p class="text-white/40 text-[13px] mt-0.5">Update member profile and role assignments</p>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3">
                <ul class="list-disc list-inside text-red-400 text-[13px] space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3 text-green-400 text-[13px]">
                {{ session('success') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="border-b border-white/[0.07] mb-6">
            <nav class="flex gap-1 -mb-px">
                @foreach(['basic' => 'Basic Info', 'employment' => 'Employment', 'contact' => 'Contact', 'bank' => 'Bank Details'] as $tab => $label)
                    <button @click="activeTab = '{{ $tab }}'"
                            :class="activeTab === '{{ $tab }}' ? 'text-orange-400 border-orange-400' : 'text-white/40 border-transparent hover:text-white/60'"
                            class="px-4 py-3 text-[13px] font-medium border-b-2 transition">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        @php $profile = $user->employeeProfiles->first(); @endphp

        <form action="{{ route('users.update', [$organization, $user]) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Tab: Basic Info --}}
            <div x-show="activeTab === 'basic'" x-cloak>
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                    <h2 class="text-[15px] font-semibold text-white/85 mb-5">Basic Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Name (readonly) --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Name</label>
                            <input type="text"
                                   value="{{ $user->name }}"
                                   readonly
                                   class="w-full bg-white/[0.03] border border-white/[0.07] text-white/40 rounded-xl px-3.5 py-2.5 text-[13px] cursor-not-allowed">
                            <p class="text-white/20 text-[11px] mt-1.5">Name is managed from the user account</p>
                        </div>

                        {{-- Employee ID --}}
                        <div>
                            <label for="employee_id" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Employee ID</label>
                            <input type="text"
                                   id="employee_id"
                                   name="employee_id"
                                   value="{{ old('employee_id', $profile->employee_id ?? '') }}"
                                   placeholder="e.g. EMP-001"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>

                        {{-- Designation --}}
                        <div>
                            <label for="designation" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Designation</label>
                            <input type="text"
                                   id="designation"
                                   name="designation"
                                   value="{{ old('designation', $profile->designation ?? '') }}"
                                   placeholder="e.g. Senior Developer"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>

                        {{-- Department --}}
                        <div>
                            <label for="department" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Department</label>
                            <input type="text"
                                   id="department"
                                   name="department"
                                   value="{{ old('department', $profile->department ?? '') }}"
                                   placeholder="e.g. Engineering"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Employment --}}
            <div x-show="activeTab === 'employment'" x-cloak>
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                    <h2 class="text-[15px] font-semibold text-white/85 mb-5">Employment Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Date of Joining --}}
                        <div>
                            <label for="date_of_joining" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Date of Joining</label>
                            <input type="date"
                                   id="date_of_joining"
                                   name="date_of_joining"
                                   value="{{ old('date_of_joining', $profile->date_of_joining ?? '') }}"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                        </div>

                        {{-- Employment Type --}}
                        <div>
                            <label for="employment_type" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Employment Type</label>
                            <select id="employment_type"
                                    name="employment_type"
                                    class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                <option value="">Select type</option>
                                @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'intern' => 'Intern', 'freelance' => 'Freelance'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('employment_type', $profile->employment_type ?? '') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Reporting Manager --}}
                        <div>
                            <label for="reporting_manager_id" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Reporting Manager</label>
                            <select id="reporting_manager_id"
                                    name="reporting_manager_id"
                                    class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                <option value="">No manager</option>
                                @foreach($orgMembers ?? [] as $m)
                                    @if($m->id !== $user->id)
                                        <option value="{{ $m->id }}" @selected(old('reporting_manager_id', $profile->reporting_manager_id ?? '') == $m->id)>
                                            {{ $m->user->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        {{-- Work Location --}}
                        <div>
                            <label for="work_location" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Work Location</label>
                            <input type="text"
                                   id="work_location"
                                   name="work_location"
                                   value="{{ old('work_location', $profile->work_location ?? '') }}"
                                   placeholder="e.g. Office - Floor 3"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>

                        {{-- Shift --}}
                        <div>
                            <label for="shift" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Shift</label>
                            <input type="text"
                                   id="shift"
                                   name="shift"
                                   value="{{ old('shift', $profile->shift ?? '') }}"
                                   placeholder="e.g. Day Shift (9AM - 6PM)"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Contact --}}
            <div x-show="activeTab === 'contact'" x-cloak>
                <div class="space-y-5">
                    {{-- Contact Details --}}
                    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                        <h2 class="text-[15px] font-semibold text-white/85 mb-5">Contact Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="phone" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Phone</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $profile->phone ?? '') }}" placeholder="+91 98765 43210"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                            <div>
                                <label for="personal_email" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Personal Email</label>
                                <input type="email" id="personal_email" name="personal_email" value="{{ old('personal_email', $profile->personal_email ?? '') }}" placeholder="personal@example.com"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                            <div>
                                <label for="work_phone" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Work Phone</label>
                                <input type="tel" id="work_phone" name="work_phone" value="{{ old('work_phone', $profile->work_phone ?? '') }}" placeholder="+91 98765 43210"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                            <div>
                                <label for="date_of_birth" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Date of Birth</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $profile->date_of_birth ?? '') }}"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                            </div>
                            <div>
                                <label for="gender" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Gender</label>
                                <select id="gender" name="gender"
                                        class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                    <option value="">Select</option>
                                    @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('gender', $profile->gender ?? '') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="marital_status" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Marital Status</label>
                                <select id="marital_status" name="marital_status"
                                        class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                    <option value="">Select</option>
                                    @foreach(['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('marital_status', $profile->marital_status ?? '') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="blood_group" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Blood Group</label>
                                <select id="blood_group" name="blood_group"
                                        class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                    <option value="">Select</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" @selected(old('blood_group', $profile->blood_group ?? '') === $bg)>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="nationality" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Nationality</label>
                                <input type="text" id="nationality" name="nationality" value="{{ old('nationality', $profile->nationality ?? '') }}" placeholder="e.g. Indian"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                        </div>
                    </div>

                    {{-- Emergency Contact --}}
                    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                        <h2 class="text-[15px] font-semibold text-white/85 mb-5">Emergency Contact</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="emergency_contact_name" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Contact Name</label>
                                <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $profile->emergency_contact_name ?? '') }}"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                            <div>
                                <label for="emergency_contact_relationship" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Relationship</label>
                                <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship', $profile->emergency_contact_relationship ?? '') }}"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                            <div>
                                <label for="emergency_contact_phone" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Phone</label>
                                <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $profile->emergency_contact_phone ?? '') }}"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                        <h2 class="text-[15px] font-semibold text-white/85 mb-5">Address</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="current_address" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Current Address</label>
                                <textarea id="current_address" name="current_address" rows="3" placeholder="Enter current address..."
                                          class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition resize-none">{{ old('current_address', $profile->current_address ?? '') }}</textarea>
                            </div>
                            <div>
                                <label for="permanent_address" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Permanent Address</label>
                                <textarea id="permanent_address" name="permanent_address" rows="3" placeholder="Enter permanent address..."
                                          class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition resize-none">{{ old('permanent_address', $profile->permanent_address ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Bank Details --}}
            <div x-show="activeTab === 'bank'" x-cloak>
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                    <h2 class="text-[15px] font-semibold text-white/85 mb-5">Bank Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="bank_name" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $profile->bank_name ?? '') }}" placeholder="e.g. State Bank of India"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label for="account_number" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Account Number</label>
                            <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $profile->account_number ?? '') }}" placeholder="Enter account number"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label for="ifsc_code" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">IFSC Code</label>
                            <input type="text" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $profile->ifsc_code ?? '') }}" placeholder="e.g. SBIN0001234"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label for="bank_branch" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Branch</label>
                            <input type="text" id="bank_branch" name="bank_branch" value="{{ old('bank_branch', $profile->bank_branch ?? '') }}" placeholder="e.g. Main Branch, Delhi"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role Assignment (always visible) --}}
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6 mt-6">
                <h2 class="text-[15px] font-semibold text-white/85 mb-2">Role Assignment</h2>
                <p class="text-white/35 text-[12px] mb-5">Assign custom roles to this member for fine-grained permission control.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @php $userRoleIds = $user->roles ? $user->roles->pluck('id')->toArray() : []; @endphp
                    @foreach($roles ?? [] as $role)
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.02] border border-white/[0.05] hover:border-white/[0.1] transition cursor-pointer">
                            <input type="checkbox"
                                   name="roles[]"
                                   value="{{ $role->id }}"
                                   @checked(in_array($role->id, old('roles', $userRoleIds)))
                                   class="w-4 h-4 rounded border-white/20 bg-white/[0.05] text-orange-500 focus:ring-orange-500/25 focus:ring-offset-0">
                            <div>
                                <span class="text-[13px] text-white/65 font-medium">{{ $role->name }}</span>
                                @if($role->description)
                                    <p class="text-[11px] text-white/25 mt-0.5">{{ Str::limit($role->description, 50) }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                @if(empty($roles) || (is_countable($roles) && count($roles) === 0))
                    <p class="text-white/25 text-[12px]">No custom roles available.
                        <a href="{{ route('roles.create', $organization) }}" class="text-orange-400 hover:text-orange-300">Create one</a>.
                    </p>
                @endif
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('users.show', [$organization, $user]) }}"
                   class="inline-flex items-center gap-2 border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2.5 text-[13px] font-medium transition">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-5 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-orange-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>

</body>
</html>
