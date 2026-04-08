<?php

use App\Http\Controllers\Api\OppFormController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardStarController;
use App\Http\Controllers\BoardViewController;
use App\Http\Controllers\ClientPortal\ClientPortalAuthController;
use App\Http\Controllers\ClientPortal\ClientPortalHomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\Hr\HrAnnouncementController;
use App\Http\Controllers\Hr\HrAttendanceController;
use App\Http\Controllers\Hr\HrDashboardController;
use App\Http\Controllers\Hr\HrDepartmentController;
use App\Http\Controllers\Hr\HrEngagementController;
use App\Http\Controllers\Hr\HrExpenseController;
use App\Http\Controllers\Hr\HrLeaveController;
use App\Http\Controllers\Hr\HrPayrollController;
use App\Http\Controllers\Hr\HrPeopleController;
use App\Http\Controllers\Hr\HrPerformanceController;
use App\Http\Controllers\Hr\HrRecruitmentController;
use App\Http\Controllers\Hr\HrSurveyController;
use App\Http\Controllers\HubController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Knowledge\KnowledgeArticleController;
use App\Http\Controllers\Knowledge\KnowledgeCategoryController;
use App\Http\Controllers\Knowledge\KnowledgeHomeController;
use App\Http\Controllers\Knowledge\KnowledgeRevisionController;
use App\Http\Controllers\Knowledge\KnowledgeSearchController;
use App\Http\Controllers\Knowledge\KnowledgeUploadController;
use App\Http\Controllers\MemberDirectoryController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Opportunity\OppDashboardController;
use App\Http\Controllers\Opportunity\OppGoalController;
use App\Http\Controllers\Opportunity\OppPortfolioController;
use App\Http\Controllers\Opportunity\OppProjectController;
use App\Http\Controllers\Opportunity\OppReportController;
use App\Http\Controllers\Opportunity\OppTemplateController;
use App\Http\Controllers\Org\OrgClientController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\PlatformInviteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminOrganizationController;
use App\Http\Controllers\SuperAdmin\SuperAdminProductController;
use App\Http\Controllers\SuperAdmin\SuperAdminSubscriptionController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->is_super_admin) {
            return redirect()->route('super-admin.dashboard');
        }
        return redirect()->route('hub');
    }

    return view('landing');
})->name('home');

// Public pricing page
Route::get('/pricing', [SubscriptionController::class, 'pricing'])->name('pricing');

// Board invitation (accessible by anyone)
Route::get('/invite/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
Route::get('/invite/{token}/decline', [InvitationController::class, 'decline'])->name('invitation.decline');

// Platform invitation (accessible by anyone)
Route::get('/platform-invite/{token}', [PlatformInviteController::class, 'accept'])->name('platform-invitation.accept');

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

    // OAuth
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

// Onboarding routes — auth required but NO org context check (to avoid redirect loops)
Route::middleware('auth')->group(function () {
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations', [OrganizationController::class, 'store'])->name('organizations.store');
});

// Stop impersonating (must be outside super-admin middleware since the impersonated user is not a super admin)
Route::middleware('auth')->post('/super-admin/stop-impersonating', [SuperAdminUserController::class, 'stopImpersonating'])->name('super-admin.stop-impersonating');

// Super Admin routes (no org context required)
Route::middleware(['auth', 'super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit-log', [SuperAdminDashboardController::class, 'auditLog'])->name('audit-log');
    Route::get('/organizations', [SuperAdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/{organization}', [SuperAdminOrganizationController::class, 'show'])->name('organizations.show');
    Route::post('/organizations/{organization}/activate', [SuperAdminOrganizationController::class, 'activate'])->name('organizations.activate');
    Route::post('/organizations/{organization}/deactivate', [SuperAdminOrganizationController::class, 'deactivate'])->name('organizations.deactivate');
    Route::get('/users', [SuperAdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [SuperAdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/impersonate', [SuperAdminUserController::class, 'impersonate'])->name('impersonate');
    Route::post('/invite', [SuperAdminUserController::class, 'invite'])->name('invite');
    Route::get('/subscriptions', [SuperAdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/organizations/{organization}/subscriptions', [SuperAdminSubscriptionController::class, 'store'])->name('organizations.add-subscription');
    Route::put('/subscriptions/{subscription}', [SuperAdminSubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [SuperAdminSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::get('/products', [SuperAdminProductController::class, 'index'])->name('products.index');
    Route::put('/products/{product}', [SuperAdminProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/toggle', [SuperAdminProductController::class, 'toggleAvailability'])->name('products.toggle-availability');
});

// Public form submission (no auth required)
Route::get('/forms/{slug}', [OppFormController::class, 'showPublic'])->name('opp.forms.public');
Route::post('/forms/{slug}', [OppFormController::class, 'submit'])->name('opp.forms.submit');

// BAI Docs public form submission (no auth required)
Route::get('/doc-forms/{slug}', [\App\Http\Controllers\Docs\DocsFormController::class, 'showPublic'])->name('docs.forms.public');
Route::post('/doc-forms/{slug}', [\App\Http\Controllers\Docs\DocsFormController::class, 'submitPublic'])->name('docs.forms.submit');

// Organization invitation accept (public, before auth)
Route::get('/org-invite/{token}', [OrganizationInvitationController::class, 'accept'])->name('org-invitation.accept');

// Client portal (external contacts — separate guard)
Route::prefix('client-portal')->name('client-portal.')->group(function () {
    Route::get('/login', [ClientPortalAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [ClientPortalAuthController::class, 'login'])->name('login.submit');
    Route::middleware('auth:client_portal')->group(function () {
        Route::get('/', [ClientPortalHomeController::class, 'dashboard'])->name('home');
        Route::post('/logout', [ClientPortalAuthController::class, 'logout'])->name('logout');
        Route::get('/documents/{document}/download', [ClientPortalHomeController::class, 'downloadDocument'])->name('documents.download');
    });
});

// Onboarding (auth required, no org context needed yet)
Route::middleware('auth')->group(function () {
    Route::get('/onboarding/plans', [OnboardingController::class, 'plans'])->name('onboarding.plans');
    Route::post('/onboarding/plans', [OnboardingController::class, 'selectPlans'])->name('onboarding.select-plans');
});

// Main authenticated routes — requires org context
Route::middleware(['auth', 'org.context'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Hub (ecosystem home)
    Route::get('/hub', [HubController::class, 'index'])->name('hub');

    // Organizations
    Route::get('/org/{organization}/manage', [OrganizationController::class, 'manage'])->name('organizations.manage');
    Route::get('/org/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::put('/org/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::post('/org/{organization}/switch', [OrganizationController::class, 'switchOrganization'])->name('organizations.switch');

    // Organization-scoped clients (BAI Projects subscription + CRM permissions)
    Route::middleware(['org.route', 'product.access:projects', 'permission:org.clients.view'])->group(function () {
        Route::get('/org/{organization}/clients', [OrgClientController::class, 'index'])->name('org.clients.index');
        Route::get('/org/{organization}/clients/{client}', [OrgClientController::class, 'show'])->name('org.clients.show');
        Route::get('/org/{organization}/clients/{client}/documents/{document}/download', [OrgClientController::class, 'downloadDocument'])->name('org.clients.documents.download');
    });

    Route::middleware(['org.route', 'product.access:projects', 'permission:org.clients.manage'])->group(function () {
        Route::post('/org/{organization}/clients', [OrgClientController::class, 'store'])->name('org.clients.store');
        Route::put('/org/{organization}/clients/{client}', [OrgClientController::class, 'update'])->name('org.clients.update');
        Route::delete('/org/{organization}/clients/{client}', [OrgClientController::class, 'destroy'])->name('org.clients.destroy');
        Route::post('/org/{organization}/clients/{client}/approve', [OrgClientController::class, 'approveRequirements'])->name('org.clients.approve');
        Route::post('/org/{organization}/clients/{client}/lost', [OrgClientController::class, 'markLost'])->name('org.clients.lost');
        Route::post('/org/{organization}/clients/{client}/hire', [OrgClientController::class, 'createDeliveryProject'])->name('org.clients.hire');
        Route::post('/org/{organization}/clients/{client}/portal-users', [OrgClientController::class, 'invitePortalUser'])->name('org.clients.portal.invite');
        Route::delete('/org/{organization}/clients/{client}/portal-users/{portalUser}', [OrgClientController::class, 'revokePortalUser'])->name('org.clients.portal.revoke');
        Route::post('/org/{organization}/clients/{client}/documents', [OrgClientController::class, 'storeDocument'])->name('org.clients.documents.store');
        Route::delete('/org/{organization}/clients/{client}/documents/{document}', [OrgClientController::class, 'destroyDocument'])->name('org.clients.documents.destroy');
    });

    // Subscription Management
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/change-plan', [SubscriptionController::class, 'changePlan'])->name('subscriptions.change-plan');
    Route::post('/subscriptions/start-trial', [SubscriptionController::class, 'startTrial'])->name('subscriptions.start-trial');
    Route::get('/subscriptions/usage', [SubscriptionController::class, 'usage'])->name('subscriptions.usage');

    // ── BAI Board (kanban product) ──────────────────────────────────
    Route::middleware('product.access:board')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Workspaces
        Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
        Route::get('/w/{workspace}', [WorkspaceController::class, 'show'])->name('workspaces.show');
        Route::put('/w/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');
        Route::delete('/w/{workspace}', [WorkspaceController::class, 'destroy'])->name('workspaces.destroy');

        // Workspace members
        Route::post('/w/{workspace}/members', [WorkspaceMemberController::class, 'store'])->name('workspace-members.store');
        Route::put('/w/{workspace}/members/{member}', [WorkspaceMemberController::class, 'update'])->name('workspace-members.update');
        Route::delete('/w/{workspace}/members/{member}', [WorkspaceMemberController::class, 'destroy'])->name('workspace-members.destroy');

        // Boards
        Route::post('/w/{workspace}/boards', [BoardController::class, 'store'])->name('boards.store');
        Route::get('/b/{board}', [BoardController::class, 'show'])->name('boards.show');
        Route::put('/b/{board}', [BoardController::class, 'update'])->name('boards.update');
        Route::delete('/b/{board}', [BoardController::class, 'destroy'])->name('boards.destroy');
        Route::post('/b/{board}/archive', [BoardController::class, 'archive'])->name('boards.archive');
        Route::post('/b/{board}/restore', [BoardController::class, 'restore'])->name('boards.restore');
        Route::post('/b/{board}/star', [BoardStarController::class, 'toggle'])->name('boards.star');

        // Search
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        // Board Views
        Route::get('/b/{board}/calendar', [BoardViewController::class, 'calendar'])->name('boards.calendar');
        Route::get('/b/{board}/table', [BoardViewController::class, 'table'])->name('boards.table');
        Route::get('/b/{board}/timeline', [BoardViewController::class, 'timeline'])->name('boards.timeline')->middleware('plan.feature:board,timeline_view');
        Route::get('/b/{board}/dashboard', [BoardViewController::class, 'boardDashboard'])->name('boards.dashboard')->middleware('plan.feature:board,dashboard_view');

        // Member Directory
        Route::get('/w/{workspace}/members', [MemberDirectoryController::class, 'index'])->name('workspace-members.index');
    });

    // ── BAI Projects ─────────────────────────────────────────────────
    Route::middleware('product.access:projects')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::get('/projects/{project}/board', [ProjectController::class, 'board'])->name('projects.board');
        Route::get('/projects/{project}/milestones', [ProjectController::class, 'milestones'])->name('projects.milestones');
        Route::get('/projects/{project}/overview', [ProjectController::class, 'overview'])->name('projects.overview');
        Route::get('/projects/{project}/timeline', [ProjectController::class, 'timeline'])->name('projects.timeline');
        Route::get('/projects/{project}/calendar', [ProjectController::class, 'calendar'])->name('projects.calendar');
        Route::get('/projects/{project}/backlog', [ProjectController::class, 'backlog'])->name('projects.backlog');
        Route::get('/projects/{project}/updates', [ProjectController::class, 'updates'])->name('projects.updates');
        Route::get('/projects/{project}/scope', [ProjectController::class, 'scope'])->name('projects.scope');
        Route::get('/projects/{project}/reports', [ProjectController::class, 'reports'])->name('projects.reports');
        Route::get('/projects/{project}/chat', [ProjectController::class, 'chat'])->name('projects.chat');
        Route::get('/projects/{project}/documents', [ProjectController::class, 'documents'])->name('projects.documents');
        Route::get('/projects/{project}/recycle-bin', [ProjectController::class, 'recycleBin'])->name('projects.recycle-bin');
        Route::get('/projects/{project}/resources', [ProjectController::class, 'resources'])->name('projects.resources');

        // Pro-only project features
        Route::get('/projects/{project}/billing', [ProjectController::class, 'billing'])->name('projects.billing')->middleware('plan.feature:projects,billing');
        Route::get('/projects/{project}/billing/{week}/invoice', [ProjectController::class, 'billingInvoice'])->name('projects.billing.invoice')->middleware('plan.feature:projects,billing');
        Route::get('/projects/{project}/timesheets', [ProjectController::class, 'timesheets'])->name('projects.timesheets')->middleware('plan.feature:projects,timesheets');
        Route::get('/projects/{project}/budget', [ProjectController::class, 'budget'])->name('projects.budget')->middleware('plan.feature:projects,budget');
        Route::get('/projects/{project}/workload', [ProjectController::class, 'workload'])->name('projects.workload')->middleware('plan.feature:projects,workload');

        // Clients (redirect to org-scoped CRM)
        Route::get('/clients', function () {
            $org = auth()->user()->currentOrganization();
            abort_unless($org, 404);

            return redirect()->route('org.clients.index', $org);
        })->name('clients.index');
        Route::get('/clients/{client}', function (Client $client) {
            $org = auth()->user()->currentOrganization();
            abort_unless($org && $client->organization_id === $org->id, 403);

            return redirect()->route('org.clients.show', [$org, $client]);
        })->name('clients.show');
    });

    // ── Opportunity (Asana-clone product) ──────────────────────────
    Route::middleware('product.access:opportunity')->prefix('opportunity')->name('opportunity.')->group(function () {
        Route::get('/', [OppDashboardController::class, 'home'])->name('home');
        Route::get('/my-tasks', [OppDashboardController::class, 'myTasks'])->name('my-tasks');
        Route::get('/inbox', [OppDashboardController::class, 'inbox'])->name('inbox');
        Route::get('/projects', [OppProjectController::class, 'index'])->name('projects.index');
        Route::post('/projects', [OppProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [OppProjectController::class, 'show'])->name('projects.show');
        Route::put('/projects/{project}', [OppProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [OppProjectController::class, 'destroy'])->name('projects.destroy');
        Route::get('/projects/{project}/overview', [OppProjectController::class, 'overview'])->name('projects.overview');
        Route::get('/projects/{project}/board', [OppProjectController::class, 'board'])->name('projects.board');
        Route::get('/projects/{project}/timeline', [OppProjectController::class, 'timeline'])->name('projects.timeline');
        Route::get('/projects/{project}/calendar', [OppProjectController::class, 'calendar'])->name('projects.calendar');

        // Pro-only: Goals
        Route::middleware('plan.feature:opportunity,goals')->group(function () {
            Route::get('/goals', [OppGoalController::class, 'index'])->name('goals.index');
            Route::post('/goals', [OppGoalController::class, 'store'])->name('goals.store');
            Route::get('/goals/{goal}', [OppGoalController::class, 'show'])->name('goals.show');
            Route::put('/goals/{goal}', [OppGoalController::class, 'update'])->name('goals.update');
            Route::delete('/goals/{goal}', [OppGoalController::class, 'destroy'])->name('goals.destroy');
        });

        // Pro-only: Portfolios
        Route::middleware('plan.feature:opportunity,portfolios')->group(function () {
            Route::get('/portfolios', [OppPortfolioController::class, 'index'])->name('portfolios.index');
            Route::post('/portfolios', [OppPortfolioController::class, 'store'])->name('portfolios.store');
            Route::get('/portfolios/{portfolio}', [OppPortfolioController::class, 'show'])->name('portfolios.show');
            Route::delete('/portfolios/{portfolio}', [OppPortfolioController::class, 'destroy'])->name('portfolios.destroy');
        });

        // Pro-only: Reports
        Route::get('/reporting', [OppReportController::class, 'index'])->name('reporting.index')->middleware('plan.feature:opportunity,reporting');

        // Pro-only: Templates
        Route::middleware('plan.feature:opportunity,templates')->group(function () {
            Route::get('/templates', [OppTemplateController::class, 'index'])->name('templates.index');
            Route::post('/projects/{project}/save-as-template', [OppTemplateController::class, 'saveAsTemplate'])->name('projects.save-template');
            Route::post('/templates/{project}/create', [OppTemplateController::class, 'createFromTemplate'])->name('templates.create');
        });
    });

    // ── BAI HR (HRMS product) ───────────────────────────────────────
    Route::middleware('product.access:hr')->prefix('hr')->name('hr.')->group(function () {
        Route::get('/', [HrDashboardController::class, 'index'])->name('dashboard');
        Route::get('/people', [HrPeopleController::class, 'index'])->name('people.index');
        Route::get('/people/org-chart', [HrPeopleController::class, 'orgChart'])->name('people.org-chart');
        Route::get('/people/{employeeProfile}', [HrPeopleController::class, 'show'])->name('people.show');
        Route::get('/departments', [HrDepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [HrDepartmentController::class, 'store'])->name('departments.store');
        Route::put('/departments/{department}', [HrDepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [HrDepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::get('/attendance', [HrAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/my', [HrAttendanceController::class, 'my'])->name('attendance.my');
        Route::get('/attendance/team', [HrAttendanceController::class, 'team'])->name('attendance.team');
        Route::get('/attendance/reports', [HrAttendanceController::class, 'reports'])->name('attendance.reports');
        Route::get('/leave', [HrLeaveController::class, 'index'])->name('leave.index');
        Route::get('/leave/apply', [HrLeaveController::class, 'apply'])->name('leave.apply');
        Route::get('/leave/my', [HrLeaveController::class, 'my'])->name('leave.my');
        Route::get('/leave/calendar', [HrLeaveController::class, 'calendar'])->name('leave.calendar');
        Route::get('/leave/approvals', [HrLeaveController::class, 'approvals'])->name('leave.approvals');
        Route::get('/payroll', [HrPayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/run', [HrPayrollController::class, 'run'])->name('payroll.run');
        Route::get('/payroll/runs/{payrollRun}', [HrPayrollController::class, 'showRun'])->name('payroll.show-run');
        Route::get('/payroll/payslip/{payrollEntry}', [HrPayrollController::class, 'payslip'])->name('payroll.payslip');
        Route::get('/payroll/my-payslips', [HrPayrollController::class, 'myPayslips'])->name('payroll.my-payslips');
        Route::get('/payroll/salary-components', [HrPayrollController::class, 'salaryComponents'])->name('payroll.salary-components');
        Route::get('/payroll/salary-structures', [HrPayrollController::class, 'salaryStructures'])->name('payroll.salary-structures');
        Route::get('/payroll/salary-structures/{employeeProfile}', [HrPayrollController::class, 'editSalaryStructure'])->name('payroll.edit-salary-structure');
        Route::get('/performance', [HrPerformanceController::class, 'index'])->name('performance.index');
        Route::get('/performance/cycles', [HrPerformanceController::class, 'cycles'])->name('performance.cycles');
        Route::get('/performance/cycles/{reviewCycle}', [HrPerformanceController::class, 'showCycle'])->name('performance.show-cycle');
        Route::get('/performance/my-review', [HrPerformanceController::class, 'myReview'])->name('performance.my-review');
        Route::get('/expenses', [HrExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [HrExpenseController::class, 'create'])->name('expenses.create');
        Route::get('/expenses/my', [HrExpenseController::class, 'my'])->name('expenses.my');
        Route::get('/expenses/approvals', [HrExpenseController::class, 'approvals'])->name('expenses.approvals');
        Route::get('/expenses/{expenseClaim}', [HrExpenseController::class, 'show'])->name('expenses.show');
        Route::get('/recruitment', [HrRecruitmentController::class, 'index'])->name('recruitment.index');
        Route::get('/recruitment/{jobPosting}', [HrRecruitmentController::class, 'showPosting'])->name('recruitment.show-posting');
        Route::get('/recruitment/{jobPosting}/pipeline', [HrRecruitmentController::class, 'pipeline'])->name('recruitment.pipeline');
        Route::get('/engagement', [HrEngagementController::class, 'index'])->name('engagement.index');
        Route::get('/engagement/birthdays', [HrEngagementController::class, 'birthdays'])->name('engagement.birthdays');
        Route::get('/engagement/anniversaries', [HrEngagementController::class, 'anniversaries'])->name('engagement.anniversaries');
        Route::get('/surveys', [HrSurveyController::class, 'index'])->name('surveys.index');
        Route::get('/surveys/create', [HrSurveyController::class, 'create'])->name('surveys.create');
        Route::get('/surveys/{survey}', [HrSurveyController::class, 'show'])->name('surveys.show');
        Route::get('/surveys/{survey}/respond', [HrSurveyController::class, 'respond'])->name('surveys.respond');
        Route::get('/announcements', [HrAnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/create', [HrAnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/announcements', [HrAnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('/announcements/{announcement}', [HrAnnouncementController::class, 'show'])->name('announcements.show');
    });

    // ── Knowledge Base ───────────────────────────────────────────
    Route::middleware(['product.access:knowledge_base', 'permission:knowledge.view'])->prefix('knowledge')->name('knowledge.')->group(function () {
        Route::get('/', [KnowledgeHomeController::class, 'index'])->name('index');
        Route::get('/search', [KnowledgeSearchController::class, 'index'])->middleware('plan.feature:knowledge_base,fulltext_search')->name('search');

        // Static category paths must be registered before `{knowledge_category}` or "create" is treated as a slug.
        Route::middleware('permission:knowledge.moderate')->group(function () {
            Route::get('/categories', [KnowledgeCategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/create', [KnowledgeCategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [KnowledgeCategoryController::class, 'store'])->name('categories.store');
        });

        Route::get('/categories/{knowledge_category}', [KnowledgeCategoryController::class, 'show'])->name('categories.show');

        Route::get('/articles/create', [KnowledgeArticleController::class, 'create'])->name('articles.create');
        Route::post('/articles', [KnowledgeArticleController::class, 'store'])->name('articles.store');
        Route::get('/articles/{knowledge_article}/revisions', [KnowledgeRevisionController::class, 'index'])->name('articles.revisions.index');
        Route::post('/articles/{knowledge_article}/revisions/{revision}/restore', [KnowledgeRevisionController::class, 'restore'])->name('articles.revisions.restore');
        Route::get('/articles/{knowledge_article}/edit', [KnowledgeArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/articles/{knowledge_article}', [KnowledgeArticleController::class, 'update'])->name('articles.update');
        Route::delete('/articles/{knowledge_article}', [KnowledgeArticleController::class, 'destroy'])->name('articles.destroy');
        Route::get('/articles/{knowledge_article}', [KnowledgeArticleController::class, 'show'])->name('articles.show');

        Route::post('/upload/image', [KnowledgeUploadController::class, 'uploadImage'])->middleware('plan.feature:knowledge_base,attachments')->name('upload.image');
        Route::post('/upload/attachment', [KnowledgeUploadController::class, 'uploadAttachment'])->middleware('plan.feature:knowledge_base,attachments')->name('upload.attachment');
        Route::get('/files/{attachment}', [KnowledgeUploadController::class, 'show'])->name('files.show');
        Route::get('/files/{attachment}/download', [KnowledgeUploadController::class, 'download'])->name('files.download');

        Route::middleware('permission:knowledge.moderate')->group(function () {
            Route::get('/categories/{knowledge_category}/edit', [KnowledgeCategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{knowledge_category}', [KnowledgeCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{knowledge_category}', [KnowledgeCategoryController::class, 'destroy'])->name('categories.destroy');
            Route::get('/trash', [KnowledgeArticleController::class, 'trash'])->name('trash');
            Route::post('/trash/{trashedArticle}/restore', [KnowledgeArticleController::class, 'restore'])->name('articles.restore');
            Route::delete('/trash/{trashedArticle}', [KnowledgeArticleController::class, 'forceDestroy'])->name('articles.forceDestroy');
        });
    });

    // ── BAI Docs (Documents, Sheets, Forms, Slides) ─────────────
    Route::middleware(['product.access:docs', 'permission:docs.view'])->prefix('docs')->name('docs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Docs\DocsHomeController::class, 'index'])->name('index');
        Route::get('/starred', [\App\Http\Controllers\Docs\DocsHomeController::class, 'starred'])->name('starred');
        Route::get('/shared', [\App\Http\Controllers\Docs\DocsHomeController::class, 'sharedWithMe'])->name('shared');
        Route::get('/trash', [\App\Http\Controllers\Docs\DocsHomeController::class, 'trash'])->name('trash');

        // Folders
        Route::get('/folder/{folder}', [\App\Http\Controllers\Docs\DocsFolderController::class, 'show'])->name('folders.show');

        // Documents (word processor)
        Route::get('/document/new', [\App\Http\Controllers\Docs\DocsDocumentController::class, 'create'])->name('documents.create');
        Route::get('/d/{document}', [\App\Http\Controllers\Docs\DocsDocumentController::class, 'show'])->name('documents.show');

        // Spreadsheets
        Route::get('/spreadsheet/new', [\App\Http\Controllers\Docs\DocsSpreadsheetController::class, 'create'])->name('spreadsheets.create');
        Route::get('/s/{document}', [\App\Http\Controllers\Docs\DocsSpreadsheetController::class, 'show'])->name('spreadsheets.show');

        // Forms
        Route::get('/form/new', [\App\Http\Controllers\Docs\DocsFormController::class, 'create'])->name('forms.create');
        Route::get('/f/{document}', [\App\Http\Controllers\Docs\DocsFormController::class, 'show'])->name('forms.show');
        Route::get('/f/{document}/responses', [\App\Http\Controllers\Docs\DocsFormController::class, 'responses'])->name('forms.responses');

        // Presentations
        Route::get('/presentation/new', [\App\Http\Controllers\Docs\DocsPresentationController::class, 'create'])->name('presentations.create');
        Route::get('/p/{document}', [\App\Http\Controllers\Docs\DocsPresentationController::class, 'show'])->name('presentations.show');
        Route::get('/p/{document}/present', [\App\Http\Controllers\Docs\DocsPresentationController::class, 'present'])->name('presentations.present');

        // Uploads
        Route::post('/upload/image', [\App\Http\Controllers\Docs\DocsUploadController::class, 'uploadImage'])->name('upload.image');
        Route::get('/files/{attachment}', [\App\Http\Controllers\Docs\DocsUploadController::class, 'show'])->name('files.show');
        Route::get('/files/{attachment}/download', [\App\Http\Controllers\Docs\DocsUploadController::class, 'download'])->name('files.download');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/full', [EmployeeProfileController::class, 'show'])->name('profile.full');
    Route::put('/profile/basic', [EmployeeProfileController::class, 'update'])->name('profile.basic.update');

    // Roles & Permissions Management
    Route::get('/org/{organization}/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/org/{organization}/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/org/{organization}/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/org/{organization}/roles/{role}', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/org/{organization}/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/org/{organization}/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // User Management
    Route::get('/org/{organization}/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/org/{organization}/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::get('/org/{organization}/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/org/{organization}/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::put('/org/{organization}/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::post('/org/{organization}/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/org/{organization}/users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');

    // Organization Invitations
    Route::post('/org/{organization}/invitations', [OrganizationInvitationController::class, 'store'])->name('org-invitations.store');
    Route::post('/org/{organization}/invitations/{invitation}/resend', [OrganizationInvitationController::class, 'resend'])->name('org-invitations.resend');
    Route::delete('/org/{organization}/invitations/{invitation}', [OrganizationInvitationController::class, 'cancel'])->name('org-invitations.cancel');
});
