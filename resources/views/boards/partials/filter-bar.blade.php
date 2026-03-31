<!-- Filter Bar -->
<div
    x-show="showFilters"
    @toggle-filter-bar.window="showFilters = !showFilters"
    style="display: none"
    class="bg-neutral-900/95 backdrop-blur-sm border-b border-white/5 px-4 py-2.5 z-20"
>
    <div class="flex items-center gap-3 flex-wrap">
        <!-- Keyword -->
        <input type="text" x-model="filters.keyword" placeholder="Search cards..."
            class="w-40 rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-white/80 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-white/20" />

        <!-- Labels -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs border border-white/10 hover:bg-white/5 transition-colors"
                :class="filters.labels.length ? 'bg-blue-500/20 border-blue-500/30 text-blue-400' : 'text-white/50'">
                Labels
                <span x-show="filters.labels.length" x-text="filters.labels.length" class="bg-blue-500 text-white text-[9px] rounded-full w-3.5 h-3.5 flex items-center justify-center"></span>
            </button>
            <div x-show="open" @click.away="open = false" style="display:none" class="absolute top-full left-0 mt-1 w-52 bg-neutral-800 rounded-lg shadow-xl border border-white/10 py-1 z-50 max-h-60 overflow-y-auto">
                <template x-for="label in allLabels" :key="label.id">
                    <button @click="toggleFilterLabel(label.id)" class="w-full text-left px-3 py-1.5 text-xs flex items-center gap-2 hover:bg-white/5">
                        <span class="w-4 h-4 rounded" :style="'background:' + getLabelHex(label.color)"></span>
                        <span class="flex-1 text-white/60" x-text="label.name || label.color"></span>
                        <svg x-show="filters.labels.includes(label.id)" class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </template>
            </div>
        </div>

        <!-- Members -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs border border-white/10 hover:bg-white/5 transition-colors"
                :class="filters.members.length ? 'bg-blue-500/20 border-blue-500/30 text-blue-400' : 'text-white/50'">
                Members
                <span x-show="filters.members.length" x-text="filters.members.length" class="bg-blue-500 text-white text-[9px] rounded-full w-3.5 h-3.5 flex items-center justify-center"></span>
            </button>
            <div x-show="open" @click.away="open = false" style="display:none" class="absolute top-full left-0 mt-1 w-52 bg-neutral-800 rounded-lg shadow-xl border border-white/10 py-1 z-50 max-h-60 overflow-y-auto">
                <template x-for="member in boardMembers" :key="member.id">
                    <button @click="toggleFilterMember(member.id)" class="w-full text-left px-3 py-1.5 text-xs flex items-center gap-2 hover:bg-white/5">
                        <div class="w-5 h-5 rounded-full bg-white/10 text-white/50 text-[7px] font-bold flex items-center justify-center" x-text="member.name.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase()"></div>
                        <span class="flex-1 text-white/60" x-text="member.name"></span>
                        <svg x-show="filters.members.includes(member.id)" class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </button>
                </template>
            </div>
        </div>

        <!-- Due Date -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs border border-white/10 hover:bg-white/5 transition-colors"
                :class="filters.dueDate ? 'bg-blue-500/20 border-blue-500/30 text-blue-400' : 'text-white/50'">
                Due Date
            </button>
            <div x-show="open" @click.away="open = false" style="display:none" class="absolute top-full left-0 mt-1 w-44 bg-neutral-800 rounded-lg shadow-xl border border-white/10 py-1 z-50">
                <button @click="filters.dueDate = filters.dueDate === 'overdue' ? null : 'overdue'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-white/5" :class="filters.dueDate === 'overdue' ? 'text-red-400' : 'text-white/60'">Overdue</button>
                <button @click="filters.dueDate = filters.dueDate === 'due_soon' ? null : 'due_soon'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-white/5" :class="filters.dueDate === 'due_soon' ? 'text-amber-400' : 'text-white/60'">Due soon</button>
                <button @click="filters.dueDate = filters.dueDate === 'complete' ? null : 'complete'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-white/5" :class="filters.dueDate === 'complete' ? 'text-green-400' : 'text-white/60'">Complete</button>
                <button @click="filters.dueDate = filters.dueDate === 'no_date' ? null : 'no_date'; open = false" class="w-full text-left px-3 py-1.5 text-xs hover:bg-white/5" :class="filters.dueDate === 'no_date' ? 'text-white/80' : 'text-white/60'">No due date</button>
            </div>
        </div>

        <!-- Clear -->
        <button x-show="hasActiveFilters" @click="clearFilters()" style="display:none" class="text-xs text-red-400 hover:text-red-300 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear
        </button>
    </div>
</div>
