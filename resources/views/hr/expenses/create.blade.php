<x-layouts.hr title="New Expense Claim" currentView="expenses">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    title: '',
    items: [
        { category_id: '', description: '', amount: 0, expense_date: '', receipt: null, receiptName: '' }
    ],
    submitting: false,
    errors: {},
    categories: @js($categories),

    get total() {
        return this.items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    },

    addItem() {
        this.items.push({ category_id: '', description: '', amount: 0, expense_date: '', receipt: null, receiptName: '' });
    },

    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },

    getCategoryMaxAmount(catId) {
        const cat = this.categories.find(c => c.id == catId);
        return cat ? cat.max_amount : null;
    },

    getCategoryRequiresReceipt(catId) {
        const cat = this.categories.find(c => c.id == catId);
        return cat ? cat.requires_receipt : false;
    },

    handleFileSelect(index, event) {
        const file = event.target.files[0];
        if (file) {
            this.items[index].receipt = file;
            this.items[index].receiptName = file.name;
        }
    },

    async submitClaim() {
        this.submitting = true;
        this.errors = {};

        try {
            const formData = new FormData();
            formData.append('title', this.title);

            this.items.forEach((item, idx) => {
                formData.append(`items[${idx}][category_id]`, item.category_id);
                formData.append(`items[${idx}][description]`, item.description);
                formData.append(`items[${idx}][amount]`, item.amount);
                formData.append(`items[${idx}][expense_date]`, item.expense_date);
                if (item.receipt) {
                    formData.append(`items[${idx}][receipt]`, item.receipt);
                }
            });

            const response = await fetch('/api/hr/expense-claims', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    this.errors = data.errors;
                } else {
                    this.errors = { general: [data.message || 'Failed to create claim'] };
                }
                return;
            }

            window.location.href = data.redirect || '{{ route('hr.expenses.index') }}';
        } catch (err) {
            this.errors = { general: ['Network error. Please try again.'] };
        } finally {
            this.submitting = false;
        }
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('hr.expenses.index') }}"
           class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] flex items-center justify-center transition-colors">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">New Expense Claim</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Create a new expense reimbursement request</p>
        </div>
    </div>

    {{-- General Error --}}
    <template x-if="errors.general">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-3.5">
            <template x-for="err in errors.general" :key="err">
                <p class="text-[13px] text-red-400" x-text="err"></p>
            </template>
        </div>
    </template>

    {{-- Claim Form --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">

        {{-- Title Section --}}
        <div class="px-6 py-5 border-b border-white/[0.06]">
            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Claim Title</label>
            <input type="text" x-model="title" placeholder="e.g., March Travel Expenses"
                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 placeholder-white/25 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
            <template x-if="errors.title">
                <p class="text-[12px] text-red-400 mt-1.5" x-text="errors.title[0]"></p>
            </template>
        </div>

        {{-- Line Items --}}
        <div class="px-6 py-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-[14px] font-semibold text-white/85">Expense Items</h2>
                <div class="flex items-center gap-3">
                    <span class="text-[13px] text-white/40">
                        Running Total:
                        <span class="text-white/85 font-bold tabular-nums" x-text="total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                    </span>
                </div>
            </div>

            <template x-for="(item, index) in items" :key="index">
                <div class="bg-white/[0.02] border border-white/[0.06] rounded-xl p-5 space-y-4 relative group">
                    {{-- Remove button --}}
                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                            class="absolute top-3 right-3 w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-6 h-6 rounded-md bg-cyan-500/10 text-cyan-400 text-[10px] font-bold flex items-center justify-center" x-text="index + 1"></span>
                        <span class="text-[12px] font-medium text-white/45">Item</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Category --}}
                        <div>
                            <label class="block text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1.5">Category</label>
                            <select x-model="item.category_id"
                                    class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-3 py-2.5 text-[13px] text-white/80 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors appearance-none">
                                <option value="" class="bg-[#17172A]">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" class="bg-[#17172A]">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <template x-if="getCategoryMaxAmount(item.category_id)">
                                <p class="text-[11px] text-cyan-400/60 mt-1">
                                    Max: <span x-text="parseFloat(getCategoryMaxAmount(item.category_id)).toLocaleString('en-IN', { minimumFractionDigits: 2 })"></span>
                                </p>
                            </template>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1.5">Description</label>
                            <input type="text" x-model="item.description" placeholder="Brief description"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-3 py-2.5 text-[13px] text-white/80 placeholder-white/20 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1.5">Amount</label>
                            <input type="number" x-model.number="item.amount" placeholder="0.00" min="0" step="0.01"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-3 py-2.5 text-[13px] text-white/80 placeholder-white/20 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors tabular-nums">
                        </div>

                        {{-- Date --}}
                        <div>
                            <label class="block text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1.5">Expense Date</label>
                            <input type="date" x-model="item.expense_date"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-3 py-2.5 text-[13px] text-white/80 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                    </div>

                    {{-- Receipt Upload --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-white/30 uppercase tracking-widest mb-1.5">
                            Receipt
                            <template x-if="getCategoryRequiresReceipt(item.category_id)">
                                <span class="text-red-400 ml-1">*Required</span>
                            </template>
                        </label>
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center gap-2 px-3 py-2 bg-white/[0.04] hover:bg-white/[0.06] border border-white/[0.08] rounded-lg cursor-pointer transition-colors">
                                <svg class="w-4 h-4 text-white/35" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="text-[12px] text-white/45">Attach file</span>
                                <input type="file" class="hidden" accept=".jpg,.jpeg,.png,.pdf" @change="handleFileSelect(index, $event)">
                            </label>
                            <template x-if="item.receiptName">
                                <span class="text-[12px] text-cyan-400/70 truncate max-w-[200px]" x-text="item.receiptName"></span>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Add Item Button --}}
            <button type="button" @click="addItem()"
                    class="w-full py-3 border-2 border-dashed border-white/[0.08] hover:border-cyan-500/30 rounded-xl text-[13px] font-medium text-white/35 hover:text-cyan-400 transition-all duration-200 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Item
            </button>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-between bg-white/[0.01]">
            <div>
                <span class="text-[12px] text-white/35">Total Amount</span>
                <p class="text-[22px] font-bold text-white/85 tabular-nums" x-text="total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.expenses.index') }}"
                   class="px-5 py-2.5 text-[13px] font-medium text-white/45 hover:text-white/65 bg-white/[0.04] hover:bg-white/[0.06] rounded-lg border border-white/[0.06] transition-colors">
                    Cancel
                </a>
                <button type="button" @click="submitClaim()" :disabled="submitting"
                        class="px-6 py-2.5 text-[13px] font-semibold text-white bg-cyan-500/80 hover:bg-cyan-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2">
                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span x-text="submitting ? 'Saving...' : 'Save as Draft'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

</x-layouts.hr>
