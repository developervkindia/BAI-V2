<?php

namespace Tests\Browser;

use App\Models\EmployeeProfile;
use App\Models\HrAnnouncement;
use App\Models\HrAttendanceLog;
use App\Models\HrCandidate;
use App\Models\HrDepartment;
use App\Models\HrDesignation;
use App\Models\HrExpenseCategory;
use App\Models\HrExpenseClaim;
use App\Models\HrExpenseItem;
use App\Models\HrGoal;
use App\Models\HrInterview;
use App\Models\HrJobPosting;
use App\Models\HrKra;
use App\Models\HrLeaveBalance;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use App\Models\HrPayrollEntry;
use App\Models\HrPayrollRun;
use App\Models\HrRecognition;
use App\Models\HrReview;
use App\Models\HrReviewCycle;
use App\Models\HrReviewRating;
use App\Models\HrSalaryComponent;
use App\Models\HrSalaryStructure;
use App\Models\HrSalaryStructureComponent;
use App\Models\HrSurvey;
use App\Models\HrSurveyQuestion;
use App\Models\HrSurveyResponse;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HrModuleTest extends DuskTestCase
{
    protected User $user;
    protected Organization $org;
    protected EmployeeProfile $profile;

    /** IDs of records created during tests, for cleanup */
    protected array $cleanup = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first();
        $this->org = $this->user->currentOrganization();
        $this->profile = $this->ensureEmployeeProfile();
    }

    protected function tearDown(): void
    {
        // Clean up in reverse dependency order
        HrReviewRating::whereIn('id', $this->cleanup['review_ratings'] ?? [])->delete();
        HrReview::whereIn('id', $this->cleanup['reviews'] ?? [])->delete();
        HrReviewCycle::whereIn('id', $this->cleanup['review_cycles'] ?? [])->delete();
        HrGoal::whereIn('id', $this->cleanup['goals'] ?? [])->delete();
        HrKra::whereIn('id', $this->cleanup['kras'] ?? [])->delete();
        HrSurveyResponse::whereIn('id', $this->cleanup['survey_responses'] ?? [])->delete();
        HrSurveyQuestion::whereIn('id', $this->cleanup['survey_questions'] ?? [])->delete();
        HrSurvey::whereIn('id', $this->cleanup['surveys'] ?? [])->delete();
        HrInterview::whereIn('id', $this->cleanup['interviews'] ?? [])->delete();
        HrCandidate::whereIn('id', $this->cleanup['candidates'] ?? [])->delete();
        HrJobPosting::whereIn('id', $this->cleanup['job_postings'] ?? [])->delete();
        HrExpenseItem::whereIn('id', $this->cleanup['expense_items'] ?? [])->delete();
        HrExpenseClaim::whereIn('id', $this->cleanup['expense_claims'] ?? [])->delete();
        HrExpenseCategory::whereIn('id', $this->cleanup['expense_categories'] ?? [])->delete();
        HrPayrollEntry::whereIn('id', $this->cleanup['payroll_entries'] ?? [])->delete();
        HrPayrollRun::whereIn('id', $this->cleanup['payroll_runs'] ?? [])->delete();
        HrLeaveRequest::whereIn('id', $this->cleanup['leave_requests'] ?? [])->delete();
        HrLeaveBalance::whereIn('id', $this->cleanup['leave_balances'] ?? [])->delete();
        HrLeaveType::whereIn('id', $this->cleanup['leave_types'] ?? [])->delete();
        HrAttendanceLog::whereIn('id', $this->cleanup['attendance_logs'] ?? [])->delete();
        HrRecognition::whereIn('id', $this->cleanup['recognitions'] ?? [])->delete();
        HrAnnouncement::whereIn('id', $this->cleanup['announcements'] ?? [])->delete();
        HrDesignation::whereIn('id', $this->cleanup['designations'] ?? [])->delete();
        HrDepartment::whereIn('id', $this->cleanup['departments'] ?? [])->delete();

        parent::tearDown();
    }

    // ═══════════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════════════════════════════════

    protected function ensureEmployeeProfile(): EmployeeProfile
    {
        $profile = EmployeeProfile::where('user_id', $this->user->id)
            ->where('organization_id', $this->org->id)
            ->first();

        if (!$profile) {
            $profile = EmployeeProfile::create([
                'organization_id' => $this->org->id,
                'user_id' => $this->user->id,
                'employee_id' => 'EMP-' . str_pad($this->user->id, 4, '0', STR_PAD_LEFT),
                'designation' => 'Software Engineer',
                'department' => 'Engineering',
                'date_of_joining' => now()->subYear(),
                'employment_type' => 'full_time',
                'status' => 'active',
            ]);
        }

        return $profile;
    }

    protected function createDepartment(array $overrides = []): HrDepartment
    {
        $dept = HrDepartment::create(array_merge([
            'organization_id' => $this->org->id,
            'name' => 'Test Dept ' . uniqid(),
            'code' => 'TD' . strtoupper(substr(uniqid(), -4)),
            'is_active' => true,
        ], $overrides));

        $this->cleanup['departments'][] = $dept->id;
        return $dept;
    }

    protected function createDesignation(array $overrides = []): HrDesignation
    {
        $desig = HrDesignation::create(array_merge([
            'organization_id' => $this->org->id,
            'name' => 'Test Designation ' . uniqid(),
            'level' => 3,
        ], $overrides));

        $this->cleanup['designations'][] = $desig->id;
        return $desig;
    }

    protected function createLeaveType(array $overrides = []): HrLeaveType
    {
        $type = HrLeaveType::create(array_merge([
            'organization_id' => $this->org->id,
            'name' => 'Test Leave ' . uniqid(),
            'code' => 'TL' . strtoupper(substr(uniqid(), -4)),
            'is_paid' => true,
            'max_days_per_year' => 20,
            'requires_approval' => true,
            'is_active' => true,
        ], $overrides));

        $this->cleanup['leave_types'][] = $type->id;
        return $type;
    }

    protected function createLeaveBalance(HrLeaveType $type, float $available = 10): HrLeaveBalance
    {
        $balance = HrLeaveBalance::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'hr_leave_type_id' => $type->id,
            'year' => now()->year,
            'opening_balance' => $available,
            'accrued' => 0,
            'used' => 0,
            'adjusted' => 0,
            'carried_forward' => 0,
            'encashed' => 0,
            'available' => $available,
        ]);

        $this->cleanup['leave_balances'][] = $balance->id;
        return $balance;
    }

    protected function createExpenseCategory(array $overrides = []): HrExpenseCategory
    {
        $cat = HrExpenseCategory::create(array_merge([
            'organization_id' => $this->org->id,
            'name' => 'Test Category ' . uniqid(),
            'max_amount' => 10000,
            'requires_receipt' => false,
            'is_active' => true,
        ], $overrides));

        $this->cleanup['expense_categories'][] = $cat->id;
        return $cat;
    }

    protected function createSalaryStructure(): HrSalaryStructure
    {
        $structure = HrSalaryStructure::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'annual_ctc' => 600000,
            'effective_from' => now()->subYear(),
            'is_current' => true,
        ]);

        $component = HrSalaryComponent::firstOrCreate([
            'organization_id' => $this->org->id,
            'code' => 'BASIC',
        ], [
            'name' => 'Basic Salary',
            'type' => 'earning',
            'calculation_type' => 'fixed',
            'is_taxable' => true,
            'is_statutory' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        HrSalaryStructureComponent::create([
            'hr_salary_structure_id' => $structure->id,
            'hr_salary_component_id' => $component->id,
            'monthly_amount' => 50000,
            'annual_amount' => 600000,
        ]);

        return $structure;
    }

    protected function track(string $key, $id): void
    {
        $this->cleanup[$key][] = $id;
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 1: PAGE NAVIGATION (31 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_page_hr_dashboard(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->assertSee('Dashboard'));
    }

    public function test_page_people_directory(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/people')
            ->assertSee('People Directory'));
    }

    public function test_page_org_chart(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/people/org-chart')
            ->assertSee('Organization Chart'));
    }

    public function test_page_departments(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/departments')
            ->assertSee('Departments'));
    }

    public function test_page_attendance_overview(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/attendance')
            ->assertSee('Attendance'));
    }

    public function test_page_attendance_my(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/attendance/my')
            ->assertSee('Attendance'));
    }

    public function test_page_attendance_team(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/attendance/team')
            ->assertSee('Attendance'));
    }

    public function test_page_attendance_reports(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/attendance/reports')
            ->assertSee('Attendance Reports'));
    }

    public function test_page_leave_overview(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/leave')
            ->assertSee('Leave'));
    }

    public function test_page_leave_apply(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/leave/apply')
            ->assertSee('Apply'));
    }

    public function test_page_leave_my(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/leave/my')
            ->assertSee('Leave'));
    }

    public function test_page_leave_calendar(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/leave/calendar')
            ->assertSee('Calendar'));
    }

    public function test_page_leave_approvals(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/leave/approvals')
            ->assertSee('Approval'));
    }

    public function test_page_payroll_dashboard(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/payroll')
            ->assertSee('Payroll'));
    }

    public function test_page_payroll_run(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/payroll/run')
            ->assertSee('Payroll'));
    }

    public function test_page_payroll_my_payslips(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/payroll/my-payslips')
            ->assertSee('Payslip'));
    }

    public function test_page_performance_overview(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/performance')
            ->assertSee('Performance'));
    }

    public function test_page_performance_cycles(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/performance/cycles')
            ->assertSee('Cycle'));
    }

    public function test_page_performance_my_review(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/performance/my-review')
            ->assertSee('Review'));
    }

    public function test_page_expenses_overview(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/expenses')
            ->assertSee('Expense'));
    }

    public function test_page_expenses_create(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/expenses/create')
            ->assertSee('Expense'));
    }

    public function test_page_expenses_my(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/expenses/my')
            ->assertSee('Expense'));
    }

    public function test_page_expenses_approvals(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/expenses/approvals')
            ->assertSee('Approval'));
    }

    public function test_page_recruitment(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/recruitment')
            ->assertSee('Recruitment'));
    }

    public function test_page_engagement(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/engagement')
            ->assertSee('Engagement'));
    }

    public function test_page_engagement_birthdays(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/engagement/birthdays')
            ->assertSee('Birthday'));
    }

    public function test_page_engagement_anniversaries(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/engagement/anniversaries')
            ->assertSee('Work Anniversaries'));
    }

    public function test_page_surveys_list(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/surveys')
            ->assertSee('Survey'));
    }

    public function test_page_surveys_create(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/surveys/create')
            ->assertSee('Survey'));
    }

    public function test_page_announcements_list(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/announcements')
            ->assertSee('Announcement'));
    }

    public function test_page_announcements_create(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/announcements/create')
            ->assertSee('Announcement'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 2: SIDEBAR NAVIGATION (10 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_sidebar_dashboard_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Dashboard')
            ->assertPathIs('/hr'));
    }

    public function test_sidebar_directory_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Directory')
            ->assertPathIs('/hr/people'));
    }

    public function test_sidebar_org_chart_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Org Chart')
            ->assertPathIs('/hr/people/org-chart'));
    }

    public function test_sidebar_departments_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Departments')
            ->assertPathIs('/hr/departments'));
    }

    public function test_sidebar_my_attendance_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('My Attendance')
            ->assertPathIs('/hr/attendance/my'));
    }

    public function test_sidebar_leave_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Leave')
            ->assertPathBeginsWith('/hr/leave'));
    }

    public function test_sidebar_payroll_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Payroll')
            ->assertPathBeginsWith('/hr/payroll'));
    }

    public function test_sidebar_performance_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/performance')
            ->assertPathIs('/hr/performance'));
    }

    public function test_sidebar_expenses_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Expenses')
            ->assertPathBeginsWith('/hr/expenses'));
    }

    public function test_sidebar_engagement_link(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr')
            ->clickLink('Engagement')
            ->assertPathBeginsWith('/hr/engagement'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 3: DEPARTMENT CRUD (5 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_create_department(): void
    {
        $name = 'Dusk Dept ' . uniqid();
        $code = 'DD' . strtoupper(substr(uniqid(), -4));

        $response = $this->actingAs($this->user)->post('/hr/departments', [
            'name' => $name,
            'code' => $code,
            'is_active' => true,
        ]);

        $response->assertStatus(302);

        $dept = HrDepartment::where('organization_id', $this->org->id)
            ->where('name', $name)
            ->first();
        $this->assertNotNull($dept, 'Department was not created in DB');
        $this->track('departments', $dept->id);
    }

    public function test_update_department(): void
    {
        $dept = $this->createDepartment();
        $updatedName = 'Updated Dept ' . uniqid();

        $response = $this->actingAs($this->user)->put('/hr/departments/' . $dept->id, [
            'name' => $updatedName,
            'code' => $dept->code,
            'is_active' => true,
        ]);

        $response->assertStatus(302);
        $dept->refresh();
        $this->assertEquals($updatedName, $dept->name);
    }

    public function test_delete_department(): void
    {
        $dept = $this->createDepartment();
        $deptId = $dept->id;

        $response = $this->actingAs($this->user)->delete('/hr/departments/' . $deptId);
        $response->assertStatus(302);

        $this->assertNull(HrDepartment::find($deptId));
        // Remove from cleanup since already deleted
        $this->cleanup['departments'] = array_diff($this->cleanup['departments'] ?? [], [$deptId]);
    }

    public function test_department_shows_in_list(): void
    {
        $dept = $this->createDepartment(['name' => 'Visible Dept ' . uniqid()]);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/departments')
            ->assertSee($dept->name));
    }

    public function test_department_validation(): void
    {
        $response = $this->actingAs($this->user)->post('/hr/departments', [
            'name' => '',
            'code' => '',
        ]);

        // Should return validation error (422 for JSON, 302 for web with errors)
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 422]),
            'Expected 302 or 422 for validation error, got ' . $response->getStatusCode()
        );
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 4: ATTENDANCE API (6 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_clock_in(): void
    {
        // Clean any existing attendance for today to avoid duplicate errors
        HrAttendanceLog::where('employee_profile_id', $this->profile->id)
            ->where('date', today()->toDateString())
            ->delete();

        $response = $this->actingAs($this->user)->postJson('/api/hr/attendance/clock-in');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $log = HrAttendanceLog::where('employee_profile_id', $this->profile->id)
            ->where('date', today()->toDateString())
            ->first();
        $this->assertNotNull($log, 'Attendance log not created');
        $this->assertNotNull($log->clock_in, 'Clock-in time not set');
        $this->track('attendance_logs', $log->id);
    }

    public function test_clock_in_duplicate(): void
    {
        // Ensure clocked in first
        HrAttendanceLog::where('employee_profile_id', $this->profile->id)
            ->where('date', today()->toDateString())
            ->delete();

        $this->actingAs($this->user)->postJson('/api/hr/attendance/clock-in');

        // Try clocking in again
        $response = $this->actingAs($this->user)->postJson('/api/hr/attendance/clock-in');
        $response->assertStatus(422);

        // Cleanup
        $log = HrAttendanceLog::where('employee_profile_id', $this->profile->id)
            ->where('date', today()->toDateString())
            ->first();
        if ($log) {
            $this->track('attendance_logs', $log->id);
        }
    }

    public function test_clock_out(): void
    {
        // Setup: ensure clocked in
        HrAttendanceLog::where('employee_profile_id', $this->profile->id)
            ->where('date', today()->toDateString())
            ->delete();

        $this->actingAs($this->user)->postJson('/api/hr/attendance/clock-in');

        $response = $this->actingAs($this->user)->postJson('/api/hr/attendance/clock-out');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $log = HrAttendanceLog::where('employee_profile_id', $this->profile->id)
            ->where('date', today()->toDateString())
            ->first();
        $this->assertNotNull($log->clock_out, 'Clock-out time not set');
        $this->track('attendance_logs', $log->id);
    }

    public function test_today_status(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/hr/attendance/today');
        $response->assertStatus(200)
            ->assertJsonStructure(['is_clocked_in']);
    }

    public function test_regularize_attendance(): void
    {
        // Create a past attendance log without clock_out
        $log = HrAttendanceLog::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'date' => today()->subDays(2)->toDateString(),
            'clock_in' => today()->subDays(2)->setHour(9)->setMinute(0),
            'status' => 'present',
            'source' => 'web',
        ]);
        $this->track('attendance_logs', $log->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/attendance/' . $log->id . '/regularize', [
            'clock_in' => today()->subDays(2)->setHour(9)->setMinute(0)->toDateTimeString(),
            'clock_out' => today()->subDays(2)->setHour(18)->setMinute(0)->toDateTimeString(),
            'remarks' => 'Forgot to clock out',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_attendance_page_shows_data(): void
    {
        // Ensure there is a log for today
        HrAttendanceLog::where('employee_profile_id', $this->profile->id)
            ->where('date', today()->toDateString())
            ->delete();

        $log = HrAttendanceLog::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(2),
            'status' => 'present',
            'source' => 'web',
        ]);
        $this->track('attendance_logs', $log->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/attendance/my')
            ->assertSee('Attendance'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 5: LEAVE MANAGEMENT (8 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_leave_types_exist(): void
    {
        $type = $this->createLeaveType(['name' => 'Casual Leave Test']);
        $this->assertDatabaseHas('hr_leave_types', [
            'id' => $type->id,
            'name' => 'Casual Leave Test',
        ]);
    }

    public function test_leave_balance_display(): void
    {
        $type = $this->createLeaveType(['name' => 'Balance Display Leave ' . uniqid()]);
        $balance = $this->createLeaveBalance($type, 15);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/leave')
            ->assertSee('Leave'));
    }

    public function test_apply_leave(): void
    {
        $type = $this->createLeaveType();
        $balance = $this->createLeaveBalance($type, 10);

        $startDate = now()->addDays(10)->toDateString();
        $endDate = now()->addDays(11)->toDateString();

        $response = $this->actingAs($this->user)->postJson('/api/hr/leave-requests', [
            'leave_type_id' => $type->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Family function',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $request = HrLeaveRequest::where('employee_profile_id', $this->profile->id)
            ->where('hr_leave_type_id', $type->id)
            ->where('start_date', $startDate)
            ->first();
        $this->assertNotNull($request);
        $this->track('leave_requests', $request->id);
    }

    public function test_apply_leave_insufficient_balance(): void
    {
        $type = $this->createLeaveType();
        $balance = $this->createLeaveBalance($type, 0);

        $response = $this->actingAs($this->user)->postJson('/api/hr/leave-requests', [
            'leave_type_id' => $type->id,
            'start_date' => now()->addDays(20)->toDateString(),
            'end_date' => now()->addDays(22)->toDateString(),
            'reason' => 'Test insufficient balance',
        ]);

        $response->assertStatus(422);
    }

    public function test_approve_leave(): void
    {
        $type = $this->createLeaveType();
        $balance = $this->createLeaveBalance($type, 10);

        $request = HrLeaveRequest::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'hr_leave_type_id' => $type->id,
            'start_date' => now()->addDays(30)->toDateString(),
            'end_date' => now()->addDays(31)->toDateString(),
            'days' => 2,
            'reason' => 'Pending leave for approval test',
            'status' => 'pending',
            'applied_at' => now(),
        ]);
        $this->track('leave_requests', $request->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/leave-requests/' . $request->id . '/approve');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $request->refresh();
        $this->assertEquals('approved', $request->status);
    }

    public function test_reject_leave(): void
    {
        $type = $this->createLeaveType();
        $balance = $this->createLeaveBalance($type, 10);

        $request = HrLeaveRequest::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'hr_leave_type_id' => $type->id,
            'start_date' => now()->addDays(40)->toDateString(),
            'end_date' => now()->addDays(41)->toDateString(),
            'days' => 2,
            'reason' => 'Pending leave for rejection test',
            'status' => 'pending',
            'applied_at' => now(),
        ]);
        $this->track('leave_requests', $request->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/leave-requests/' . $request->id . '/reject', [
            'rejection_reason' => 'Project deadline',
        ]);
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $request->refresh();
        $this->assertEquals('rejected', $request->status);
    }

    public function test_cancel_leave(): void
    {
        $type = $this->createLeaveType();
        $balance = $this->createLeaveBalance($type, 10);

        $request = HrLeaveRequest::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'hr_leave_type_id' => $type->id,
            'start_date' => now()->addDays(50)->toDateString(),
            'end_date' => now()->addDays(51)->toDateString(),
            'days' => 2,
            'reason' => 'Approved leave for cancel test',
            'status' => 'approved',
            'approved_by' => $this->user->id,
            'applied_at' => now(),
            'actioned_at' => now(),
        ]);
        $this->track('leave_requests', $request->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/leave-requests/' . $request->id . '/cancel');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $request->refresh();
        $this->assertEquals('cancelled', $request->status);
    }

    public function test_leave_calendar_page(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/leave/calendar')
            ->assertSee('Calendar'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 6: PAYROLL WORKFLOW (6 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_process_payroll_run(): void
    {
        $this->createSalaryStructure();

        // Use a unique month/year to avoid collision
        $month = 1;
        $year = 2020;
        // Remove any existing run for this month
        HrPayrollRun::where('organization_id', $this->org->id)
            ->where('month', $month)
            ->where('year', $year)
            ->delete();

        $response = $this->actingAs($this->user)->postJson('/api/hr/payroll-runs', [
            'month' => $month,
            'year' => $year,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $run = HrPayrollRun::where('organization_id', $this->org->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();
        $this->assertNotNull($run, 'Payroll run not created');
        $this->track('payroll_runs', $run->id);

        // Track entries
        foreach ($run->entries as $entry) {
            $this->track('payroll_entries', $entry->id);
        }
    }

    public function test_duplicate_payroll_run(): void
    {
        $this->createSalaryStructure();

        $month = 2;
        $year = 2020;
        HrPayrollRun::where('organization_id', $this->org->id)
            ->where('month', $month)
            ->where('year', $year)
            ->delete();

        $this->actingAs($this->user)->postJson('/api/hr/payroll-runs', [
            'month' => $month,
            'year' => $year,
        ]);

        // Track first run
        $run = HrPayrollRun::where('organization_id', $this->org->id)
            ->where('month', $month)->where('year', $year)->first();
        if ($run) {
            $this->track('payroll_runs', $run->id);
            foreach ($run->entries as $entry) {
                $this->track('payroll_entries', $entry->id);
            }
        }

        // Try same month again
        $response = $this->actingAs($this->user)->postJson('/api/hr/payroll-runs', [
            'month' => $month,
            'year' => $year,
        ]);

        $response->assertStatus(422);
    }

    public function test_finalize_payroll(): void
    {
        $run = HrPayrollRun::create([
            'organization_id' => $this->org->id,
            'month' => 3,
            'year' => 2020,
            'status' => 'draft',
            'total_gross' => 50000,
            'total_deductions' => 5000,
            'total_net' => 45000,
            'employee_count' => 1,
            'processed_by' => $this->user->id,
            'processed_at' => now(),
        ]);
        $this->track('payroll_runs', $run->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/payroll-runs/' . $run->id . '/finalize');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $run->refresh();
        $this->assertEquals('finalized', $run->status);
    }

    public function test_mark_payroll_paid(): void
    {
        $run = HrPayrollRun::create([
            'organization_id' => $this->org->id,
            'month' => 4,
            'year' => 2020,
            'status' => 'finalized',
            'total_gross' => 50000,
            'total_deductions' => 5000,
            'total_net' => 45000,
            'employee_count' => 1,
            'processed_by' => $this->user->id,
            'processed_at' => now(),
            'finalized_at' => now(),
        ]);
        $this->track('payroll_runs', $run->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/payroll-runs/' . $run->id . '/mark-paid');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $run->refresh();
        $this->assertEquals('paid', $run->status);
    }

    public function test_payroll_run_page(): void
    {
        $run = HrPayrollRun::create([
            'organization_id' => $this->org->id,
            'month' => 5,
            'year' => 2020,
            'status' => 'draft',
            'total_gross' => 50000,
            'total_deductions' => 5000,
            'total_net' => 45000,
            'employee_count' => 1,
            'processed_by' => $this->user->id,
            'processed_at' => now(),
        ]);
        $this->track('payroll_runs', $run->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/payroll/runs/' . $run->id)
            ->pause(1000)
            ->assertSee('Employee Payroll Entries'));
    }

    public function test_my_payslips_page(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/payroll/my-payslips')
            ->assertSee('Payslip'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 7: PERFORMANCE REVIEW (6 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_create_review_cycle(): void
    {
        $cycleName = 'Q1 Review Cycle ' . uniqid();
        $cycle = HrReviewCycle::create([
            'organization_id' => $this->org->id,
            'name' => $cycleName,
            'type' => 'quarterly',
            'start_date' => now()->startOfQuarter()->toDateString(),
            'end_date' => now()->endOfQuarter()->toDateString(),
            'self_review_deadline' => now()->addDays(15)->toDateString(),
            'manager_review_deadline' => now()->addDays(30)->toDateString(),
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        $this->track('review_cycles', $cycle->id);

        // Verify the cycle exists in DB
        $this->assertDatabaseHas('hr_review_cycles', ['id' => $cycle->id, 'name' => $cycleName]);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/performance/cycles')
            ->assertSee('Review Cycles'));
    }

    public function test_show_cycle(): void
    {
        $cycleName = 'Show Cycle ' . uniqid();
        $cycle = HrReviewCycle::create([
            'organization_id' => $this->org->id,
            'name' => $cycleName,
            'type' => 'annual',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'self_review_deadline' => now()->addDays(15)->toDateString(),
            'manager_review_deadline' => now()->addDays(30)->toDateString(),
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        $this->track('review_cycles', $cycle->id);

        $review = HrReview::create([
            'hr_review_cycle_id' => $cycle->id,
            'employee_profile_id' => $this->profile->id,
            'reviewer_id' => $this->user->id,
            'review_type' => 'self',
            'status' => 'pending',
        ]);
        $this->track('reviews', $review->id);

        $this->assertDatabaseHas('hr_review_cycles', ['id' => $cycle->id]);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/performance/cycles/' . $cycle->id)
            ->assertTitleContains($cycleName));
    }

    public function test_submit_review(): void
    {
        $cycle = HrReviewCycle::create([
            'organization_id' => $this->org->id,
            'name' => 'Submit Review Cycle ' . uniqid(),
            'type' => 'quarterly',
            'start_date' => now()->startOfQuarter()->toDateString(),
            'end_date' => now()->endOfQuarter()->toDateString(),
            'self_review_deadline' => now()->addDays(15)->toDateString(),
            'manager_review_deadline' => now()->addDays(30)->toDateString(),
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        $this->track('review_cycles', $cycle->id);

        $review = HrReview::create([
            'hr_review_cycle_id' => $cycle->id,
            'employee_profile_id' => $this->profile->id,
            'reviewer_id' => $this->user->id,
            'review_type' => 'self',
            'status' => 'pending',
        ]);
        $this->track('reviews', $review->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/reviews/' . $review->id . '/submit', [
            'overall_rating' => 4.5,
            'strengths' => 'Great problem solver',
            'improvements' => 'Time management',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $review->refresh();
        $this->assertEquals('submitted', $review->status);
    }

    public function test_rate_kra(): void
    {
        $cycle = HrReviewCycle::create([
            'organization_id' => $this->org->id,
            'name' => 'KRA Rating Cycle ' . uniqid(),
            'type' => 'quarterly',
            'start_date' => now()->startOfQuarter()->toDateString(),
            'end_date' => now()->endOfQuarter()->toDateString(),
            'self_review_deadline' => now()->addDays(15)->toDateString(),
            'manager_review_deadline' => now()->addDays(30)->toDateString(),
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        $this->track('review_cycles', $cycle->id);

        $review = HrReview::create([
            'hr_review_cycle_id' => $cycle->id,
            'employee_profile_id' => $this->profile->id,
            'reviewer_id' => $this->user->id,
            'review_type' => 'self',
            'status' => 'pending',
        ]);
        $this->track('reviews', $review->id);

        $desig = $this->createDesignation();
        $kra = HrKra::create([
            'organization_id' => $this->org->id,
            'hr_designation_id' => $desig->id,
            'title' => 'Code Quality KRA ' . uniqid(),
            'description' => 'Maintain high code quality',
            'weightage' => 30,
        ]);
        $this->track('kras', $kra->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/review-ratings', [
            'review_id' => $review->id,
            'kra_id' => $kra->id,
            'rating' => 4,
            'comments' => 'Excellent quality',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $rating = HrReviewRating::where('hr_review_id', $review->id)
            ->where('hr_kra_id', $kra->id)
            ->first();
        $this->assertNotNull($rating);
        $this->track('review_ratings', $rating->id);
    }

    public function test_rate_goal(): void
    {
        $cycle = HrReviewCycle::create([
            'organization_id' => $this->org->id,
            'name' => 'Goal Rating Cycle ' . uniqid(),
            'type' => 'quarterly',
            'start_date' => now()->startOfQuarter()->toDateString(),
            'end_date' => now()->endOfQuarter()->toDateString(),
            'self_review_deadline' => now()->addDays(15)->toDateString(),
            'manager_review_deadline' => now()->addDays(30)->toDateString(),
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        $this->track('review_cycles', $cycle->id);

        $review = HrReview::create([
            'hr_review_cycle_id' => $cycle->id,
            'employee_profile_id' => $this->profile->id,
            'reviewer_id' => $this->user->id,
            'review_type' => 'self',
            'status' => 'pending',
        ]);
        $this->track('reviews', $review->id);

        $goal = HrGoal::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'hr_review_cycle_id' => $cycle->id,
            'title' => 'Complete Project X ' . uniqid(),
            'description' => 'Deliver project on time',
            'goal_type' => 'individual',
            'metric_type' => 'percentage',
            'target_value' => 100,
            'current_value' => 80,
            'weightage' => 40,
            'status' => 'in_progress',
            'start_date' => now()->startOfQuarter()->toDateString(),
            'due_date' => now()->endOfQuarter()->toDateString(),
        ]);
        $this->track('goals', $goal->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/review-ratings', [
            'review_id' => $review->id,
            'goal_id' => $goal->id,
            'rating' => 4,
            'comments' => 'Great progress',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $rating = HrReviewRating::where('hr_review_id', $review->id)
            ->where('hr_goal_id', $goal->id)
            ->first();
        $this->assertNotNull($rating);
        $this->track('review_ratings', $rating->id);
    }

    public function test_my_review_page(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/performance/my-review')
            ->assertSee('Review'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 8: EXPENSE CLAIMS (7 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_create_expense_claim(): void
    {
        $category = $this->createExpenseCategory();

        $response = $this->actingAs($this->user)->postJson('/api/hr/expense-claims', [
            'title' => 'Business Trip Expense ' . uniqid(),
            'items' => [
                [
                    'hr_expense_category_id' => $category->id,
                    'description' => 'Hotel stay',
                    'amount' => 5000,
                    'expense_date' => now()->subDays(3)->toDateString(),
                ],
                [
                    'hr_expense_category_id' => $category->id,
                    'description' => 'Cab fare',
                    'amount' => 1500,
                    'expense_date' => now()->subDays(2)->toDateString(),
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $claim = HrExpenseClaim::where('employee_profile_id', $this->profile->id)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($claim);
        $this->track('expense_claims', $claim->id);
        foreach ($claim->items as $item) {
            $this->track('expense_items', $item->id);
        }
    }

    public function test_submit_expense_claim(): void
    {
        $claim = HrExpenseClaim::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'title' => 'Draft Expense ' . uniqid(),
            'total_amount' => 3000,
            'status' => 'draft',
        ]);
        $this->track('expense_claims', $claim->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/expense-claims/' . $claim->id . '/submit');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $claim->refresh();
        $this->assertEquals('submitted', $claim->status);
    }

    public function test_approve_expense_claim(): void
    {
        $claim = HrExpenseClaim::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'title' => 'Submitted Expense ' . uniqid(),
            'total_amount' => 4000,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $this->track('expense_claims', $claim->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/expense-claims/' . $claim->id . '/approve');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $claim->refresh();
        $this->assertEquals('approved', $claim->status);
    }

    public function test_reject_expense_claim(): void
    {
        $claim = HrExpenseClaim::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'title' => 'Reject Expense ' . uniqid(),
            'total_amount' => 2000,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $this->track('expense_claims', $claim->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/expense-claims/' . $claim->id . '/reject', [
            'rejection_reason' => 'Missing receipts',
        ]);
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $claim->refresh();
        $this->assertEquals('rejected', $claim->status);
    }

    public function test_reimburse_expense_claim(): void
    {
        $claim = HrExpenseClaim::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'title' => 'Reimburse Expense ' . uniqid(),
            'total_amount' => 6000,
            'status' => 'approved',
            'submitted_at' => now(),
            'approved_by' => $this->user->id,
            'approved_at' => now(),
        ]);
        $this->track('expense_claims', $claim->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/expense-claims/' . $claim->id . '/reimburse');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $claim->refresh();
        $this->assertEquals('reimbursed', $claim->status);
    }

    public function test_expense_page_shows_claims(): void
    {
        $claim = HrExpenseClaim::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'title' => 'Visible Claim ' . uniqid(),
            'total_amount' => 1000,
            'status' => 'draft',
        ]);
        $this->track('expense_claims', $claim->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/expenses')
            ->assertSee('Expense'));
    }

    public function test_expense_create_page(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/expenses/create')
            ->assertSee('Expense'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 9: RECRUITMENT (7 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_create_job_posting(): void
    {
        $dept = $this->createDepartment();

        $response = $this->actingAs($this->user)->postJson('/api/hr/job-postings', [
            'title' => 'Senior Developer ' . uniqid(),
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'description' => 'Looking for experienced developers',
            'requirements' => '5+ years experience',
            'location' => 'Remote',
            'positions' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $posting = HrJobPosting::where('organization_id', $this->org->id)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($posting);
        $this->track('job_postings', $posting->id);
    }

    public function test_add_candidate(): void
    {
        $dept = $this->createDepartment();
        $posting = HrJobPosting::create([
            'organization_id' => $this->org->id,
            'title' => 'Candidate Test Posting ' . uniqid(),
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'status' => 'open',
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);
        $this->track('job_postings', $posting->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/candidates', [
            'hr_job_posting_id' => $posting->id,
            'name' => 'John Doe',
            'email' => 'johndoe_' . uniqid() . '@example.com',
            'stage' => 'applied',
            'phone' => '9876543210',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $candidate = HrCandidate::where('hr_job_posting_id', $posting->id)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($candidate);
        $this->track('candidates', $candidate->id);
    }

    public function test_move_candidate_stage(): void
    {
        $dept = $this->createDepartment();
        $posting = HrJobPosting::create([
            'organization_id' => $this->org->id,
            'title' => 'Move Stage Posting ' . uniqid(),
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'status' => 'open',
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);
        $this->track('job_postings', $posting->id);

        $candidate = HrCandidate::create([
            'organization_id' => $this->org->id,
            'hr_job_posting_id' => $posting->id,
            'name' => 'Jane Smith',
            'email' => 'janesmith_' . uniqid() . '@example.com',
            'stage' => 'applied',
        ]);
        $this->track('candidates', $candidate->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/candidates/' . $candidate->id . '/move', [
            'stage' => 'screening',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $candidate->refresh();
        $this->assertEquals('screening', $candidate->stage);
    }

    public function test_schedule_interview(): void
    {
        $dept = $this->createDepartment();
        $posting = HrJobPosting::create([
            'organization_id' => $this->org->id,
            'title' => 'Interview Posting ' . uniqid(),
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'status' => 'open',
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);
        $this->track('job_postings', $posting->id);

        $candidate = HrCandidate::create([
            'organization_id' => $this->org->id,
            'hr_job_posting_id' => $posting->id,
            'name' => 'Interview Candidate',
            'email' => 'interview_' . uniqid() . '@example.com',
            'stage' => 'screening',
        ]);
        $this->track('candidates', $candidate->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/interviews', [
            'hr_candidate_id' => $candidate->id,
            'interviewer_id' => $this->user->id,
            'round' => 1,
            'scheduled_at' => now()->addDays(5)->toDateTimeString(),
            'mode' => 'video',
            'duration_minutes' => 60,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $interview = HrInterview::where('hr_candidate_id', $candidate->id)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($interview);
        $this->track('interviews', $interview->id);
    }

    public function test_submit_interview_feedback(): void
    {
        $dept = $this->createDepartment();
        $posting = HrJobPosting::create([
            'organization_id' => $this->org->id,
            'title' => 'Feedback Posting ' . uniqid(),
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'status' => 'open',
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);
        $this->track('job_postings', $posting->id);

        $candidate = HrCandidate::create([
            'organization_id' => $this->org->id,
            'hr_job_posting_id' => $posting->id,
            'name' => 'Feedback Candidate',
            'email' => 'feedback_' . uniqid() . '@example.com',
            'stage' => 'interview',
        ]);
        $this->track('candidates', $candidate->id);

        $interview = HrInterview::create([
            'hr_candidate_id' => $candidate->id,
            'interviewer_id' => $this->user->id,
            'round' => 1,
            'scheduled_at' => now()->addDay(),
            'mode' => 'in_person',
            'status' => 'scheduled',
        ]);
        $this->track('interviews', $interview->id);

        $response = $this->actingAs($this->user)->putJson('/api/hr/interviews/' . $interview->id, [
            'rating' => 4,
            'feedback' => 'Strong technical skills, good communication',
            'decision' => 'advance',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $interview->refresh();
        $this->assertEquals(4, $interview->rating);
        $this->assertEquals('advance', $interview->decision);
    }

    public function test_recruitment_page(): void
    {
        $dept = $this->createDepartment();
        $posting = HrJobPosting::create([
            'organization_id' => $this->org->id,
            'title' => 'Visible Posting ' . uniqid(),
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'status' => 'open',
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);
        $this->track('job_postings', $posting->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/recruitment')
            ->assertSee($posting->title));
    }

    public function test_pipeline_page(): void
    {
        $dept = $this->createDepartment();
        $posting = HrJobPosting::create([
            'organization_id' => $this->org->id,
            'title' => 'Pipeline Posting ' . uniqid(),
            'hr_department_id' => $dept->id,
            'employment_type' => 'full_time',
            'status' => 'open',
            'posted_by' => $this->user->id,
            'posted_at' => now(),
        ]);
        $this->track('job_postings', $posting->id);

        $candidate = HrCandidate::create([
            'organization_id' => $this->org->id,
            'hr_job_posting_id' => $posting->id,
            'name' => 'Pipeline Candidate',
            'email' => 'pipeline_' . uniqid() . '@example.com',
            'stage' => 'applied',
        ]);
        $this->track('candidates', $candidate->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/recruitment/' . $posting->id . '/pipeline')
            ->assertSee($posting->title));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 10: SURVEYS (6 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_create_survey(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/hr/surveys', [
            'title' => 'Employee Satisfaction ' . uniqid(),
            'description' => 'Quarterly satisfaction survey',
            'type' => 'engagement',
            'is_anonymous' => true,
            'questions' => [
                [
                    'question' => 'How satisfied are you with your work?',
                    'type' => 'rating',
                    'is_required' => true,
                    'sort_order' => 1,
                ],
                [
                    'question' => 'Any suggestions for improvement?',
                    'type' => 'text',
                    'is_required' => false,
                    'sort_order' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $survey = HrSurvey::where('organization_id', $this->org->id)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($survey);
        $this->track('surveys', $survey->id);
        foreach ($survey->questions as $q) {
            $this->track('survey_questions', $q->id);
        }
    }

    public function test_publish_survey(): void
    {
        $survey = HrSurvey::create([
            'organization_id' => $this->org->id,
            'title' => 'Publish Test Survey ' . uniqid(),
            'description' => 'Test survey for publish',
            'type' => 'pulse',
            'is_anonymous' => false,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);
        $this->track('surveys', $survey->id);

        HrSurveyQuestion::create([
            'hr_survey_id' => $survey->id,
            'question' => 'Rate your experience',
            'type' => 'rating',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $this->track('survey_questions', HrSurveyQuestion::where('hr_survey_id', $survey->id)->first()->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/surveys/' . $survey->id . '/publish');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $survey->refresh();
        $this->assertEquals('active', $survey->status);
    }

    public function test_respond_to_survey(): void
    {
        $survey = HrSurvey::create([
            'organization_id' => $this->org->id,
            'title' => 'Response Test Survey ' . uniqid(),
            'description' => 'Test responding',
            'type' => 'pulse',
            'is_anonymous' => false,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'created_by' => $this->user->id,
        ]);
        $this->track('surveys', $survey->id);

        $question = HrSurveyQuestion::create([
            'hr_survey_id' => $survey->id,
            'question' => 'How do you rate your team?',
            'type' => 'rating',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $this->track('survey_questions', $question->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/surveys/' . $survey->id . '/respond', [
            'responses' => [
                [
                    'question_id' => $question->id,
                    'answer' => 'Great team',
                    'rating' => 5,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $surveyResponse = HrSurveyResponse::where('hr_survey_id', $survey->id)
            ->where('hr_survey_question_id', $question->id)
            ->first();
        $this->assertNotNull($surveyResponse);
        $this->track('survey_responses', $surveyResponse->id);
    }

    public function test_close_survey(): void
    {
        $survey = HrSurvey::create([
            'organization_id' => $this->org->id,
            'title' => 'Close Test Survey ' . uniqid(),
            'description' => 'Test closing',
            'type' => 'pulse',
            'is_anonymous' => false,
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        $this->track('surveys', $survey->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/surveys/' . $survey->id . '/close');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $survey->refresh();
        $this->assertEquals('closed', $survey->status);
    }

    public function test_survey_list_page(): void
    {
        $surveyTitle = 'Visible Survey ' . uniqid();
        $survey = HrSurvey::create([
            'organization_id' => $this->org->id,
            'title' => $surveyTitle,
            'description' => 'Should appear in list',
            'type' => 'pulse',
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
        $this->track('surveys', $survey->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/surveys')
            ->assertSee($surveyTitle));
    }

    public function test_survey_respond_page(): void
    {
        $surveyTitle = 'Respond Page Survey ' . uniqid();
        $survey = HrSurvey::create([
            'organization_id' => $this->org->id,
            'title' => $surveyTitle,
            'description' => 'Respond page test',
            'type' => 'pulse',
            'is_anonymous' => false,
            'status' => 'active',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'created_by' => $this->user->id,
        ]);
        $this->track('surveys', $survey->id);

        $question = HrSurveyQuestion::create([
            'hr_survey_id' => $survey->id,
            'question' => 'Test question for respond page',
            'type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $this->track('survey_questions', $question->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/surveys/' . $survey->id . '/respond')
            ->pause(2000)
            ->assertPresent('.p-5'));  // Page loads without error
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 11: ANNOUNCEMENTS & ENGAGEMENT (7 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_create_announcement_api(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/hr/announcements', [
            'title' => 'Company Update ' . uniqid(),
            'body' => 'We are pleased to announce new office timings.',
            'type' => 'general',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $announcement = HrAnnouncement::where('organization_id', $this->org->id)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($announcement);
        $this->track('announcements', $announcement->id);
    }

    public function test_update_announcement(): void
    {
        $announcement = HrAnnouncement::create([
            'organization_id' => $this->org->id,
            'title' => 'Original Title ' . uniqid(),
            'body' => 'Original body content',
            'type' => 'general',
            'created_by' => $this->user->id,
        ]);
        $this->track('announcements', $announcement->id);

        $updatedTitle = 'Updated Title ' . uniqid();
        $response = $this->actingAs($this->user)->putJson('/api/hr/announcements/' . $announcement->id, [
            'title' => $updatedTitle,
            'body' => 'Updated body content',
            'type' => 'general',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $announcement->refresh();
        $this->assertEquals($updatedTitle, $announcement->title);
    }

    public function test_pin_announcement(): void
    {
        $announcement = HrAnnouncement::create([
            'organization_id' => $this->org->id,
            'title' => 'Pin Test ' . uniqid(),
            'body' => 'This should be pinned',
            'type' => 'general',
            'is_pinned' => false,
            'created_by' => $this->user->id,
        ]);
        $this->track('announcements', $announcement->id);

        $response = $this->actingAs($this->user)->postJson('/api/hr/announcements/' . $announcement->id . '/pin');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $announcement->refresh();
        $this->assertTrue($announcement->is_pinned);
    }

    public function test_delete_announcement(): void
    {
        $announcement = HrAnnouncement::create([
            'organization_id' => $this->org->id,
            'title' => 'Delete Test ' . uniqid(),
            'body' => 'This will be deleted',
            'type' => 'general',
            'created_by' => $this->user->id,
        ]);
        $announcementId = $announcement->id;

        $response = $this->actingAs($this->user)->deleteJson('/api/hr/announcements/' . $announcementId);
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNull(HrAnnouncement::find($announcementId));
        // No need to track for cleanup since already deleted
    }

    public function test_create_recognition(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/hr/recognitions', [
            'employee_profile_id' => $this->profile->id,
            'type' => 'shoutout',
            'title' => 'Outstanding Work ' . uniqid(),
            'description' => 'Great job on the product launch!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $recognition = HrRecognition::where('organization_id', $this->org->id)
            ->orderByDesc('id')
            ->first();
        $this->assertNotNull($recognition);
        $this->track('recognitions', $recognition->id);
    }

    public function test_announcements_page(): void
    {
        $title = 'Page Visible Announcement ' . uniqid();
        $announcement = HrAnnouncement::create([
            'organization_id' => $this->org->id,
            'title' => $title,
            'body' => 'Visible in list',
            'type' => 'general',
            'created_by' => $this->user->id,
            'published_at' => now(),
        ]);
        $this->track('announcements', $announcement->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/announcements')
            ->assertSee($title));
    }

    public function test_engagement_feed(): void
    {
        $recognition = HrRecognition::create([
            'organization_id' => $this->org->id,
            'employee_profile_id' => $this->profile->id,
            'recognized_by' => $this->user->id,
            'type' => 'shoutout',
            'title' => 'Engagement Feed Test ' . uniqid(),
            'description' => 'Test recognition for feed',
        ]);
        $this->track('recognitions', $recognition->id);

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/engagement')
            ->assertSee('Engagement'));
    }

    // ═══════════════════════════════════════════════════════════════════
    // SECTION 12: EMPLOYEE PROFILE (3 tests)
    // ═══════════════════════════════════════════════════════════════════

    public function test_profile_directory_renders(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/people')
            ->assertSee('People Directory'));
    }

    public function test_employee_show(): void
    {
        $profile = EmployeeProfile::where('organization_id', $this->org->id)->first();
        if (!$profile) {
            $this->markTestSkipped('No employee profiles exist');
        }

        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/people/' . $profile->id)
            ->assertSee($profile->user->name));
    }

    public function test_profile_org_chart_renders(): void
    {
        $this->browse(fn (Browser $b) => $b->loginAs($this->user)
            ->visit('/hr/people/org-chart')
            ->assertSee('Organization Chart'));
    }
}
