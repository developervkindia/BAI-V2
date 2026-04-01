<x-layouts.hr title="People Directory" currentView="people">

<div class="p-5 lg:p-7 space-y-5"
     x-data="{
        viewMode: '{{ $viewMode ?? 'grid' }}',
        search: '',
        departmentFilter: '',
        statusFilter: '',
        get filteredEmployees() {
            return this.$refs.cards ? true : true;
        },
        matchesSearch(name) {
            if (!this.search) return true;
            return name.toLowerCase().includes(this.search.toLowerCase());
        },
        matchesDepartment(dept) {
            if (!this.departmentFilter) return true;
            return dept === this.departmentFilter;
        },
        matchesStatus(status) {
            if (!this.statusFilter) return true;
            return status === this.statusFilter;
        },
        shouldShow(name, dept, status) {
            return this.matchesSearch(name) && this.matchesDepartment(dept) && this.matchesStatus(status);
        }
     }">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white/85">People Directory</h1>
            <p class="text-sm text-white/45 mt-0.5">{{ $employees->total() }} employees</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="flex items-center bg-white/[0.06] rounded-lg p-0.5 border border-white/[0.07]">
                <button @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-white/[0.12] text-white/85 shadow-sm' : 'text-white/40 hover:text-white/60'"
                        class="p-1.5 rounded-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
                <button @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-white/[0.12] text-white/85 shadow-sm' : 'text-white/40 hover:text-white/60'"
                        class="p-1.5 rounded-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        {{-- Search --}}
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" placeholder="Search by name..."
                   class="w-full pl-9 pr-4 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-sm text-white/70 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
        </div>

        {{-- Department Filter --}}
        <select x-model="departmentFilter"
                class="px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-sm text-white/65 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer min-w-[160px]">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->name }}">{{ $dept->name }}</option>
            @endforeach
        </select>

        {{-- Status Filter --}}
        <select x-model="statusFilter"
                class="px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-sm text-white/65 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer min-w-[130px]">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="on_leave">On Leave</option>
        </select>
    </div>

    {{-- GRID VIEW --}}
    <div x-show="viewMode === 'grid'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($employees as $emp)
            <a href="{{ route('hr.people.show', $emp) }}"
               x-show="shouldShow('{{ addslashes($emp->user->name ?? 'Employee') }}', '{{ addslashes($emp->department ?? '') }}', '{{ $emp->status }}')"
               class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-white/[0.14] hover:bg-[#1a1a30] transition-all group cursor-pointer">
                <div class="flex items-start gap-3.5">
                    {{-- Avatar --}}
                    <div class="w-11 h-11 rounded-full prod-bg-muted prod-text text-sm font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($emp->user->name ?? 'E', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-white/85 truncate group-hover:text-white transition-colors">
                            {{ $emp->user->name ?? 'Employee' }}
                        </h3>
                        <p class="text-xs text-white/45 truncate mt-0.5">
                            {{ $emp->hrDesignation->name ?? $emp->designation ?? 'No designation' }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-white/35">{{ $emp->department ?? 'Unassigned' }}</span>
                    @php
                        $statusColors = [
                            'active'   => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                            'inactive' => 'bg-red-500/15 text-red-400 border-red-500/20',
                            'on_leave' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                        ];
                        $sColor = $statusColors[$emp->status] ?? $statusColors['active'];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $sColor }}">
                        {{ ucfirst(str_replace('_', ' ', $emp->status)) }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- LIST VIEW --}}
    <div x-show="viewMode === 'list'" x-cloak class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.07]">
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Employee</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Employee ID</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Department</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Designation</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Status</th>
                        <th class="text-left px-5 py-3 text-[11px] font-semibold text-white/35 uppercase tracking-wider">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.05]">
                    @foreach($employees as $emp)
                        <tr x-show="shouldShow('{{ addslashes($emp->user->name ?? 'Employee') }}', '{{ addslashes($emp->department ?? '') }}', '{{ $emp->status }}')"
                            class="hover:bg-white/[0.03] transition-colors cursor-pointer"
                            onclick="window.location='{{ route('hr.people.show', $emp) }}'">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full prod-bg-muted prod-text text-[11px] font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($emp->user->name ?? 'E', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-white/80">{{ $emp->user->name ?? 'Employee' }}</p>
                                        <p class="text-xs text-white/35">{{ $emp->user->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-white/55 font-mono">{{ $emp->employee_id }}</td>
                            <td class="px-5 py-3.5 text-sm text-white/55">{{ $emp->department ?? 'Unassigned' }}</td>
                            <td class="px-5 py-3.5 text-sm text-white/55">{{ $emp->hrDesignation->name ?? $emp->designation ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                @php
                                    $sColor = $statusColors[$emp->status] ?? $statusColors['active'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border {{ $sColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $emp->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-white/45">{{ $emp->joining_date ? \Carbon\Carbon::parse($emp->joining_date)->format('M d, Y') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- No results message --}}
    <div x-show="document.querySelectorAll('[x-show*=shouldShow]:not([style*=none])').length === 0 && (search || departmentFilter || statusFilter)" x-cloak
         class="text-center py-16">
        <svg class="w-12 h-12 text-white/15 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <p class="text-white/35 text-sm">No employees match your filters</p>
    </div>

    {{-- Pagination --}}
    @if($employees->hasPages())
        <div class="flex justify-center pt-2">
            <nav class="flex items-center gap-1">
                {{-- Previous --}}
                @if($employees->onFirstPage())
                    <span class="px-3 py-1.5 rounded-lg text-sm text-white/20 cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $employees->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-white/55 hover:bg-white/[0.06] hover:text-white/75 transition-colors">Previous</a>
                @endif

                {{-- Page Numbers --}}
                @foreach($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                    @if($page == $employees->currentPage())
                        <span class="px-3 py-1.5 rounded-lg text-sm font-medium prod-bg-muted prod-text">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-sm text-white/45 hover:bg-white/[0.06] hover:text-white/70 transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($employees->hasMorePages())
                    <a href="{{ $employees->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-sm text-white/55 hover:bg-white/[0.06] hover:text-white/75 transition-colors">Next</a>
                @else
                    <span class="px-3 py-1.5 rounded-lg text-sm text-white/20 cursor-not-allowed">Next</span>
                @endif
            </nav>
        </div>
    @endif

</div>

</x-layouts.hr>
