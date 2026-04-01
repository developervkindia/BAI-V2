<x-layouts.hr title="My Review" currentView="my-review">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    currentCycle: {{ Js::from($currentCycle) }},
    review: {{ Js::from($review) }},
    goals: {{ Js::from($goals) }},
    employee: {{ Js::from($employee) }},

    form: {
        overall_rating: {{ $review->overall_rating ?? 0 }},
        strengths: {{ Js::from($review->strengths ?? '') }},
        improvements: {{ Js::from($review->improvements ?? '') }},
        comments: {{ Js::from($review->comments ?? '') }},
    },

    goalRatings: {},
    submitting: false,
    savingGoal: null,
    submitSuccess: false,
    submitError: '',

    init() {
        // Initialize goal ratings from existing data
        if (this.goals) {
            this.goals.forEach(g => {
                this.goalRatings[g.id] = {
                    rating: g.pivot_rating || g.review_rating || 0,
                    comments: g.pivot_comments || g.review_comments || '',
                };
            });
        }
    },

    get isSubmitted() {
        return this.review && this.review.status === 'submitted';
    },

    get csrfToken() {
        return document.querySelector('meta[name=&quot;csrf-token&quot;]').content;
    },

    formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },

    cycleStatusColor(status) {
        const colors = {
            'draft': 'text-white/45 bg-white/[0.06]',
            'active': 'text-emerald-400 bg-emerald-500/10',
            'self_review': 'text-amber-400 bg-amber-500/10',
            'manager_review': 'text-blue-400 bg-blue-500/10',
            'calibration': 'text-violet-400 bg-violet-500/10',
            'closed': 'text-red-400 bg-red-500/10',
        };
        return colors[status] || 'text-white/45 bg-white/[0.06]';
    },

    setRating(val) {
        if (this.isSubmitted) return;
        this.form.overall_rating = val;
    },

    setGoalRating(goalId, val) {
        if (this.isSubmitted) return;
        if (!this.goalRatings[goalId]) this.goalRatings[goalId] = { rating: 0, comments: '' };
        this.goalRatings[goalId].rating = val;
    },

    async saveGoalRating(goalId) {
        if (this.isSubmitted || !this.review) return;
        this.savingGoal = goalId;
        try {
            const resp = await fetch('/api/hr/review-ratings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    review_id: this.review.id,
                    goal_id: goalId,
                    rating: this.goalRatings[goalId]?.rating || 0,
                    comments: this.goalRatings[goalId]?.comments || '',
                }),
            });
            if (!resp.ok) {
                const data = await resp.json();
                alert(data.message || 'Failed to save goal rating');
            }
        } catch (e) {
            alert('Network error saving goal rating.');
        } finally {
            this.savingGoal = null;
        }
    },

    async submitReview() {
        if (this.isSubmitted || !this.review) return;
        if (this.form.overall_rating < 1) {
            this.submitError = 'Please select an overall rating before submitting.';
            return;
        }
        this.submitting = true;
        this.submitError = '';
        this.submitSuccess = false;

        try {
            const resp = await fetch('/api/hr/reviews/' + this.review.id + '/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    overall_rating: this.form.overall_rating,
                    strengths: this.form.strengths,
                    improvements: this.form.improvements,
                    comments: this.form.comments,
                    goal_ratings: this.goalRatings,
                }),
            });

            if (resp.ok) {
                this.submitSuccess = true;
                this.review.status = 'submitted';
                this.review.submitted_at = new Date().toISOString();
            } else {
                const data = await resp.json();
                this.submitError = data.message || 'Failed to submit review. Please try again.';
            }
        } catch (e) {
            this.submitError = 'Network error. Please check your connection and try again.';
        } finally {
            this.submitting = false;
        }
    },

    goalProgress(goal) {
        if (!goal.target_value || goal.target_value === 0) return 0;
        return Math.min(Math.round((goal.current_value / goal.target_value) * 100), 100);
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">My Review</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Self-assessment and performance review</p>
        </div>
    </div>

    {{-- No Active Cycle --}}
    <template x-if="!currentCycle">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/[0.04] flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <h3 class="text-[16px] font-semibold text-white/50 mb-1">No Active Review Cycle</h3>
            <p class="text-[13px] text-white/30 max-w-md mx-auto">There is no active review cycle at the moment. You will be notified when a new review cycle begins and your self-assessment is due.</p>
            <a href="{{ route('hr.performance.index') }}" class="inline-flex items-center gap-2 mt-5 px-4 py-2 rounded-lg bg-white/[0.06] text-white/50 text-[13px] font-medium hover:bg-white/[0.1] hover:text-white/70 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Performance
            </a>
        </div>
    </template>

    {{-- Active Cycle with Review --}}
    <template x-if="currentCycle">
        <div class="space-y-6">

            {{-- Cycle Info --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-[16px] font-semibold text-white/85" x-text="currentCycle.name"></h2>
                            <span class="text-[11px] font-semibold capitalize px-2.5 py-0.5 rounded-full" :class="cycleStatusColor(currentCycle.status)" x-text="currentCycle.status.replace('_', ' ')"></span>
                        </div>
                        <div class="flex items-center gap-4 text-[12px] text-white/40">
                            <span class="capitalize" x-text="currentCycle.type.replace('_', ' ')"></span>
                            <span x-text="formatDate(currentCycle.start_date) + ' — ' + formatDate(currentCycle.end_date)"></span>
                        </div>
                    </div>
                    <template x-if="isSubmitted">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-[12px] font-semibold text-emerald-400">Submitted</span>
                            <span class="text-[11px] text-emerald-400/60" x-text="review.submitted_at ? formatDate(review.submitted_at) : ''"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Success Message --}}
            <template x-if="submitSuccess">
                <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[13px] text-emerald-400 font-medium">Your review has been submitted successfully!</p>
                </div>
            </template>

            {{-- Error Message --}}
            <template x-if="submitError">
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[13px] text-red-400 font-medium" x-text="submitError"></p>
                </div>
            </template>

            <template x-if="review">
                <div class="space-y-6">

                    {{-- Overall Rating --}}
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-white/[0.06]">
                            <h3 class="text-[14px] font-semibold text-white/85">Overall Self-Rating</h3>
                            <p class="text-[12px] text-white/35 mt-0.5">Rate your overall performance for this cycle</p>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-1">
                                <template x-for="star in [1,2,3,4,5]" :key="star">
                                    <button @click="setRating(star)"
                                            :disabled="isSubmitted"
                                            class="p-1 transition-all duration-150"
                                            :class="isSubmitted ? 'cursor-default' : 'cursor-pointer hover:scale-110'">
                                        <svg class="w-8 h-8 transition-colors" :class="star <= form.overall_rating ? 'text-amber-400' : 'text-white/10'" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                </template>
                                <span class="text-[14px] font-bold ml-3 tabular-nums" :class="form.overall_rating > 0 ? 'text-amber-400' : 'text-white/20'" x-text="form.overall_rating > 0 ? form.overall_rating + '/5' : 'Not rated'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Self Review Form --}}
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                        <div class="px-5 py-4 border-b border-white/[0.06]">
                            <h3 class="text-[14px] font-semibold text-white/85">Self Assessment</h3>
                            <p class="text-[12px] text-white/35 mt-0.5">Share your reflections on this review period</p>
                        </div>
                        <div class="p-5 space-y-5">

                            {{-- Strengths --}}
                            <div>
                                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Key Strengths</label>
                                <template x-if="!isSubmitted">
                                    <textarea x-model="form.strengths"
                                              rows="3"
                                              placeholder="Describe your key strengths and accomplishments this period..."
                                              class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[13px] text-white/75 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 focus:bg-white/[0.06] resize-none transition-all"></textarea>
                                </template>
                                <template x-if="isSubmitted">
                                    <div class="px-3.5 py-2.5 rounded-lg bg-white/[0.03] border border-white/[0.05] text-[13px] text-white/60 min-h-[60px]" x-text="form.strengths || 'No response provided'"></div>
                                </template>
                            </div>

                            {{-- Areas for Improvement --}}
                            <div>
                                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Areas for Improvement</label>
                                <template x-if="!isSubmitted">
                                    <textarea x-model="form.improvements"
                                              rows="3"
                                              placeholder="Identify areas where you can grow and improve..."
                                              class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[13px] text-white/75 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 focus:bg-white/[0.06] resize-none transition-all"></textarea>
                                </template>
                                <template x-if="isSubmitted">
                                    <div class="px-3.5 py-2.5 rounded-lg bg-white/[0.03] border border-white/[0.05] text-[13px] text-white/60 min-h-[60px]" x-text="form.improvements || 'No response provided'"></div>
                                </template>
                            </div>

                            {{-- Additional Comments --}}
                            <div>
                                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Additional Comments</label>
                                <template x-if="!isSubmitted">
                                    <textarea x-model="form.comments"
                                              rows="3"
                                              placeholder="Any additional thoughts or feedback..."
                                              class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[13px] text-white/75 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 focus:bg-white/[0.06] resize-none transition-all"></textarea>
                                </template>
                                <template x-if="isSubmitted">
                                    <div class="px-3.5 py-2.5 rounded-lg bg-white/[0.03] border border-white/[0.05] text-[13px] text-white/60 min-h-[60px]" x-text="form.comments || 'No response provided'"></div>
                                </template>
                            </div>

                        </div>
                    </div>

                    {{-- Goal Ratings --}}
                    <template x-if="goals && goals.length > 0">
                        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                            <div class="px-5 py-4 border-b border-white/[0.06]">
                                <h3 class="text-[14px] font-semibold text-white/85">Goal Ratings</h3>
                                <p class="text-[12px] text-white/35 mt-0.5">Rate your performance on each assigned goal</p>
                            </div>
                            <div class="divide-y divide-white/[0.05]">
                                <template x-for="goal in goals" :key="goal.id">
                                    <div class="p-5">
                                        {{-- Goal Info --}}
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-[13px] font-semibold text-white/80" x-text="goal.title"></h4>
                                                <p class="text-[12px] text-white/35 mt-0.5 line-clamp-2" x-text="goal.description"></p>
                                            </div>
                                            <template x-if="goal.weightage">
                                                <span class="text-[10px] font-semibold text-white/30 bg-white/[0.04] px-2 py-0.5 rounded ml-3 shrink-0" x-text="goal.weightage + '% weight'"></span>
                                            </template>
                                        </div>

                                        {{-- Goal Progress --}}
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="flex-1 h-[5px] bg-white/[0.06] rounded-full overflow-hidden">
                                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-500/80 to-cyan-400/60 transition-all"
                                                     :style="'width: ' + goalProgress(goal) + '%'"></div>
                                            </div>
                                            <span class="text-[11px] font-medium text-white/40 tabular-nums shrink-0" x-text="goalProgress(goal) + '% (' + (goal.current_value || 0) + '/' + (goal.target_value || 0) + ')'"></span>
                                        </div>

                                        {{-- Rating Stars for Goal --}}
                                        <div class="flex items-center gap-4 mb-3">
                                            <span class="text-[11px] font-semibold text-white/40">Your Rating:</span>
                                            <div class="flex items-center gap-0.5">
                                                <template x-for="star in [1,2,3,4,5]" :key="'goal-' + goal.id + '-star-' + star">
                                                    <button @click="setGoalRating(goal.id, star)"
                                                            :disabled="isSubmitted"
                                                            class="p-0.5 transition-all duration-150"
                                                            :class="isSubmitted ? 'cursor-default' : 'cursor-pointer hover:scale-110'">
                                                        <svg class="w-5 h-5 transition-colors" :class="star <= (goalRatings[goal.id]?.rating || 0) ? 'text-amber-400' : 'text-white/10'" viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                        </svg>
                                                    </button>
                                                </template>
                                                <span class="text-[12px] font-semibold ml-1.5 tabular-nums" :class="(goalRatings[goal.id]?.rating || 0) > 0 ? 'text-amber-400' : 'text-white/20'" x-text="(goalRatings[goal.id]?.rating || 0) > 0 ? (goalRatings[goal.id]?.rating || 0) + '/5' : '—'"></span>
                                            </div>
                                        </div>

                                        {{-- Goal Comments --}}
                                        <template x-if="!isSubmitted">
                                            <div class="flex items-end gap-2">
                                                <div class="flex-1">
                                                    <textarea x-model="goalRatings[goal.id].comments"
                                                              rows="2"
                                                              placeholder="Comments on this goal..."
                                                              class="w-full px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.08] text-[12px] text-white/70 placeholder-white/20 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 resize-none transition-all"></textarea>
                                                </div>
                                                <button @click="saveGoalRating(goal.id)"
                                                        :disabled="savingGoal === goal.id"
                                                        class="px-3 py-2 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/50 hover:text-white/70 text-[11px] font-semibold transition-all shrink-0 disabled:opacity-50">
                                                    <span x-text="savingGoal === goal.id ? 'Saving...' : 'Save'"></span>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="isSubmitted && goalRatings[goal.id]?.comments">
                                            <div class="px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.05] text-[12px] text-white/50" x-text="goalRatings[goal.id].comments"></div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Submit Button --}}
                    <template x-if="!isSubmitted">
                        <div class="flex items-center justify-between bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
                            <div>
                                <p class="text-[13px] text-white/50">
                                    <template x-if="currentCycle.self_review_deadline">
                                        <span>Deadline: <span class="text-white/70 font-medium" x-text="formatDate(currentCycle.self_review_deadline)"></span></span>
                                    </template>
                                </p>
                                <p class="text-[11px] text-white/30 mt-0.5">Once submitted, you will not be able to make changes.</p>
                            </div>
                            <button @click="submitReview()"
                                    :disabled="submitting"
                                    class="flex items-center gap-2 px-5 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span x-text="submitting ? 'Submitting...' : 'Submit Review'"></span>
                            </button>
                        </div>
                    </template>

                </div>
            </template>

            {{-- No Review Yet (cycle exists but no review assigned) --}}
            <template x-if="!review">
                <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-10 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-[16px] font-semibold text-white/50 mb-1">Review Not Yet Assigned</h3>
                    <p class="text-[13px] text-white/30 max-w-md mx-auto">An active review cycle is in progress, but your review has not been assigned yet. Please check back later or contact your manager.</p>
                </div>
            </template>

        </div>
    </template>

</div>

</x-layouts.hr>
