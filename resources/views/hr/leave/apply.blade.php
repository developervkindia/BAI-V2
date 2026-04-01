<x-layouts.hr title="Apply Leave" currentView="leave-apply">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    csrf: document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
    form: {
        leave_type_id: '',
        start_date: '',
        end_date: '',
        is_half_day: false,
        half_day_period: 'first_half',
        reason: '',
    },
    leaveTypes: @js($leaveTypes->map(fn($t) => [
        'id' => $t->id,
        'name' => $t->name,
        'code' => $t->code,
        'color' => $t->color ?? '#06b6d4',
        'is_paid' => $t->is_paid,
        'max_days' => $t->max_days_per_year,
    ])),
    balances: @js($balances->keyBy('hr_leave_type_id')->map(fn($b) => [
        'available' => $b->available,
        'used' => $b->used,
        'total' => $b->leaveType->max_days_per_year ?? 0,
    ])),
    calculatedDays: 0,
    submitting: false,
    submitted: false,
    errors: {},
    successMessage: '',

    get selectedType() {
        if (!this.form.leave_type_id) return null;
        return this.leaveTypes.find(t => t.id == this.form.leave_type_id) || null;
    },
    get selectedBalance() {
        if (!this.form.leave_type_id) return null;
        return this.balances[this.form.leave_type_id] || { available: 0, used: 0, total: 0 };
    },
    get exceedsBalance() {
        if (!this.selectedBalance) return false;
        return this.calculatedDays > this.selectedBalance.available;
    },

    calculateDays() {
        if (!this.form.start_date || !this.form.end_date) {
            this.calculatedDays = 0;
            return;
        }
        if (this.form.is_half_day) {
            this.calculatedDays = 0.5;
            this.form.end_date = this.form.start_date;
            return;
        }
        const start = new Date(this.form.start_date);
        const end = new Date(this.form.end_date);
        if (end < start) {
            this.calculatedDays = 0;
            return;
        }
        let count = 0;
        let current = new Date(start);
        while (current <= end) {
            const day = current.getDay();
            if (day !== 0 && day !== 6) {
                count++;
            }
            current.setDate(current.getDate() + 1);
        }
        this.calculatedDays = count;
    },

    async submitForm() {
        this.errors = {};
        this.successMessage = '';

        if (!this.form.leave_type_id) {
            this.errors.leave_type_id = 'Please select a leave type';
            return;
        }
        if (!this.form.start_date) {
            this.errors.start_date = 'Start date is required';
            return;
        }
        if (!this.form.end_date) {
            this.errors.end_date = 'End date is required';
            return;
        }
        if (this.calculatedDays <= 0) {
            this.errors.start_date = 'Invalid date range';
            return;
        }
        if (!this.form.reason || this.form.reason.trim().length < 3) {
            this.errors.reason = 'Please provide a reason (min 3 characters)';
            return;
        }

        this.submitting = true;

        try {
            const res = await fetch('/api/hr/leave-requests', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({
                    hr_leave_type_id: this.form.leave_type_id,
                    start_date: this.form.start_date,
                    end_date: this.form.end_date,
                    is_half_day: this.form.is_half_day,
                    half_day_period: this.form.is_half_day ? this.form.half_day_period : null,
                    days: this.calculatedDays,
                    reason: this.form.reason.trim(),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                if (data.errors) {
                    this.errors = data.errors;
                } else {
                    this.errors.general = data.message || 'Something went wrong. Please try again.';
                }
                return;
            }

            this.submitted = true;
            this.successMessage = data.message || 'Leave request submitted successfully!';

            // Reset form after short delay
            setTimeout(() => {
                window.location.href = '{{ route('hr.leave.my') }}';
            }, 1500);

        } catch (err) {
            this.errors.general = 'Network error. Please check your connection and try again.';
        } finally {
            this.submitting = false;
        }
    },
}"
x-init="
    $watch('form.start_date', () => calculateDays());
    $watch('form.end_date', () => calculateDays());
    $watch('form.is_half_day', (val) => {
        if (val && form.start_date) {
            form.end_date = form.start_date;
        }
        calculateDays();
    });
">

    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('hr.leave.index') }}" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Apply for Leave</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Submit a new leave request</p>
        </div>
    </div>

    {{-- Success Message --}}
    <div x-show="submitted" x-cloak x-transition
         class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <p class="text-[13px] font-semibold text-emerald-400" x-text="successMessage"></p>
            <p class="text-[12px] text-emerald-400/60 mt-0.5">Redirecting to your leaves...</p>
        </div>
    </div>

    {{-- General Error --}}
    <template x-if="errors.general">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-[13px] text-red-400" x-text="errors.general"></p>
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5" x-show="!submitted">

        {{-- Form Card --}}
        <div class="lg:col-span-2 bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06]">
                <h2 class="text-[14px] font-semibold text-white/85">Leave Details</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Fill in the details for your leave request</p>
            </div>

            <form @submit.prevent="submitForm()" class="p-5 space-y-5">

                {{-- Leave Type --}}
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 uppercase tracking-wider mb-2">Leave Type</label>
                    <select x-model="form.leave_type_id"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.05] border text-[13px] text-white/80 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 transition-colors appearance-none"
                            :class="errors.leave_type_id ? 'border-red-500/40' : 'border-white/[0.08]'">
                        <option value="" disabled class="bg-[#17172A] text-white/50">Select leave type</option>
                        <template x-for="type in leaveTypes" :key="type.id">
                            <option :value="type.id" class="bg-[#17172A] text-white/80" x-text="type.name + ' (' + type.code + ')'"></option>
                        </template>
                    </select>
                    <template x-if="errors.leave_type_id">
                        <p class="text-[11px] text-red-400 mt-1" x-text="errors.leave_type_id"></p>
                    </template>
                </div>

                {{-- Date Range --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-white/50 uppercase tracking-wider mb-2">Start Date</label>
                        <input type="date" x-model="form.start_date"
                               class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.05] border text-[13px] text-white/80 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 transition-colors [color-scheme:dark]"
                               :class="errors.start_date ? 'border-red-500/40' : 'border-white/[0.08]'"
                               :min="new Date().toISOString().split('T')[0]">
                        <template x-if="errors.start_date">
                            <p class="text-[11px] text-red-400 mt-1" x-text="errors.start_date"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-white/50 uppercase tracking-wider mb-2">End Date</label>
                        <input type="date" x-model="form.end_date"
                               class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.05] border text-[13px] text-white/80 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 transition-colors [color-scheme:dark]"
                               :class="errors.end_date ? 'border-red-500/40' : 'border-white/[0.08]'"
                               :min="form.start_date || new Date().toISOString().split('T')[0]"
                               :disabled="form.is_half_day">
                        <template x-if="errors.end_date">
                            <p class="text-[11px] text-red-400 mt-1" x-text="errors.end_date"></p>
                        </template>
                    </div>
                </div>

                {{-- Half Day Toggle --}}
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" x-model="form.is_half_day" class="sr-only peer">
                            <div class="w-9 h-5 bg-white/10 rounded-full peer-checked:bg-cyan-500/50 transition-colors"></div>
                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white/60 rounded-full peer-checked:translate-x-4 peer-checked:bg-white transition-all"></div>
                        </div>
                        <span class="text-[13px] text-white/60">Half Day</span>
                    </label>

                    <div x-show="form.is_half_day" x-transition class="flex items-center gap-2">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="form.half_day_period" value="first_half"
                                   class="w-3.5 h-3.5 bg-white/[0.06] border border-white/[0.15] text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-0">
                            <span class="text-[12px] text-white/55">First Half</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="form.half_day_period" value="second_half"
                                   class="w-3.5 h-3.5 bg-white/[0.06] border border-white/[0.15] text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-0">
                            <span class="text-[12px] text-white/55">Second Half</span>
                        </label>
                    </div>
                </div>

                {{-- Calculated Days --}}
                <div x-show="calculatedDays > 0" x-transition
                     class="flex items-center gap-3 p-3.5 rounded-lg"
                     :class="exceedsBalance ? 'bg-red-500/10 border border-red-500/20' : 'bg-cyan-500/10 border border-cyan-500/20'">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                         :class="exceedsBalance ? 'bg-red-500/20' : 'bg-cyan-500/20'">
                        <svg class="w-4 h-4" :class="exceedsBalance ? 'text-red-400' : 'text-cyan-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold" :class="exceedsBalance ? 'text-red-400' : 'text-cyan-400'">
                            <span x-text="calculatedDays"></span> working day<span x-show="calculatedDays !== 1">s</span>
                        </p>
                        <p x-show="exceedsBalance" class="text-[11px] text-red-400/70 mt-0.5">
                            Exceeds available balance (<span x-text="selectedBalance?.available ?? 0"></span> days remaining)
                        </p>
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 uppercase tracking-wider mb-2">Reason</label>
                    <textarea x-model="form.reason" rows="3"
                              placeholder="Provide a reason for your leave..."
                              class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.05] border text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 transition-colors resize-none"
                              :class="errors.reason ? 'border-red-500/40' : 'border-white/[0.08]'"></textarea>
                    <template x-if="errors.reason">
                        <p class="text-[11px] text-red-400 mt-1" x-text="errors.reason"></p>
                    </template>
                </div>

                {{-- Submit --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            :disabled="submitting"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="submitting ? 'Submitting...' : 'Submit Request'"></span>
                    </button>
                    <a href="{{ route('hr.leave.index') }}" class="px-5 py-2.5 rounded-lg border border-white/[0.08] text-[13px] font-medium text-white/50 hover:text-white/70 hover:border-white/[0.15] transition-colors">
                        Cancel
                    </a>
                </div>

            </form>
        </div>

        {{-- Sidebar: Balance Summary --}}
        <div class="space-y-4">
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06]">
                    <h2 class="text-[14px] font-semibold text-white/85">Leave Balances</h2>
                    <p class="text-[12px] text-white/35 mt-0.5">Available for {{ now()->format('Y') }}</p>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    <template x-for="type in leaveTypes" :key="type.id">
                        <div class="px-5 py-3.5 flex items-center justify-between hover:bg-white/[0.02] transition-colors cursor-pointer"
                             :class="form.leave_type_id == type.id ? 'bg-white/[0.03] border-l-2' : 'border-l-2 border-transparent'"
                             :style="form.leave_type_id == type.id ? 'border-left-color:' + type.color : ''"
                             @click="form.leave_type_id = type.id">
                            <div class="flex items-center gap-2.5">
                                <div class="w-2.5 h-2.5 rounded-full shrink-0" :style="'background-color:' + type.color"></div>
                                <div>
                                    <p class="text-[13px] font-medium text-white/70" x-text="type.name"></p>
                                    <p class="text-[10px] text-white/30 font-mono" x-text="type.code"></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[15px] font-bold text-white/80 tabular-nums" x-text="balances[type.id]?.available ?? 0"></p>
                                <p class="text-[10px] text-white/30">of <span x-text="type.max_days"></span></p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty --}}
                <template x-if="leaveTypes.length === 0">
                    <div class="px-5 py-8 text-center">
                        <p class="text-[13px] text-white/30">No leave types configured</p>
                    </div>
                </template>
            </div>

            {{-- Quick Info --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 space-y-3">
                <h3 class="text-[12px] font-semibold text-white/50 uppercase tracking-wider">Quick Info</h3>
                <div class="space-y-2.5">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-white/25 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-[12px] text-white/40 leading-relaxed">Weekends (Sat & Sun) are automatically excluded from leave calculation.</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-white/25 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-[12px] text-white/40 leading-relaxed">Half day leaves can only be for a single day.</p>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-white/25 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-[12px] text-white/40 leading-relaxed">Pending requests can be cancelled from the My Leaves page.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

</x-layouts.hr>
