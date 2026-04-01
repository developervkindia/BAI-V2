<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // PHASE 1: Core HR — Departments, Designations, Onboarding, Exits
        // ═══════════════════════════════════════════════════════════════

        Schema::create('hr_departments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code', 20)->nullable();
            $t->foreignId('parent_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $t->foreignId('head_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('hr_designations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unsignedSmallInteger('level')->default(0);
            $t->foreignId('hr_department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $t->text('description')->nullable();
            $t->timestamps();
        });

        // Add FK columns to existing employee_profiles
        if (!Schema::hasColumn('employee_profiles', 'hr_department_id')) {
            Schema::table('employee_profiles', function (Blueprint $t) {
                $t->foreignId('hr_department_id')->nullable()->after('department')->constrained('hr_departments')->nullOnDelete();
                $t->foreignId('hr_designation_id')->nullable()->after('designation')->constrained('hr_designations')->nullOnDelete();
            });
        }

        Schema::create('hr_onboardings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->enum('status', ['pending_offer', 'offer_sent', 'documents_pending', 'joining_pending', 'completed', 'cancelled'])->default('pending_offer');
            $t->string('offer_letter_path', 500)->nullable();
            $t->date('expected_joining_date')->nullable();
            $t->date('actual_joining_date')->nullable();
            $t->json('checklist')->nullable();
            $t->foreignId('assigned_buddy_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('created_by')->constrained('users');
            $t->text('notes')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_onboarding_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_onboarding_id')->constrained('hr_onboardings')->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('is_completed')->default(false);
            $t->timestamp('completed_at')->nullable();
            $t->date('due_date')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('hr_exits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->enum('type', ['resignation', 'termination', 'retirement', 'absconding'])->default('resignation');
            $t->text('reason')->nullable();
            $t->date('resignation_date')->nullable();
            $t->date('last_working_date')->nullable();
            $t->enum('status', ['initiated', 'clearance_pending', 'fnf_pending', 'completed', 'cancelled'])->default('initiated');
            $t->decimal('fnf_amount', 12, 2)->nullable();
            $t->timestamp('fnf_settled_at')->nullable();
            $t->text('exit_interview_notes')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('hr_exit_clearances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_exit_id')->constrained('hr_exits')->cascadeOnDelete();
            $t->string('department');
            $t->foreignId('approver_id')->constrained('users');
            $t->enum('status', ['pending', 'cleared', 'rejected'])->default('pending');
            $t->text('remarks')->nullable();
            $t->timestamp('cleared_at')->nullable();
            $t->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 2: Attendance
        // ═══════════════════════════════════════════════════════════════

        Schema::create('hr_shifts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code', 20)->nullable();
            $t->time('start_time');
            $t->time('end_time');
            $t->unsignedSmallInteger('grace_minutes')->default(0);
            $t->boolean('is_night_shift')->default(false);
            $t->unsignedSmallInteger('break_duration_minutes')->default(0);
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('hr_shift_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->foreignId('hr_shift_id')->constrained('hr_shifts')->cascadeOnDelete();
            $t->date('effective_from');
            $t->date('effective_until')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_attendance_policies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->unsignedSmallInteger('late_mark_after_minutes')->default(15);
            $t->unsignedSmallInteger('half_day_after_minutes')->default(240);
            $t->unsignedSmallInteger('absent_after_minutes')->default(480);
            $t->unsignedSmallInteger('overtime_threshold_minutes')->default(0);
            $t->decimal('overtime_rate', 3, 2)->default(1.50);
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        Schema::create('hr_attendance_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->date('date');
            $t->timestamp('clock_in')->nullable();
            $t->timestamp('clock_out')->nullable();
            $t->string('clock_in_ip', 45)->nullable();
            $t->string('clock_out_ip', 45)->nullable();
            $t->decimal('total_hours', 5, 2)->nullable();
            $t->decimal('overtime_hours', 5, 2)->default(0);
            $t->enum('status', ['present', 'absent', 'half_day', 'late', 'on_leave', 'holiday', 'weekend'])->default('absent');
            $t->enum('source', ['web', 'mobile', 'biometric', 'manual'])->default('web');
            $t->text('remarks')->nullable();
            $t->foreignId('regularized_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['employee_profile_id', 'date']);
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 3: Leave Management
        // ═══════════════════════════════════════════════════════════════

        Schema::create('hr_leave_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code', 10);
            $t->string('color', 20)->default('#3B82F6');
            $t->boolean('is_paid')->default(true);
            $t->decimal('max_days_per_year', 5, 1)->default(0);
            $t->enum('accrual_type', ['annual', 'monthly', 'quarterly', 'none'])->default('annual');
            $t->decimal('accrual_count', 4, 1)->default(0);
            $t->unsignedSmallInteger('carry_forward_limit')->default(0);
            $t->boolean('encashable')->default(false);
            $t->boolean('requires_approval')->default(true);
            $t->decimal('min_days', 3, 1)->default(0.5);
            $t->unsignedSmallInteger('max_consecutive_days')->nullable();
            $t->boolean('sandwich_policy')->default(false);
            $t->enum('applicable_gender', ['all', 'male', 'female'])->default('all');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('hr_leave_balances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->foreignId('hr_leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $t->unsignedSmallInteger('year');
            $t->decimal('opening_balance', 5, 1)->default(0);
            $t->decimal('accrued', 5, 1)->default(0);
            $t->decimal('used', 5, 1)->default(0);
            $t->decimal('adjusted', 5, 1)->default(0);
            $t->decimal('carried_forward', 5, 1)->default(0);
            $t->decimal('encashed', 5, 1)->default(0);
            $t->decimal('available', 5, 1)->default(0);
            $t->timestamps();
            $t->unique(['employee_profile_id', 'hr_leave_type_id', 'year'], 'hr_leave_bal_emp_type_year_unique');
        });

        Schema::create('hr_leave_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->foreignId('hr_leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $t->date('start_date');
            $t->date('end_date');
            $t->decimal('days', 4, 1);
            $t->boolean('is_half_day')->default(false);
            $t->enum('half_day_period', ['first_half', 'second_half'])->nullable();
            $t->text('reason')->nullable();
            $t->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('rejection_reason')->nullable();
            $t->timestamp('applied_at')->nullable();
            $t->timestamp('actioned_at')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_comp_offs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->date('worked_on');
            $t->date('expires_on');
            $t->decimal('days', 3, 1)->default(1);
            $t->enum('status', ['pending', 'approved', 'used', 'expired'])->default('pending');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 4: Payroll
        // ═══════════════════════════════════════════════════════════════

        Schema::create('hr_salary_components', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('code', 20);
            $t->enum('type', ['earning', 'deduction', 'employer_contribution']);
            $t->enum('calculation_type', ['fixed', 'percentage'])->default('fixed');
            $t->string('percentage_of', 20)->nullable();
            $t->boolean('is_taxable')->default(true);
            $t->boolean('is_statutory')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('hr_salary_structures', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->decimal('annual_ctc', 12, 2);
            $t->date('effective_from');
            $t->date('effective_until')->nullable();
            $t->boolean('is_current')->default(true);
            $t->timestamps();
        });

        Schema::create('hr_salary_structure_components', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_salary_structure_id')->constrained('hr_salary_structures')->cascadeOnDelete();
            $t->foreignId('hr_salary_component_id')->constrained('hr_salary_components')->cascadeOnDelete();
            $t->decimal('monthly_amount', 10, 2);
            $t->decimal('annual_amount', 12, 2);
            $t->timestamps();
        });

        Schema::create('hr_payroll_runs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('month');
            $t->unsignedSmallInteger('year');
            $t->enum('status', ['draft', 'processing', 'processed', 'finalized', 'paid'])->default('draft');
            $t->decimal('total_gross', 14, 2)->default(0);
            $t->decimal('total_deductions', 14, 2)->default(0);
            $t->decimal('total_net', 14, 2)->default(0);
            $t->unsignedInteger('employee_count')->default(0);
            $t->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('processed_at')->nullable();
            $t->timestamp('finalized_at')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['organization_id', 'month', 'year']);
        });

        Schema::create('hr_payroll_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_payroll_run_id')->constrained('hr_payroll_runs')->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->decimal('gross_earnings', 10, 2)->default(0);
            $t->decimal('total_deductions', 10, 2)->default(0);
            $t->decimal('net_pay', 10, 2)->default(0);
            $t->unsignedSmallInteger('working_days')->default(0);
            $t->unsignedSmallInteger('days_present')->default(0);
            $t->decimal('lop_days', 4, 1)->default(0);
            $t->json('components')->nullable();
            $t->enum('status', ['draft', 'processed', 'held'])->default('draft');
            $t->timestamps();
        });

        Schema::create('hr_salary_revisions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->decimal('previous_ctc', 12, 2);
            $t->decimal('new_ctc', 12, 2);
            $t->date('effective_from');
            $t->text('reason')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('hr_tax_declarations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->string('financial_year', 10);
            $t->enum('regime', ['old', 'new'])->default('new');
            $t->json('declarations')->nullable();
            $t->enum('status', ['draft', 'submitted', 'verified'])->default('draft');
            $t->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 5: Performance Management
        // ═══════════════════════════════════════════════════════════════

        Schema::create('hr_review_cycles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('type', ['quarterly', 'half_yearly', 'annual'])->default('annual');
            $t->date('start_date');
            $t->date('end_date');
            $t->date('self_review_deadline')->nullable();
            $t->date('manager_review_deadline')->nullable();
            $t->enum('status', ['draft', 'active', 'self_review', 'manager_review', 'calibration', 'closed'])->default('draft');
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('hr_kras', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('hr_designation_id')->nullable()->constrained('hr_designations')->nullOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('weightage')->default(0);
            $t->timestamps();
        });

        Schema::create('hr_goals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->foreignId('hr_review_cycle_id')->nullable()->constrained('hr_review_cycles')->nullOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('hr_goals')->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->enum('goal_type', ['individual', 'team', 'org'])->default('individual');
            $t->enum('metric_type', ['numeric', 'percentage', 'binary'])->default('percentage');
            $t->decimal('target_value', 10, 2)->nullable();
            $t->decimal('current_value', 10, 2)->default(0);
            $t->unsignedSmallInteger('weightage')->default(0);
            $t->enum('status', ['not_started', 'in_progress', 'completed', 'cancelled'])->default('not_started');
            $t->date('start_date')->nullable();
            $t->date('due_date')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_review_cycle_id')->constrained('hr_review_cycles')->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->foreignId('reviewer_id')->constrained('users');
            $t->enum('review_type', ['self', 'manager', 'peer', 'skip_level'])->default('self');
            $t->decimal('overall_rating', 3, 1)->nullable();
            $t->text('strengths')->nullable();
            $t->text('improvements')->nullable();
            $t->text('comments')->nullable();
            $t->enum('status', ['pending', 'in_progress', 'submitted'])->default('pending');
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_review_ratings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_review_id')->constrained('hr_reviews')->cascadeOnDelete();
            $t->foreignId('hr_kra_id')->nullable()->constrained('hr_kras')->nullOnDelete();
            $t->foreignId('hr_goal_id')->nullable()->constrained('hr_goals')->nullOnDelete();
            $t->decimal('rating', 3, 1);
            $t->text('comments')->nullable();
            $t->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 6: Expenses + Recruitment
        // ═══════════════════════════════════════════════════════════════

        Schema::create('hr_expense_categories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->decimal('max_amount', 10, 2)->nullable();
            $t->boolean('requires_receipt')->default(true);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('hr_expense_claims', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->string('title');
            $t->decimal('total_amount', 10, 2)->default(0);
            $t->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'reimbursed'])->default('draft');
            $t->timestamp('submitted_at')->nullable();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('reimbursed_at')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_expense_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_expense_claim_id')->constrained('hr_expense_claims')->cascadeOnDelete();
            $t->foreignId('hr_expense_category_id')->constrained('hr_expense_categories');
            $t->text('description')->nullable();
            $t->decimal('amount', 10, 2);
            $t->date('expense_date');
            $t->string('receipt_path', 500)->nullable();
            $t->timestamps();
        });

        Schema::create('hr_job_postings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->foreignId('hr_department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $t->foreignId('hr_designation_id')->nullable()->constrained('hr_designations')->nullOnDelete();
            $t->text('description')->nullable();
            $t->text('requirements')->nullable();
            $t->string('employment_type', 30)->nullable();
            $t->string('location')->nullable();
            $t->decimal('salary_range_min', 10, 2)->nullable();
            $t->decimal('salary_range_max', 10, 2)->nullable();
            $t->unsignedSmallInteger('positions')->default(1);
            $t->enum('status', ['draft', 'open', 'on_hold', 'closed'])->default('draft');
            $t->foreignId('posted_by')->constrained('users');
            $t->timestamp('posted_at')->nullable();
            $t->timestamp('closed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_candidates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('hr_job_posting_id')->constrained('hr_job_postings')->cascadeOnDelete();
            $t->string('name');
            $t->string('email');
            $t->string('phone', 20)->nullable();
            $t->string('resume_path', 500)->nullable();
            $t->enum('source', ['portal', 'referral', 'direct', 'agency'])->default('direct');
            $t->enum('stage', ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected'])->default('applied');
            $t->string('current_company')->nullable();
            $t->string('current_designation')->nullable();
            $t->decimal('experience_years', 3, 1)->nullable();
            $t->decimal('expected_ctc', 12, 2)->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('hr_interviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_candidate_id')->constrained('hr_candidates')->cascadeOnDelete();
            $t->foreignId('interviewer_id')->constrained('users');
            $t->unsignedTinyInteger('round')->default(1);
            $t->timestamp('scheduled_at');
            $t->unsignedSmallInteger('duration_minutes')->default(60);
            $t->enum('mode', ['in_person', 'video', 'phone'])->default('video');
            $t->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $t->unsignedTinyInteger('rating')->nullable();
            $t->text('feedback')->nullable();
            $t->enum('decision', ['advance', 'hold', 'reject'])->nullable();
            $t->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // PHASE 7: Engagement
        // ═══════════════════════════════════════════════════════════════

        Schema::create('hr_surveys', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->enum('type', ['pulse', 'engagement', 'custom'])->default('custom');
            $t->boolean('is_anonymous')->default(false);
            $t->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('hr_survey_questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_survey_id')->constrained('hr_surveys')->cascadeOnDelete();
            $t->text('question');
            $t->enum('type', ['rating', 'text', 'multiple_choice', 'yes_no'])->default('rating');
            $t->json('options')->nullable();
            $t->boolean('is_required')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('hr_survey_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('hr_survey_id')->constrained('hr_surveys')->cascadeOnDelete();
            $t->foreignId('hr_survey_question_id')->constrained('hr_survey_questions')->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $t->text('answer')->nullable();
            $t->unsignedTinyInteger('rating')->nullable();
            $t->timestamps();
        });

        Schema::create('hr_announcements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('body');
            $t->enum('type', ['general', 'policy', 'event', 'celebration'])->default('general');
            $t->json('target_departments')->nullable();
            $t->boolean('is_pinned')->default(false);
            $t->timestamp('published_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
        });

        Schema::create('hr_recognitions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $t->foreignId('recognized_by')->constrained('users');
            $t->enum('type', ['badge', 'award', 'shoutout'])->default('shoutout');
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('badge_icon', 50)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_recognitions');
        Schema::dropIfExists('hr_announcements');
        Schema::dropIfExists('hr_survey_responses');
        Schema::dropIfExists('hr_survey_questions');
        Schema::dropIfExists('hr_surveys');
        Schema::dropIfExists('hr_interviews');
        Schema::dropIfExists('hr_candidates');
        Schema::dropIfExists('hr_job_postings');
        Schema::dropIfExists('hr_expense_items');
        Schema::dropIfExists('hr_expense_claims');
        Schema::dropIfExists('hr_expense_categories');
        Schema::dropIfExists('hr_review_ratings');
        Schema::dropIfExists('hr_reviews');
        Schema::dropIfExists('hr_goals');
        Schema::dropIfExists('hr_kras');
        Schema::dropIfExists('hr_review_cycles');
        Schema::dropIfExists('hr_tax_declarations');
        Schema::dropIfExists('hr_salary_revisions');
        Schema::dropIfExists('hr_payroll_entries');
        Schema::dropIfExists('hr_payroll_runs');
        Schema::dropIfExists('hr_salary_structure_components');
        Schema::dropIfExists('hr_salary_structures');
        Schema::dropIfExists('hr_salary_components');
        Schema::dropIfExists('hr_comp_offs');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_leave_balances');
        Schema::dropIfExists('hr_leave_types');
        Schema::dropIfExists('hr_attendance_logs');
        Schema::dropIfExists('hr_attendance_policies');
        Schema::dropIfExists('hr_shift_assignments');
        Schema::dropIfExists('hr_shifts');
        Schema::dropIfExists('hr_exit_clearances');
        Schema::dropIfExists('hr_exits');
        Schema::dropIfExists('hr_onboarding_tasks');
        Schema::dropIfExists('hr_onboardings');
        if (Schema::hasColumn('employee_profiles', 'hr_department_id')) {
            Schema::table('employee_profiles', function (Blueprint $t) {
                $t->dropConstrainedForeignId('hr_designation_id');
                $t->dropConstrainedForeignId('hr_department_id');
            });
        }
        Schema::dropIfExists('hr_designations');
        Schema::dropIfExists('hr_departments');
    }
};
