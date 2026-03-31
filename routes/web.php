<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardStarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HubController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\OrganizationInvitationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BoardViewController;
use App\Http\Controllers\MemberDirectoryController;
use App\Http\Controllers\Opportunity\OppDashboardController;
use App\Http\Controllers\Opportunity\OppGoalController;
use App\Http\Controllers\Opportunity\OppPortfolioController;
use App\Http\Controllers\Opportunity\OppProjectController;
use App\Http\Controllers\Opportunity\OppReportController;
use App\Http\Controllers\Opportunity\OppTemplateController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminOrganizationController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\SuperAdminSubscriptionController;
use App\Http\Controllers\SuperAdmin\SuperAdminProductController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('hub');
    }
    return view('landing');
})->name('home');

// Board invitation (accessible by anyone)
Route::get('/invite/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
Route::get('/invite/{token}/decline', [InvitationController::class, 'decline'])->name('invitation.decline');

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

// Super Admin routes (no org context required)
Route::middleware(['auth', 'super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/',                                              [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/audit-log',                                     [SuperAdminDashboardController::class, 'auditLog'])->name('audit-log');
    Route::get('/organizations',                                 [SuperAdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/{organization}',                  [SuperAdminOrganizationController::class, 'show'])->name('organizations.show');
    Route::post('/organizations/{organization}/activate',        [SuperAdminOrganizationController::class, 'activate'])->name('organizations.activate');
    Route::post('/organizations/{organization}/deactivate',      [SuperAdminOrganizationController::class, 'deactivate'])->name('organizations.deactivate');
    Route::get('/users',                                         [SuperAdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}',                                  [SuperAdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/impersonate',                     [SuperAdminUserController::class, 'impersonate'])->name('impersonate');
    Route::post('/stop-impersonating',                           [SuperAdminUserController::class, 'stopImpersonating'])->name('stop-impersonating');
    Route::get('/subscriptions',                                 [SuperAdminSubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/organizations/{organization}/subscriptions',   [SuperAdminSubscriptionController::class, 'store'])->name('organizations.add-subscription');
    Route::put('/subscriptions/{subscription}',                  [SuperAdminSubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}',               [SuperAdminSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::get('/products',                                      [SuperAdminProductController::class, 'index'])->name('products.index');
    Route::put('/products/{product}',                            [SuperAdminProductController::class, 'update'])->name('products.toggle-availability');
});

// Public form submission (no auth required)
Route::get('/forms/{slug}', [App\Http\Controllers\Api\OppFormController::class, 'showPublic'])->name('opp.forms.public');
Route::post('/forms/{slug}', [App\Http\Controllers\Api\OppFormController::class, 'submit'])->name('opp.forms.submit');

// Organization invitation accept (public, before auth)
Route::get('/org-invite/{token}', [OrganizationInvitationController::class, 'accept'])->name('org-invitation.accept');

// Main authenticated routes — requires org context
Route::middleware(['auth', 'org.context'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Hub (ecosystem home)
    Route::get('/hub', [HubController::class, 'index'])->name('hub');

    // Organizations
    Route::get('/org/{organization}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::put('/org/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::post('/org/{organization}/switch', [OrganizationController::class, 'switchOrganization'])->name('organizations.switch');

    // SmartBoard dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // SmartProjects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/{project}/board',      [ProjectController::class, 'board'])->name('projects.board');
    Route::get('/projects/{project}/milestones', [ProjectController::class, 'milestones'])->name('projects.milestones');
    Route::get('/projects/{project}/overview',   [ProjectController::class, 'overview'])->name('projects.overview');
    Route::get('/projects/{project}/timeline',   [ProjectController::class, 'timeline'])->name('projects.timeline');
    Route::get('/projects/{project}/calendar',   [ProjectController::class, 'calendar'])->name('projects.calendar');
    Route::get('/projects/{project}/backlog',    [ProjectController::class, 'backlog'])->name('projects.backlog');
    Route::get('/projects/{project}/updates',    [ProjectController::class, 'updates'])->name('projects.updates');
    Route::get('/projects/{project}/billing',                        [ProjectController::class, 'billing'])->name('projects.billing');
    Route::get('/projects/{project}/billing/{week}/invoice',         [ProjectController::class, 'billingInvoice'])->name('projects.billing.invoice');
    Route::get('/projects/{project}/scope',       [ProjectController::class, 'scope'])->name('projects.scope');
    Route::get('/projects/{project}/timesheets',  [ProjectController::class, 'timesheets'])->name('projects.timesheets');
    Route::get('/projects/{project}/budget',       [ProjectController::class, 'budget'])->name('projects.budget');
    Route::get('/projects/{project}/resources',    [ProjectController::class, 'resources'])->name('projects.resources');
    Route::get('/projects/{project}/reports',      [ProjectController::class, 'reports'])->name('projects.reports');
    Route::get('/projects/{project}/chat',         [ProjectController::class, 'chat'])->name('projects.chat');
    Route::get('/projects/{project}/documents',    [ProjectController::class, 'documents'])->name('projects.documents');
    Route::get('/projects/{project}/recycle-bin',   [ProjectController::class, 'recycleBin'])->name('projects.recycle-bin');
    Route::get('/projects/{project}/workload',     [ProjectController::class, 'workload'])->name('projects.workload');

    // Clients
    Route::get('/clients',           [ClientController::class, 'index'])->name('clients.index');
    Route::post('/clients',          [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}',  [ClientController::class, 'show'])->name('clients.show');
    Route::put('/clients/{client}',  [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

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
    Route::get('/b/{board}/timeline', [BoardViewController::class, 'timeline'])->name('boards.timeline');
    Route::get('/b/{board}/table', [BoardViewController::class, 'table'])->name('boards.table');
    Route::get('/b/{board}/dashboard', [BoardViewController::class, 'boardDashboard'])->name('boards.dashboard');

    // Member Directory
    Route::get('/w/{workspace}/members', [MemberDirectoryController::class, 'index'])->name('workspace-members.index');

    // ── Opportunity (Asana-clone product) ──────────────────────────
    Route::prefix('opportunity')->name('opportunity.')->group(function () {
        Route::get('/',                              [OppDashboardController::class, 'home'])->name('home');
        Route::get('/my-tasks',                      [OppDashboardController::class, 'myTasks'])->name('my-tasks');
        Route::get('/inbox',                         [OppDashboardController::class, 'inbox'])->name('inbox');
        Route::get('/projects',                      [OppProjectController::class, 'index'])->name('projects.index');
        Route::post('/projects',                     [OppProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}',            [OppProjectController::class, 'show'])->name('projects.show');
        Route::put('/projects/{project}',            [OppProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}',         [OppProjectController::class, 'destroy'])->name('projects.destroy');
        Route::get('/projects/{project}/overview',   [OppProjectController::class, 'overview'])->name('projects.overview');
        Route::get('/projects/{project}/board',      [OppProjectController::class, 'board'])->name('projects.board');
        Route::get('/projects/{project}/timeline',   [OppProjectController::class, 'timeline'])->name('projects.timeline');
        Route::get('/projects/{project}/calendar',   [OppProjectController::class, 'calendar'])->name('projects.calendar');

        // Goals
        Route::get('/goals',                          [OppGoalController::class, 'index'])->name('goals.index');
        Route::post('/goals',                         [OppGoalController::class, 'store'])->name('goals.store');
        Route::get('/goals/{goal}',                   [OppGoalController::class, 'show'])->name('goals.show');
        Route::put('/goals/{goal}',                   [OppGoalController::class, 'update'])->name('goals.update');
        Route::delete('/goals/{goal}',                [OppGoalController::class, 'destroy'])->name('goals.destroy');

        // Portfolios
        Route::get('/portfolios',                     [OppPortfolioController::class, 'index'])->name('portfolios.index');
        Route::post('/portfolios',                    [OppPortfolioController::class, 'store'])->name('portfolios.store');
        Route::get('/portfolios/{portfolio}',         [OppPortfolioController::class, 'show'])->name('portfolios.show');
        Route::delete('/portfolios/{portfolio}',      [OppPortfolioController::class, 'destroy'])->name('portfolios.destroy');

        // Reports
        Route::get('/reporting',                      [OppReportController::class, 'index'])->name('reporting.index');

        // Templates
        Route::get('/templates',                      [OppTemplateController::class, 'index'])->name('templates.index');
        Route::post('/projects/{project}/save-as-template', [OppTemplateController::class, 'saveAsTemplate'])->name('projects.save-template');
        Route::post('/templates/{project}/create',    [OppTemplateController::class, 'createFromTemplate'])->name('templates.create');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile/full', [EmployeeProfileController::class, 'show'])->name('profile.full');
    Route::put('/profile/basic', [EmployeeProfileController::class, 'update'])->name('profile.basic.update');

    // Roles & Permissions Management
    Route::get('/org/{organization}/roles',           [RoleController::class, 'index'])->name('roles.index');
    Route::get('/org/{organization}/roles/create',    [RoleController::class, 'create'])->name('roles.create');
    Route::post('/org/{organization}/roles',          [RoleController::class, 'store'])->name('roles.store');
    Route::get('/org/{organization}/roles/{role}',    [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/org/{organization}/roles/{role}',    [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/org/{organization}/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // User Management
    Route::get('/org/{organization}/users',                          [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/org/{organization}/users/{user}',                   [UserManagementController::class, 'show'])->name('users.show');
    Route::get('/org/{organization}/users/{user}/edit',              [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/org/{organization}/users/{user}',                   [UserManagementController::class, 'update'])->name('users.update');
    Route::put('/org/{organization}/users/{user}/role',              [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::post('/org/{organization}/users/{user}/deactivate',       [UserManagementController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/org/{organization}/users/{user}/activate',         [UserManagementController::class, 'activate'])->name('users.activate');

    // Organization Invitations
    Route::post('/org/{organization}/invitations',                         [OrganizationInvitationController::class, 'store'])->name('org-invitations.store');
    Route::post('/org/{organization}/invitations/{invitation}/resend',     [OrganizationInvitationController::class, 'resend'])->name('org-invitations.resend');
    Route::delete('/org/{organization}/invitations/{invitation}',          [OrganizationInvitationController::class, 'cancel'])->name('org-invitations.cancel');
});
