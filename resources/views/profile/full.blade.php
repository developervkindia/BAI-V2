<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#0D0D18] text-white antialiased">

    {{-- Top Bar --}}
    <div class="border-b border-white/[0.07] bg-[#0B0B12]">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ url('/dashboard') }}"
                   class="flex items-center gap-2 text-white/40 hover:text-white/70 transition text-[13px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Page Content --}}
    <div class="max-w-6xl mx-auto px-6 py-8" x-data="profileManager()">

        {{-- Profile Header --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-5">
                {{-- Avatar with Upload --}}
                <div class="relative group">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-orange-500/25 to-orange-600/25 flex items-center justify-center text-orange-400 text-[28px] font-bold overflow-hidden">
                        @if(auth()->user()->avatar)
                            <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <label class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-2xl opacity-0 group-hover:opacity-100 transition cursor-pointer">
                        <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <input type="file" accept="image/*" class="hidden" @change="uploadAvatar($event)">
                    </label>
                </div>
                <div>
                    <h1 class="text-[20px] font-semibold text-white/85">{{ auth()->user()->name }}</h1>
                    <p class="text-white/40 text-[13px] mt-0.5">{{ auth()->user()->email }}</p>
                    @if($profile)
                        <div class="flex items-center gap-3 mt-2">
                            @if($profile->employee_id)
                                <span class="text-[12px] text-white/30 bg-white/[0.05] px-2 py-0.5 rounded-md">{{ $profile->employee_id }}</span>
                            @endif
                            @if($profile->designation)
                                <span class="text-[12px] text-white/40">{{ $profile->designation }}</span>
                            @endif
                            @if($profile->department)
                                <span class="text-[12px] text-orange-400/60 bg-orange-500/10 px-2 py-0.5 rounded-md">{{ $profile->department }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        <div x-show="flashMessage" x-transition
             :class="flashType === 'success' ? 'bg-green-500/10 border-green-500/20 text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-400'"
             class="mb-6 border rounded-xl px-4 py-3 text-[13px]"
             x-text="flashMessage"
             x-cloak></div>

        {{-- Tabs --}}
        <div class="border-b border-white/[0.07] mb-6">
            <nav class="flex gap-1 -mb-px overflow-x-auto">
                @foreach(['personal' => 'Personal', 'employment' => 'Employment', 'education' => 'Education', 'experience' => 'Experience', 'documents' => 'Documents', 'skills' => 'Skills', 'security' => 'Security'] as $tab => $label)
                    <button @click="activeTab = '{{ $tab }}'"
                            :class="activeTab === '{{ $tab }}' ? 'text-orange-400 border-orange-400' : 'text-white/40 border-transparent hover:text-white/60'"
                            class="px-4 py-3 text-[13px] font-medium border-b-2 transition whitespace-nowrap">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab: Personal --}}
        <div x-show="activeTab === 'personal'" x-cloak>
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                <h2 class="text-[15px] font-semibold text-white/85 mb-5">Personal Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Phone</label>
                        <input type="tel" x-model="personal.phone" placeholder="+91 98765 43210"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Date of Birth</label>
                        <input type="date" x-model="personal.date_of_birth"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Gender</label>
                        <select x-model="personal.gender"
                                class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                            <option value="prefer_not_to_say">Prefer not to say</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Marital Status</label>
                        <select x-model="personal.marital_status"
                                class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                            <option value="">Select</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Blood Group</label>
                        <select x-model="personal.blood_group"
                                class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                            <option value="">Select</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                <option value="{{ $bg }}">{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Nationality</label>
                        <input type="text" x-model="personal.nationality" placeholder="e.g. Indian"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                </div>

                {{-- Emergency Contact --}}
                <h3 class="text-[14px] font-semibold text-white/75 mt-6 mb-4">Emergency Contact</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Contact Name</label>
                        <input type="text" x-model="personal.emergency_contact_name"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Relationship</label>
                        <input type="text" x-model="personal.emergency_contact_relationship"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Phone</label>
                        <input type="tel" x-model="personal.emergency_contact_phone"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                </div>

                {{-- Address --}}
                <h3 class="text-[14px] font-semibold text-white/75 mt-6 mb-4">Address</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Current Address</label>
                        <textarea x-model="personal.current_address" rows="3" placeholder="Enter current address..."
                                  class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Permanent Address</label>
                        <textarea x-model="personal.permanent_address" rows="3" placeholder="Enter permanent address..."
                                  class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition resize-none"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="savePersonal()"
                            :disabled="saving"
                            class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-5 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-orange-500/20 disabled:opacity-50">
                        <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="saving ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Tab: Employment (Read-Only) --}}
        <div x-show="activeTab === 'employment'" x-cloak>
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-[15px] font-semibold text-white/85">Employment Details</h2>
                    <span class="text-[11px] text-white/25 bg-white/[0.05] px-2.5 py-1 rounded-lg">Set by admin</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([
                        'Employee ID' => $profile->employee_id ?? '-',
                        'Designation' => $profile->designation ?? '-',
                        'Department' => $profile->department ?? '-',
                        'Date of Joining' => $profile->date_of_joining ? \Carbon\Carbon::parse($profile->date_of_joining)->format('M d, Y') : '-',
                        'Employment Type' => ucfirst(str_replace('_', ' ', $profile->employment_type ?? '-')),
                        'Reporting Manager' => $profile->manager->user->name ?? '-',
                        'Work Location' => $profile->work_location ?? '-',
                        'Shift' => $profile->shift ?? '-',
                    ] as $label => $value)
                        <div class="flex items-center justify-between py-2 px-3 rounded-xl bg-white/[0.02]">
                            <span class="text-[12px] text-white/35">{{ $label }}</span>
                            <span class="text-[13px] text-white/65 font-medium">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Tab: Education --}}
        <div x-show="activeTab === 'education'" x-cloak>
            <div class="space-y-4">
                {{-- Education List --}}
                <template x-for="(edu, index) in educations" :key="edu.id || index">
                    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                        <template x-if="editingEducation !== index">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-[14px] font-semibold text-white/80" x-text="edu.degree"></h4>
                                    <p class="text-[13px] text-white/50 mt-0.5" x-text="edu.institution"></p>
                                    <div class="flex items-center gap-3 mt-2">
                                        <span class="text-[12px] text-white/35" x-text="edu.start_year + ' - ' + (edu.end_year || 'Present')"></span>
                                        <template x-if="edu.grade">
                                            <span class="text-[12px] text-orange-400/70 bg-orange-500/10 px-2 py-0.5 rounded-md" x-text="'Grade: ' + edu.grade"></span>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="editingEducation = index"
                                            class="text-white/30 hover:text-white/60 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteEducation(edu.id, index)"
                                            class="text-red-400/30 hover:text-red-400 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="editingEducation === index">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Degree</label>
                                        <input type="text" x-model="edu.degree" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Institution</label>
                                        <input type="text" x-model="edu.institution" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Start Year</label>
                                        <input type="text" x-model="edu.start_year" placeholder="2018" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">End Year</label>
                                        <input type="text" x-model="edu.end_year" placeholder="2022" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Grade</label>
                                        <input type="text" x-model="edu.grade" placeholder="e.g. 3.8 GPA" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="saveEducation(edu, index)"
                                            class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2 text-[13px] font-semibold transition">
                                        Save
                                    </button>
                                    <button @click="editingEducation = null"
                                            class="border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2 text-[13px] font-medium transition">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Add Education Form --}}
                <div x-show="showAddEducation" x-cloak class="bg-[#111120] border border-orange-500/20 rounded-2xl p-5">
                    <h3 class="text-[14px] font-semibold text-white/80 mb-4">Add Education</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Degree *</label>
                            <input type="text" x-model="newEducation.degree" placeholder="e.g. B.Tech Computer Science" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Institution *</label>
                            <input type="text" x-model="newEducation.institution" placeholder="e.g. IIT Delhi" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Start Year</label>
                            <input type="text" x-model="newEducation.start_year" placeholder="2018" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">End Year</label>
                            <input type="text" x-model="newEducation.end_year" placeholder="2022" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Grade</label>
                            <input type="text" x-model="newEducation.grade" placeholder="e.g. 3.8 GPA" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-4">
                        <button @click="addEducation()"
                                class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2 text-[13px] font-semibold transition">
                            Add
                        </button>
                        <button @click="showAddEducation = false; resetNewEducation()"
                                class="border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2 text-[13px] font-medium transition">
                            Cancel
                        </button>
                    </div>
                </div>

                <button x-show="!showAddEducation" @click="showAddEducation = true"
                        class="w-full border-2 border-dashed border-white/[0.08] hover:border-white/[0.15] rounded-2xl p-4 text-white/35 hover:text-white/55 text-[13px] font-medium transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Education
                </button>
            </div>
        </div>

        {{-- Tab: Experience --}}
        <div x-show="activeTab === 'experience'" x-cloak>
            <div class="space-y-4">
                <template x-for="(exp, index) in experiences" :key="exp.id || index">
                    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                        <template x-if="editingExperience !== index">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-[14px] font-semibold text-white/80" x-text="exp.title"></h4>
                                    <p class="text-[13px] text-white/50 mt-0.5" x-text="exp.company"></p>
                                    <span class="text-[12px] text-white/35 mt-1 block" x-text="exp.start_date + ' - ' + (exp.end_date || 'Present')"></span>
                                    <template x-if="exp.description">
                                        <p class="text-[12px] text-white/40 mt-2 leading-relaxed" x-text="exp.description"></p>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="editingExperience = index" class="text-white/30 hover:text-white/60 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteExperience(exp.id, index)" class="text-red-400/30 hover:text-red-400 transition p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="editingExperience === index">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Job Title</label>
                                        <input type="text" x-model="exp.title" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Company</label>
                                        <input type="text" x-model="exp.company" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Start Date</label>
                                        <input type="date" x-model="exp.start_date" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">End Date</label>
                                        <input type="date" x-model="exp.end_date" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Description</label>
                                    <textarea x-model="exp.description" rows="3" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition resize-none"></textarea>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="saveExperience(exp, index)" class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2 text-[13px] font-semibold transition">Save</button>
                                    <button @click="editingExperience = null" class="border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2 text-[13px] font-medium transition">Cancel</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Add Experience Form --}}
                <div x-show="showAddExperience" x-cloak class="bg-[#111120] border border-orange-500/20 rounded-2xl p-5">
                    <h3 class="text-[14px] font-semibold text-white/80 mb-4">Add Experience</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Job Title *</label>
                            <input type="text" x-model="newExperience.title" placeholder="e.g. Software Engineer" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Company *</label>
                            <input type="text" x-model="newExperience.company" placeholder="e.g. Google" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Start Date</label>
                            <input type="date" x-model="newExperience.start_date" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">End Date</label>
                            <input type="date" x-model="newExperience.end_date" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Description</label>
                        <textarea x-model="newExperience.description" rows="3" placeholder="Describe your role and responsibilities..." class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition resize-none"></textarea>
                    </div>
                    <div class="flex items-center gap-2 mt-4">
                        <button @click="addExperience()" class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2 text-[13px] font-semibold transition">Add</button>
                        <button @click="showAddExperience = false; resetNewExperience()" class="border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2 text-[13px] font-medium transition">Cancel</button>
                    </div>
                </div>

                <button x-show="!showAddExperience" @click="showAddExperience = true"
                        class="w-full border-2 border-dashed border-white/[0.08] hover:border-white/[0.15] rounded-2xl p-4 text-white/35 hover:text-white/55 text-[13px] font-medium transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Experience
                </button>
            </div>
        </div>

        {{-- Tab: Documents --}}
        <div x-show="activeTab === 'documents'" x-cloak>
            <div class="space-y-4">
                {{-- Upload Form --}}
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                    <h3 class="text-[14px] font-semibold text-white/80 mb-4">Upload Document</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Document Type</label>
                            <select x-model="newDocument.type"
                                    class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                <option value="">Select type</option>
                                <option value="id_proof">ID Proof</option>
                                <option value="address_proof">Address Proof</option>
                                <option value="education">Education</option>
                                <option value="experience">Experience</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Document Name</label>
                            <input type="text" x-model="newDocument.name" placeholder="e.g. Passport"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">File</label>
                            <input type="file" @change="newDocument.file = $event.target.files[0]"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] file:mr-3 file:rounded-lg file:border-0 file:bg-orange-500/20 file:text-orange-400 file:px-3 file:py-1 file:text-[12px] file:font-medium focus:outline-none transition">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button @click="uploadDocument()"
                                :disabled="!newDocument.type || !newDocument.name || !newDocument.file"
                                class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2 text-[13px] font-semibold transition disabled:opacity-30 disabled:cursor-not-allowed">
                            Upload
                        </button>
                    </div>
                </div>

                {{-- Document List --}}
                <template x-for="doc in documents" :key="doc.id">
                    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-violet-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[13px] font-medium text-white/70" x-text="doc.name"></span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-cyan-500/15 text-cyan-400 uppercase" x-text="doc.type"></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <template x-if="doc.file_path">
                                <a :href="'/storage/' + doc.file_path" target="_blank"
                                   class="inline-flex items-center gap-1 border border-white/[0.08] text-white/40 hover:text-white/65 rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                    Download
                                </a>
                            </template>
                            <button @click="deleteDocument(doc.id)"
                                    class="inline-flex items-center gap-1 border border-red-500/15 text-red-400/40 hover:text-red-400 rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="documents.length === 0">
                    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-10 text-center">
                        <p class="text-white/30 text-[13px]">No documents uploaded yet.</p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Tab: Skills --}}
        <div x-show="activeTab === 'skills'" x-cloak>
            <div class="space-y-5">
                {{-- Add Skill --}}
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                    <h3 class="text-[14px] font-semibold text-white/80 mb-4">Add Skill</h3>
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Skill Name</label>
                            <input type="text" x-model="newSkill" placeholder="e.g. JavaScript, Project Management..."
                                   @keydown.enter="addSkill()"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                        </div>
                        <button @click="addSkill()"
                                :disabled="!newSkill.trim()"
                                class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition disabled:opacity-30">
                            Add
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-4">
                        <template x-for="(skill, index) in skills" :key="skill.id || index">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-orange-500/10 text-orange-400 text-[12px] font-medium border border-orange-500/15">
                                <span x-text="skill.name"></span>
                                <button @click="deleteSkill(skill.id, index)" class="text-orange-400/40 hover:text-orange-400 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </div>
                    <template x-if="skills.length === 0">
                        <p class="text-white/25 text-[12px] mt-3">No skills added yet. Type a skill name and press Enter or click Add.</p>
                    </template>
                </div>

                {{-- Certifications --}}
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                    <h3 class="text-[14px] font-semibold text-white/80 mb-4">Certifications</h3>

                    <div class="space-y-3 mb-4">
                        <template x-for="(cert, index) in certifications" :key="cert.id || index">
                            <div class="flex items-start justify-between p-3 rounded-xl bg-white/[0.02] border border-white/[0.04]">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-4 h-4 text-cyan-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="text-[13px] font-medium text-white/70" x-text="cert.name"></span>
                                        <div class="text-[12px] text-white/35 mt-0.5" x-text="cert.issuer"></div>
                                        <div class="text-[11px] text-white/25 mt-0.5" x-text="(cert.issued_date || '') + (cert.expiry_date ? ' - ' + cert.expiry_date : '')"></div>
                                    </div>
                                </div>
                                <button @click="deleteCertification(cert.id, index)" class="text-red-400/30 hover:text-red-400 transition p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Add Certification --}}
                    <div x-show="showAddCert" x-cloak class="border border-orange-500/20 rounded-xl p-4 mb-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Name *</label>
                                <input type="text" x-model="newCert.name" placeholder="e.g. AWS Solutions Architect" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Issuer *</label>
                                <input type="text" x-model="newCert.issuer" placeholder="e.g. Amazon Web Services" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Issued Date</label>
                                <input type="date" x-model="newCert.issued_date" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Expiry Date</label>
                                <input type="date" x-model="newCert.expiry_date" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition [color-scheme:dark]">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button @click="addCertification()" class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2 text-[13px] font-semibold transition">Add</button>
                            <button @click="showAddCert = false" class="border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2 text-[13px] font-medium transition">Cancel</button>
                        </div>
                    </div>

                    <button x-show="!showAddCert" @click="showAddCert = true"
                            class="text-orange-400 hover:text-orange-300 text-[12px] font-medium transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Certification
                    </button>
                </div>
            </div>
        </div>

        {{-- Tab: Security --}}
        <div x-show="activeTab === 'security'" x-cloak>
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6">
                <h2 class="text-[15px] font-semibold text-white/85 mb-5">Change Password</h2>
                <div class="max-w-md space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Current Password</label>
                        <input type="password" x-model="passwordForm.current_password" placeholder="Enter current password"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">New Password</label>
                        <input type="password" x-model="passwordForm.password" placeholder="Enter new password"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Confirm New Password</label>
                        <input type="password" x-model="passwordForm.password_confirmation" placeholder="Confirm new password"
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                    <div>
                        <button @click="changePassword()"
                                :disabled="!passwordForm.current_password || !passwordForm.password || !passwordForm.password_confirmation"
                                class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-5 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-orange-500/20 disabled:opacity-30 disabled:cursor-not-allowed">
                            Update Password
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function profileManager() {
            const profileData = @json($profile ?? new \stdClass());
            const apiBase = '{{ url("/api/profile") }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            return {
                activeTab: 'personal',
                saving: false,
                flashMessage: '',
                flashType: 'success',

                // Personal info
                personal: {
                    phone: profileData.phone || '',
                    date_of_birth: profileData.date_of_birth || '',
                    gender: profileData.gender || '',
                    marital_status: profileData.marital_status || '',
                    blood_group: profileData.blood_group || '',
                    nationality: profileData.nationality || '',
                    emergency_contact_name: profileData.emergency_contact_name || '',
                    emergency_contact_relationship: profileData.emergency_contact_relationship || '',
                    emergency_contact_phone: profileData.emergency_contact_phone || '',
                    current_address: profileData.current_address || '',
                    permanent_address: profileData.permanent_address || '',
                },

                // Education
                educations: @json($profile->education ?? []),
                editingEducation: null,
                showAddEducation: false,
                newEducation: { degree: '', institution: '', start_year: '', end_year: '', grade: '' },

                // Experience
                experiences: @json($profile->experience ?? []),
                editingExperience: null,
                showAddExperience: false,
                newExperience: { title: '', company: '', start_date: '', end_date: '', description: '' },

                // Documents
                documents: @json($profile->documents ?? []),
                newDocument: { type: '', name: '', file: null },

                // Skills
                skills: @json($profile->skills ?? []),
                newSkill: '',
                certifications: @json($profile->skills->where('category', 'certification') ?? []),
                showAddCert: false,
                newCert: { name: '', issuer: '', issued_date: '', expiry_date: '' },

                // Security
                passwordForm: { current_password: '', password: '', password_confirmation: '' },

                showFlash(message, type = 'success') {
                    this.flashMessage = message;
                    this.flashType = type;
                    setTimeout(() => this.flashMessage = '', 4000);
                },

                async apiCall(url, method = 'GET', data = null) {
                    const opts = {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    };
                    if (data) opts.body = JSON.stringify(data);
                    const res = await fetch(url, opts);
                    if (!res.ok) throw await res.json();
                    return res.json();
                },

                async apiUpload(url, formData) {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });
                    if (!res.ok) throw await res.json();
                    return res.json();
                },

                async uploadAvatar(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    const fd = new FormData();
                    fd.append('avatar', file);
                    try {
                        await this.apiUpload(apiBase + '/avatar', fd);
                        this.showFlash('Avatar updated successfully');
                        window.location.reload();
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to upload avatar', 'error');
                    }
                },

                async savePersonal() {
                    this.saving = true;
                    try {
                        await this.apiCall(apiBase + '/personal', 'PUT', this.personal);
                        this.showFlash('Personal information saved successfully');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to save changes', 'error');
                    }
                    this.saving = false;
                },

                resetNewEducation() {
                    this.newEducation = { degree: '', institution: '', start_year: '', end_year: '', grade: '' };
                },

                async addEducation() {
                    try {
                        const res = await this.apiCall(apiBase + '/educations', 'POST', this.newEducation);
                        this.educations.push(res.data || res);
                        this.showAddEducation = false;
                        this.resetNewEducation();
                        this.showFlash('Education added');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to add education', 'error');
                    }
                },

                async saveEducation(edu, index) {
                    try {
                        await this.apiCall(apiBase + '/educations/' + edu.id, 'PUT', edu);
                        this.editingEducation = null;
                        this.showFlash('Education updated');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to update education', 'error');
                    }
                },

                async deleteEducation(id, index) {
                    if (!confirm('Delete this education entry?')) return;
                    try {
                        await this.apiCall(apiBase + '/educations/' + id, 'DELETE');
                        this.educations.splice(index, 1);
                        this.showFlash('Education deleted');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to delete', 'error');
                    }
                },

                resetNewExperience() {
                    this.newExperience = { title: '', company: '', start_date: '', end_date: '', description: '' };
                },

                async addExperience() {
                    try {
                        const res = await this.apiCall(apiBase + '/experiences', 'POST', this.newExperience);
                        this.experiences.push(res.data || res);
                        this.showAddExperience = false;
                        this.resetNewExperience();
                        this.showFlash('Experience added');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to add experience', 'error');
                    }
                },

                async saveExperience(exp, index) {
                    try {
                        await this.apiCall(apiBase + '/experiences/' + exp.id, 'PUT', exp);
                        this.editingExperience = null;
                        this.showFlash('Experience updated');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to update experience', 'error');
                    }
                },

                async deleteExperience(id, index) {
                    if (!confirm('Delete this experience entry?')) return;
                    try {
                        await this.apiCall(apiBase + '/experiences/' + id, 'DELETE');
                        this.experiences.splice(index, 1);
                        this.showFlash('Experience deleted');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to delete', 'error');
                    }
                },

                async uploadDocument() {
                    const fd = new FormData();
                    fd.append('type', this.newDocument.type);
                    fd.append('name', this.newDocument.name);
                    fd.append('file', this.newDocument.file);
                    try {
                        const res = await this.apiUpload(apiBase + '/documents', fd);
                        this.documents.push(res.data || res);
                        this.newDocument = { type: '', name: '', file: null };
                        this.showFlash('Document uploaded');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to upload document', 'error');
                    }
                },

                async deleteDocument(id) {
                    if (!confirm('Delete this document?')) return;
                    try {
                        await this.apiCall(apiBase + '/documents/' + id, 'DELETE');
                        this.documents = this.documents.filter(d => d.id !== id);
                        this.showFlash('Document deleted');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to delete', 'error');
                    }
                },

                async addSkill() {
                    if (!this.newSkill.trim()) return;
                    try {
                        const res = await this.apiCall(apiBase + '/skills', 'POST', { name: this.newSkill.trim() });
                        this.skills.push(res.data || res);
                        this.newSkill = '';
                        this.showFlash('Skill added');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to add skill', 'error');
                    }
                },

                async deleteSkill(id, index) {
                    try {
                        await this.apiCall(apiBase + '/skills/' + id, 'DELETE');
                        this.skills.splice(index, 1);
                        this.showFlash('Skill removed');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to remove skill', 'error');
                    }
                },

                async addCertification() {
                    try {
                        const res = await this.apiCall(apiBase + '/certifications', 'POST', this.newCert);
                        this.certifications.push(res.data || res);
                        this.showAddCert = false;
                        this.newCert = { name: '', issuer: '', issued_date: '', expiry_date: '' };
                        this.showFlash('Certification added');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to add certification', 'error');
                    }
                },

                async deleteCertification(id, index) {
                    if (!confirm('Delete this certification?')) return;
                    try {
                        await this.apiCall(apiBase + '/certifications/' + id, 'DELETE');
                        this.certifications.splice(index, 1);
                        this.showFlash('Certification deleted');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to delete', 'error');
                    }
                },

                async changePassword() {
                    try {
                        await this.apiCall(apiBase + '/password', 'PUT', this.passwordForm);
                        this.passwordForm = { current_password: '', password: '', password_confirmation: '' };
                        this.showFlash('Password updated successfully');
                    } catch (e) {
                        this.showFlash(e.message || 'Failed to update password', 'error');
                    }
                },
            };
        }
    </script>

</body>
</html>
