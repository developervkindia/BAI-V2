<x-layouts.hr title="Organization Chart" currentView="org-chart">

<div class="p-5 lg:p-7 space-y-6"
     x-data="orgChart()"
     x-init="init()">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white/85">Organization Chart</h1>
            <p class="text-sm text-white/45 mt-0.5">Visual hierarchy of your organization</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="expandAll()" class="px-3 py-1.5 rounded-lg text-xs font-medium text-white/55 bg-white/[0.06] border border-white/[0.07] hover:bg-white/[0.1] hover:text-white/75 transition-colors">
                Expand All
            </button>
            <button @click="collapseAll()" class="px-3 py-1.5 rounded-lg text-xs font-medium text-white/55 bg-white/[0.06] border border-white/[0.07] hover:bg-white/[0.1] hover:text-white/75 transition-colors">
                Collapse All
            </button>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="searchQuery" placeholder="Search employees..."
               class="w-full pl-9 pr-4 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-sm text-white/70 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
    </div>

    {{-- Org Chart Tree --}}
    <div class="overflow-x-auto pb-8">
        <div class="min-w-[600px]">
            @php
                $allEmployees = $employees->keyBy('id');
                $topLevel = $employees->filter(fn($e) => !$e->reporting_manager_id);

                function getReports($managerId, $allEmployees) {
                    return $allEmployees->filter(fn($e) => $e->reporting_manager_id == $managerId);
                }
            @endphp

            {{-- Top Level Nodes --}}
            <div class="flex flex-col items-center gap-8">
                @foreach($topLevel as $leader)
                    <div class="w-full" x-data="{ showId: {{ $leader->id }} }">
                        <template x-if="matchesSearch({{ json_encode($leader->user->name ?? 'Employee') }})
                                        || hasMatchingDescendant({{ $leader->id }})
                                        || !searchQuery">
                            <div>
                                @include('hr.people._org-node', ['employee' => $leader, 'allEmployees' => $allEmployees, 'depth' => 0])
                            </div>
                        </template>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@php
    $employeesJson = $employees->map(function ($e) {
        return [
            'id' => $e->id,
            'name' => $e->user->name ?? ('EMP-' . $e->id),
            'designation' => $e->designation ?? '',
            'department' => $e->department ?? '',
            'reporting_manager_id' => $e->reporting_manager_id,
        ];
    })->values();
@endphp
<script>
function orgChart() {
    return {
        searchQuery: '',
        expanded: {},
        employees: @json($employeesJson),

        init() {
            // Expand first two levels by default
            this.employees.forEach(emp => {
                if (!emp.reporting_manager_id) {
                    this.expanded[emp.id] = true;
                    // Expand direct reports of top level
                    this.employees.filter(e => e.reporting_manager_id === emp.id).forEach(sub => {
                        this.expanded[sub.id] = true;
                    });
                }
            });
        },

        toggle(id) {
            this.expanded[id] = !this.expanded[id];
        },

        isExpanded(id) {
            return this.expanded[id] === true;
        },

        expandAll() {
            this.employees.forEach(emp => {
                this.expanded[emp.id] = true;
            });
        },

        collapseAll() {
            this.expanded = {};
        },

        getReportCount(id) {
            return this.employees.filter(e => e.reporting_manager_id === id).length;
        },

        matchesSearch(name) {
            if (!this.searchQuery) return true;
            return name.toLowerCase().includes(this.searchQuery.toLowerCase());
        },

        hasMatchingDescendant(id) {
            if (!this.searchQuery) return false;
            const directReports = this.employees.filter(e => e.reporting_manager_id === id);
            for (const report of directReports) {
                if (this.matchesSearch(report.name) || this.hasMatchingDescendant(report.id)) {
                    return true;
                }
            }
            return false;
        }
    };
}
</script>

</x-layouts.hr>
