<?php

use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\ProjectActivityController;
use App\Http\Controllers\Api\ProjectAttachmentController;
use App\Http\Controllers\Api\ProjectCommentController;
use App\Http\Controllers\Api\ProjectLabelController;
use App\Http\Controllers\Api\ProjectMemberController;
use App\Http\Controllers\Api\ProjectMilestoneController;
use App\Http\Controllers\Api\EmployeeProfileApiController;
use App\Http\Controllers\Api\OppApprovalController;
use App\Http\Controllers\Api\OppAttachmentController;
use App\Http\Controllers\Api\OppCommentController;
use App\Http\Controllers\Api\OppFavoriteController;
use App\Http\Controllers\Api\OppFormController;
use App\Http\Controllers\Api\OppRuleController;
use App\Http\Controllers\Api\OppSearchController;
use App\Http\Controllers\Api\OppSectionController;
use App\Http\Controllers\Api\OppTagController;
use App\Http\Controllers\Api\OppTaskController;
use App\Http\Controllers\Opportunity\OppGoalController;
use App\Http\Controllers\Opportunity\OppPortfolioController;
use App\Http\Controllers\Opportunity\OppReportController;
use App\Http\Controllers\Api\ProjectBulkActionController;
use App\Http\Controllers\Api\ProjectChatController;
use App\Http\Controllers\Api\ProjectCustomFieldController;
use App\Http\Controllers\Api\ProjectDocumentController;
use App\Http\Controllers\Api\ProjectRecycleBinController;
use App\Http\Controllers\Api\ProjectSavedViewController;
use App\Http\Controllers\Api\ProjectTaskChecklistController;
use App\Http\Controllers\Api\ProjectTemplateController;
use App\Http\Controllers\Api\RecurringTaskController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\TimesheetController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\ProjectStatusController;
use App\Http\Controllers\Api\ProjectTaskController;
use App\Http\Controllers\Api\ProjectTaskLinkController;
use App\Http\Controllers\Api\ProjectTaskListController;
use App\Http\Controllers\Api\ProjectBillingController;
use App\Http\Controllers\Api\ProjectScopeChangeController;
use App\Http\Controllers\Api\ProjectTimeLogController;
use App\Http\Controllers\Api\ProjectWeeklyUpdateController;
use App\Http\Controllers\Api\SprintController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Api\BoardChatController;
use App\Http\Controllers\Api\BoardListController;
use App\Http\Controllers\Api\BulkActionController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\CardDependencyController;
use App\Http\Controllers\Api\CardMemberController;
use App\Http\Controllers\Api\CardTemplateController;
use App\Http\Controllers\Api\CardVoteController;
use App\Http\Controllers\Api\ChecklistController;
use App\Http\Controllers\Api\ChecklistTemplateController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\InboundEmailController;
use App\Http\Controllers\Api\LabelController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\WorkspaceGroupController;
use App\Http\Controllers\Api\WorkspaceTemplateController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardMemberController;
use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// All API routes require auth via web session
Route::middleware(['web', 'auth', 'throttle:api'])->group(function () {

    // ── BAI Board API ────────────────────────────────────────────────
    Route::middleware('product.access:board')->group(function () {

    // Board Members
    Route::get('/boards/{board}/members', [BoardMemberController::class, 'index']);
    Route::post('/boards/{board}/members', [BoardMemberController::class, 'store']);
    Route::put('/boards/{board}/members/{member}', [BoardMemberController::class, 'update']);
    Route::delete('/boards/{board}/members/{member}', [BoardMemberController::class, 'destroy']);
    Route::post('/boards/{board}/invitations/{invitation}/cancel', [BoardMemberController::class, 'cancelInvitation']);
    Route::post('/boards/{board}/invitations/{invitation}/resend', [BoardMemberController::class, 'resendInvitation']);

    // Lists
    Route::post('/boards/{board}/lists', [BoardListController::class, 'store']);
    Route::put('/lists/{list}', [BoardListController::class, 'update'])->where('list', '[0-9]+');
    Route::delete('/lists/{list}', [BoardListController::class, 'destroy'])->where('list', '[0-9]+');
    Route::post('/lists/{list}/archive', [BoardListController::class, 'archive']);
    Route::post('/lists/{list}/restore', [BoardListController::class, 'restore']);
    Route::patch('/boards/{board}/lists/reorder', [BoardListController::class, 'reorder']);
    Route::post('/lists/{list}/copy', [BoardListController::class, 'copy']);
    Route::post('/lists/{list}/move-all-cards', [BoardListController::class, 'moveAllCards']);

    // Cards
    Route::post('/lists/{list}/cards', [CardController::class, 'store']);
    Route::get('/cards/{card}', [CardController::class, 'show']);
    Route::put('/cards/{card}', [CardController::class, 'update']);
    Route::delete('/cards/{card}', [CardController::class, 'destroy']);
    Route::post('/cards/{card}/archive', [CardController::class, 'archive']);
    Route::post('/cards/{card}/restore', [CardController::class, 'restore']);
    Route::put('/cards/{card}/move', [CardController::class, 'move']);
    Route::patch('/lists/{list}/cards/reorder', [CardController::class, 'reorder']);
    Route::post('/cards/{card}/duplicate', [CardController::class, 'duplicate']);
    Route::post('/cards/{card}/watch', [CardController::class, 'toggleWatch']);
    Route::post('/cards/{card}/copy-to-board', [CardController::class, 'copyToBoard']);
    Route::post('/cards/{card}/move-to-board', [CardController::class, 'moveToBoard']);

    // Card Members
    Route::get('/cards/{card}/members', [CardMemberController::class, 'index']);
    Route::post('/cards/{card}/members', [CardMemberController::class, 'toggle']);

    // Labels
    Route::get('/boards/{board}/labels', [LabelController::class, 'index']);
    Route::post('/boards/{board}/labels', [LabelController::class, 'store']);
    Route::put('/labels/{label}', [LabelController::class, 'update']);
    Route::delete('/labels/{label}', [LabelController::class, 'destroy']);
    Route::post('/cards/{card}/labels', [LabelController::class, 'toggleCard']);

    // Checklists
    Route::post('/cards/{card}/checklists', [ChecklistController::class, 'store']);
    Route::put('/checklists/{checklist}', [ChecklistController::class, 'update']);
    Route::delete('/checklists/{checklist}', [ChecklistController::class, 'destroy']);
    Route::post('/checklists/{checklist}/items', [ChecklistController::class, 'storeItem']);
    Route::put('/checklist-items/{item}', [ChecklistController::class, 'updateItem']);
    Route::patch('/checklist-items/{item}/toggle', [ChecklistController::class, 'toggleItem']);
    Route::delete('/checklist-items/{item}', [ChecklistController::class, 'destroyItem']);

    // Comments
    Route::get('/cards/{card}/comments', [CommentController::class, 'index']);
    Route::post('/cards/{card}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Attachments
    Route::post('/cards/{card}/attachments', [AttachmentController::class, 'store']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download']);

    // Board archived items
    Route::get('/boards/{board}/archived-items', [BoardController::class, 'archivedItems']);

    // Custom Fields
    Route::get('/boards/{board}/custom-fields', [CustomFieldController::class, 'index']);
    Route::post('/boards/{board}/custom-fields', [CustomFieldController::class, 'store']);
    Route::put('/custom-fields/{field}', [CustomFieldController::class, 'update']);
    Route::delete('/custom-fields/{field}', [CustomFieldController::class, 'destroy']);
    Route::put('/cards/{card}/custom-fields', [CustomFieldController::class, 'updateCardValue']);

    // Card Templates
    Route::get('/boards/{board}/card-templates', [CardTemplateController::class, 'index']);
    Route::post('/cards/{card}/save-as-template', [CardTemplateController::class, 'store']);
    Route::post('/boards/{board}/card-from-template', [CardTemplateController::class, 'createFromTemplate']);
    Route::delete('/card-templates/{card}', [CardTemplateController::class, 'destroy']);

    // Card Votes
    Route::post('/cards/{card}/vote', [CardVoteController::class, 'toggle']);

    // Card Dependencies
    Route::get('/cards/{card}/dependencies', [CardDependencyController::class, 'index']);
    Route::post('/cards/{card}/dependencies', [CardDependencyController::class, 'store']);
    Route::delete('/card-dependencies/{dependency}', [CardDependencyController::class, 'destroy']);

    // Checklist Templates
    Route::get('/boards/{board}/checklist-templates', [ChecklistTemplateController::class, 'index']);
    Route::post('/boards/{board}/checklist-templates', [ChecklistTemplateController::class, 'store']);
    Route::post('/checklists/{checklist}/save-as-template', [ChecklistTemplateController::class, 'storeFromChecklist']);
    Route::post('/checklist-templates/{template}/apply/{card}', [ChecklistTemplateController::class, 'apply']);
    Route::delete('/checklist-templates/{template}', [ChecklistTemplateController::class, 'destroy']);

    // Bulk Actions
    Route::post('/boards/{board}/bulk-actions', [BulkActionController::class, 'execute']);

    // Board Search
    Route::get('/boards/{board}/search', [SearchController::class, 'boardSearch']);

    // Workspace Groups
    Route::get('/workspaces/{workspace}/groups', [WorkspaceGroupController::class, 'index']);
    Route::post('/workspaces/{workspace}/groups', [WorkspaceGroupController::class, 'store']);
    Route::put('/workspace-groups/{group}', [WorkspaceGroupController::class, 'update']);
    Route::delete('/workspace-groups/{group}', [WorkspaceGroupController::class, 'destroy']);

    // Workspace Templates
    Route::get('/workspace-templates', [WorkspaceTemplateController::class, 'index']);
    Route::post('/workspaces/{workspace}/save-as-template', [WorkspaceTemplateController::class, 'store']);
    Route::post('/workspace-templates/{template}/apply', [WorkspaceTemplateController::class, 'createFromTemplate']);
    Route::delete('/workspace-templates/{template}', [WorkspaceTemplateController::class, 'destroy']);

    // Inbound Email
    Route::post('/inbound-email', [InboundEmailController::class, 'receive']);

    // Board Chat
    Route::get('/boards/{board}/chat', [BoardChatController::class, 'index']);
    Route::post('/boards/{board}/chat', [BoardChatController::class, 'store']);
    Route::delete('/board-messages/{message}', [BoardChatController::class, 'destroy']);

    }); // end board product access

    // Notifications (cross-product)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // ── BAI Projects API ─────────────────────────────────────────────
    Route::middleware('product.access:projects')->group(function () {

    // SmartProjects — Resources & Capacity
    Route::get('/projects/{project}/capacities',               [ResourceController::class, 'capacities']);
    Route::post('/projects/{project}/capacities',              [ResourceController::class, 'storeCapacity']);
    Route::put('/user-capacities/{capacity}',                  [ResourceController::class, 'updateCapacity']);
    Route::get('/projects/{project}/workload',                 [ResourceController::class, 'workload']);
    Route::get('/projects/{project}/budget-forecast',          [ResourceController::class, 'budgetForecast']);

    // SmartProjects — Saved Views
    Route::get('/projects/{project}/saved-views',          [ProjectSavedViewController::class, 'index']);
    Route::post('/projects/{project}/saved-views',         [ProjectSavedViewController::class, 'store']);
    Route::put('/project-saved-views/{view}',              [ProjectSavedViewController::class, 'update']);
    Route::delete('/project-saved-views/{view}',           [ProjectSavedViewController::class, 'destroy']);

    // SmartProjects — Custom Fields
    Route::get('/projects/{project}/custom-fields',          [ProjectCustomFieldController::class, 'index']);
    Route::post('/projects/{project}/custom-fields',         [ProjectCustomFieldController::class, 'store']);
    Route::put('/project-custom-fields/{field}',             [ProjectCustomFieldController::class, 'update']);
    Route::delete('/project-custom-fields/{field}',          [ProjectCustomFieldController::class, 'destroy']);
    Route::put('/project-tasks/{task}/custom-fields',        [ProjectCustomFieldController::class, 'updateTaskValue']);

    // SmartProjects — Statuses
    Route::get('/projects/{project}/statuses',           [ProjectStatusController::class, 'index']);
    Route::post('/projects/{project}/statuses',          [ProjectStatusController::class, 'store']);
    Route::put('/project-statuses/{status}',             [ProjectStatusController::class, 'update']);
    Route::delete('/project-statuses/{status}',          [ProjectStatusController::class, 'destroy']);
    Route::patch('/projects/{project}/statuses/reorder', [ProjectStatusController::class, 'reorder']);

    // SmartProjects — Task Lists
    Route::post('/projects/{project}/task-lists', [ProjectTaskListController::class, 'store']);
    Route::put('/project-task-lists/{taskList}', [ProjectTaskListController::class, 'update']);
    Route::delete('/project-task-lists/{taskList}', [ProjectTaskListController::class, 'destroy']);
    Route::patch('/projects/{project}/task-lists/reorder', [ProjectTaskListController::class, 'reorder']);

    // SmartProjects — Tasks
    Route::post('/projects/{project}/tasks', [ProjectTaskController::class, 'store']);
    Route::get('/project-tasks/{task}', [ProjectTaskController::class, 'show']);
    Route::put('/project-tasks/{task}', [ProjectTaskController::class, 'update']);
    Route::delete('/project-tasks/{task}', [ProjectTaskController::class, 'destroy']);
    Route::put('/project-tasks/{task}/move', [ProjectTaskController::class, 'move']);
    Route::patch('/projects/{project}/tasks/reorder', [ProjectTaskController::class, 'reorder']);

    // SmartProjects — Comments
    Route::get('/project-tasks/{task}/comments', [ProjectCommentController::class, 'index']);
    Route::post('/project-tasks/{task}/comments', [ProjectCommentController::class, 'store']);
    Route::delete('/project-comments/{comment}', [ProjectCommentController::class, 'destroy']);

    // SmartProjects — Milestones
    Route::post('/projects/{project}/milestones', [ProjectMilestoneController::class, 'store']);
    Route::put('/project-milestones/{milestone}', [ProjectMilestoneController::class, 'update']);
    Route::delete('/project-milestones/{milestone}', [ProjectMilestoneController::class, 'destroy']);

    // SmartProjects — Members
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store']);
    Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy']);

    // SmartProjects — Attachments
    Route::post('/project-tasks/{task}/attachments', [ProjectAttachmentController::class, 'store']);
    Route::delete('/project-attachments/{attachment}', [ProjectAttachmentController::class, 'destroy']);
    Route::get('/project-attachments/{attachment}/download', [ProjectAttachmentController::class, 'download']);

    // SmartProjects — Labels
    Route::get('/projects/{project}/labels',    [ProjectLabelController::class, 'index']);
    Route::post('/projects/{project}/labels',   [ProjectLabelController::class, 'store']);
    Route::put('/project-labels/{label}',       [ProjectLabelController::class, 'update']);
    Route::delete('/project-labels/{label}',    [ProjectLabelController::class, 'destroy']);
    Route::post('/project-tasks/{task}/labels', [ProjectLabelController::class, 'toggle']);

    // SmartProjects — Activity log
    Route::get('/project-tasks/{task}/activity', [ProjectActivityController::class, 'index']);

    // SmartProjects — Time logs
    Route::get('/project-tasks/{task}/time-logs',    [ProjectTimeLogController::class, 'index']);
    Route::post('/project-tasks/{task}/time-logs',   [ProjectTimeLogController::class, 'store']);
    Route::put('/project-time-logs/{log}',           [ProjectTimeLogController::class, 'update']);
    Route::delete('/project-time-logs/{log}',        [ProjectTimeLogController::class, 'destroy']);

    // SmartProjects — Timer
    Route::post('/project-tasks/{task}/timer/start', [ProjectTimeLogController::class, 'startTimer']);
    Route::post('/project-tasks/{task}/timer/stop',  [ProjectTimeLogController::class, 'stopTimer']);
    Route::get('/timer/active',                      [ProjectTimeLogController::class, 'activeTimer']);

    // SmartProjects — Timesheets
    Route::post('/projects/{project}/timesheets/submit',            [TimesheetController::class, 'submit']);
    Route::get('/projects/{project}/timesheets/submissions',        [TimesheetController::class, 'submissions']);
    Route::post('/timesheet-submissions/{submission}/approve',      [TimesheetController::class, 'approve']);
    Route::post('/timesheet-submissions/{submission}/reject',       [TimesheetController::class, 'reject']);

    // SmartProjects — Task links
    Route::get('/project-tasks/{task}/links',    [ProjectTaskLinkController::class, 'index']);
    Route::post('/project-tasks/{task}/links',   [ProjectTaskLinkController::class, 'store']);
    Route::delete('/project-task-links/{link}',  [ProjectTaskLinkController::class, 'destroy']);

    // SmartProjects — Watch
    Route::post('/project-tasks/{task}/watch', [ProjectTaskController::class, 'toggleWatch']);

    // SmartProjects — Sprints
    Route::get('/projects/{project}/sprints',                        [SprintController::class, 'index']);
    Route::post('/projects/{project}/sprints',                       [SprintController::class, 'store']);
    Route::put('/project-sprints/{sprint}',                          [SprintController::class, 'update']);
    Route::delete('/project-sprints/{sprint}',                       [SprintController::class, 'destroy']);
    Route::post('/project-sprints/{sprint}/tasks',                   [SprintController::class, 'addTask']);
    Route::delete('/project-sprints/{sprint}/tasks/{task}',          [SprintController::class, 'removeTask']);
    Route::post('/project-sprints/{sprint}/complete',                [SprintController::class, 'complete']);

    // SmartProjects — Scope changes
    Route::post('/projects/{project}/scope-changes',    [ProjectScopeChangeController::class, 'store']);
    Route::put('/project-scope-changes/{change}',       [ProjectScopeChangeController::class, 'update']);
    Route::delete('/project-scope-changes/{change}',    [ProjectScopeChangeController::class, 'destroy']);

    // SmartProjects — Weekly updates
    Route::post('/projects/{project}/weekly-updates',   [ProjectWeeklyUpdateController::class, 'store']);
    Route::put('/project-weekly-updates/{update}',      [ProjectWeeklyUpdateController::class, 'update']);
    Route::delete('/project-weekly-updates/{update}',   [ProjectWeeklyUpdateController::class, 'destroy']);

    // SmartProjects — Billing
    Route::post('/projects/{project}/billing-weeks',         [ProjectBillingController::class, 'store']);
    Route::put('/project-billing-weeks/{week}',              [ProjectBillingController::class, 'update']);
    Route::post('/project-billing-weeks/{week}/lock',        [ProjectBillingController::class, 'lock']);
    Route::get('/project-billing-weeks/{week}/logs',         [ProjectBillingController::class, 'logs']);
    Route::put('/project-billing-entries/{entry}',           [ProjectBillingController::class, 'updateEntry']);

    // SmartProjects — Task Checklists
    Route::post('/project-tasks/{task}/checklists',                          [ProjectTaskChecklistController::class, 'store']);
    Route::put('/project-task-checklists/{checklist}',                       [ProjectTaskChecklistController::class, 'update']);
    Route::delete('/project-task-checklists/{checklist}',                    [ProjectTaskChecklistController::class, 'destroy']);
    Route::post('/project-task-checklists/{checklist}/items',                [ProjectTaskChecklistController::class, 'storeItem']);
    Route::put('/project-task-checklist-items/{item}',                       [ProjectTaskChecklistController::class, 'updateItem']);
    Route::patch('/project-task-checklist-items/{item}/toggle',              [ProjectTaskChecklistController::class, 'toggleItem']);
    Route::delete('/project-task-checklist-items/{item}',                    [ProjectTaskChecklistController::class, 'destroyItem']);

    // SmartProjects — Project Chat
    Route::get('/projects/{project}/chat',                    [ProjectChatController::class, 'index']);
    Route::post('/projects/{project}/chat',                   [ProjectChatController::class, 'store']);
    Route::delete('/project-messages/{message}',              [ProjectChatController::class, 'destroy']);

    // SmartProjects — Documents
    Route::get('/projects/{project}/documents',               [ProjectDocumentController::class, 'index']);
    Route::get('/project-folders/{folder}',                   [ProjectDocumentController::class, 'showFolder']);
    Route::post('/projects/{project}/folders',                [ProjectDocumentController::class, 'createFolder']);
    Route::put('/project-folders/{folder}',                   [ProjectDocumentController::class, 'updateFolder']);
    Route::delete('/project-folders/{folder}',                [ProjectDocumentController::class, 'deleteFolder']);
    Route::post('/projects/{project}/documents/upload',       [ProjectDocumentController::class, 'upload']);

    // SmartProjects — Recurring Tasks
    Route::get('/projects/{project}/recurring-tasks',         [RecurringTaskController::class, 'index']);
    Route::post('/projects/{project}/recurring-tasks',        [RecurringTaskController::class, 'store']);
    Route::put('/recurring-tasks/{pattern}',                  [RecurringTaskController::class, 'update']);
    Route::delete('/recurring-tasks/{pattern}',               [RecurringTaskController::class, 'destroy']);
    Route::patch('/recurring-tasks/{pattern}/toggle',         [RecurringTaskController::class, 'toggleActive']);

    // SmartProjects — Bulk Actions
    Route::post('/projects/{project}/bulk-actions',           [ProjectBulkActionController::class, 'execute']);

    // SmartProjects — Recycle Bin
    Route::get('/projects/{project}/recycle-bin',             [ProjectRecycleBinController::class, 'index']);
    Route::post('/projects/{project}/recycle-bin/restore',    [ProjectRecycleBinController::class, 'restore']);
    Route::delete('/projects/{project}/recycle-bin/delete',   [ProjectRecycleBinController::class, 'permanentDelete']);
    Route::delete('/projects/{project}/recycle-bin/empty',    [ProjectRecycleBinController::class, 'emptyBin']);

    // SmartProjects — Templates & Cloning
    Route::get('/project-templates',                          [ProjectTemplateController::class, 'index']);
    Route::post('/projects/{project}/save-as-template',       [ProjectTemplateController::class, 'saveAsTemplate']);
    Route::post('/project-templates/{template}/create',       [ProjectTemplateController::class, 'createFromTemplate']);
    Route::post('/projects/{project}/clone',                  [ProjectTemplateController::class, 'clone']);
    Route::delete('/project-templates/{template}',            [ProjectTemplateController::class, 'destroy']);

    // Clients — API list for dropdowns
    Route::get('/clients', [ClientController::class, 'apiIndex']);

    }); // end projects product access

    // Employee Profile Sub-resources
    Route::post('/employee-profiles/{profile}/education',      [EmployeeProfileApiController::class, 'storeEducation']);
    Route::put('/employee-education/{education}',              [EmployeeProfileApiController::class, 'updateEducation']);
    Route::delete('/employee-education/{education}',           [EmployeeProfileApiController::class, 'destroyEducation']);
    Route::post('/employee-profiles/{profile}/experience',     [EmployeeProfileApiController::class, 'storeExperience']);
    Route::put('/employee-experience/{experience}',            [EmployeeProfileApiController::class, 'updateExperience']);
    Route::delete('/employee-experience/{experience}',         [EmployeeProfileApiController::class, 'destroyExperience']);
    Route::post('/employee-profiles/{profile}/documents',      [EmployeeProfileApiController::class, 'storeDocument']);
    Route::delete('/employee-documents/{document}',            [EmployeeProfileApiController::class, 'destroyDocument']);
    Route::post('/employee-profiles/{profile}/assets',         [EmployeeProfileApiController::class, 'storeAsset']);
    Route::put('/employee-assets/{asset}',                     [EmployeeProfileApiController::class, 'updateAsset']);
    Route::delete('/employee-assets/{asset}',                  [EmployeeProfileApiController::class, 'destroyAsset']);
    Route::post('/employee-profiles/{profile}/skills',         [EmployeeProfileApiController::class, 'storeSkill']);
    Route::delete('/employee-skills/{skill}',                  [EmployeeProfileApiController::class, 'destroySkill']);

    // ── Opportunity API ────────────────────────────────────────────
    Route::middleware('product.access:opportunity')->group(function () {
    Route::get('/opp/my-tasks',                         [OppTaskController::class, 'myTasks']);
    Route::post('/opp/tasks',                        [OppTaskController::class, 'store']);
    Route::get('/opp/tasks/{task}',                  [OppTaskController::class, 'show']);
    Route::put('/opp/tasks/{task}',                  [OppTaskController::class, 'update']);
    Route::delete('/opp/tasks/{task}',               [OppTaskController::class, 'destroy']);
    Route::post('/opp/tasks/{task}/complete',        [OppTaskController::class, 'complete']);
    Route::post('/opp/tasks/{task}/like',            [OppTaskController::class, 'toggleLike']);
    Route::put('/opp/tasks/{task}/move',             [OppTaskController::class, 'move']);
    Route::patch('/opp/tasks/reorder',               [OppTaskController::class, 'reorder']);
    Route::post('/opp/tasks/{task}/duplicate',       [OppTaskController::class, 'duplicate']);
    Route::post('/opp/tasks/{task}/followers',       [OppTaskController::class, 'toggleFollower']);
    Route::post('/opp/tasks/{task}/assignees',       [OppTaskController::class, 'toggleAssignee']);
    Route::post('/opp/sections',                     [OppSectionController::class, 'store']);
    Route::put('/opp/sections/{section}',            [OppSectionController::class, 'update']);
    Route::delete('/opp/sections/{section}',         [OppSectionController::class, 'destroy']);
    Route::patch('/opp/sections/reorder',            [OppSectionController::class, 'reorder']);
    Route::get('/opp/comments',                      [OppCommentController::class, 'index']);
    Route::post('/opp/comments',                     [OppCommentController::class, 'store']);
    Route::put('/opp/comments/{comment}',            [OppCommentController::class, 'update']);
    Route::delete('/opp/comments/{comment}',         [OppCommentController::class, 'destroy']);
    Route::post('/opp/tasks/{task}/attachments',     [OppAttachmentController::class, 'store']);
    Route::delete('/opp/attachments/{attachment}',   [OppAttachmentController::class, 'destroy']);
    Route::get('/opp/tags',                          [OppTagController::class, 'index']);
    Route::post('/opp/tags',                         [OppTagController::class, 'store']);
    Route::put('/opp/tags/{tag}',                    [OppTagController::class, 'update']);
    Route::delete('/opp/tags/{tag}',                 [OppTagController::class, 'destroy']);
    Route::post('/opp/tasks/{task}/tags',            [OppTagController::class, 'toggle']);

    // Opportunity — Project Members
    Route::get('/opp/projects/{project}/members',    [OppTaskController::class, 'projectMembers']);
    Route::post('/opp/projects/{project}/members',   [OppTaskController::class, 'addProjectMember']);
    Route::delete('/opp/projects/{project}/members/{user}', [OppTaskController::class, 'removeProjectMember']);

    // Opportunity — Rules (Automation)
    Route::get('/opp/projects/{project}/rules',      [OppRuleController::class, 'index']);
    Route::post('/opp/projects/{project}/rules',     [OppRuleController::class, 'store']);
    Route::put('/opp/rules/{rule}',                  [OppRuleController::class, 'update']);
    Route::delete('/opp/rules/{rule}',               [OppRuleController::class, 'destroy']);

    // Opportunity — Approvals
    Route::post('/opp/approvals',                    [OppApprovalController::class, 'store']);
    Route::post('/opp/approvals/{approval}/approve', [OppApprovalController::class, 'approve']);
    Route::post('/opp/approvals/{approval}/reject',  [OppApprovalController::class, 'reject']);

    // Opportunity — Goals
    Route::put('/opp/goals/{goal}/progress',         [OppGoalController::class, 'updateProgress']);
    Route::post('/opp/goals/{goal}/link',            [OppGoalController::class, 'linkItem']);
    Route::delete('/opp/goal-links/{link}',          [OppGoalController::class, 'unlinkItem']);

    // Opportunity — Reports
    Route::get('/opp/reports/task-completion',        [OppReportController::class, 'taskCompletion']);
    Route::get('/opp/reports/team-workload',          [OppReportController::class, 'teamWorkload']);
    Route::get('/opp/reports/project-progress',       [OppReportController::class, 'projectProgress']);

    // Opportunity — Portfolios
    Route::post('/opp/portfolios/{portfolio}/projects',              [OppPortfolioController::class, 'addProject']);
    Route::delete('/opp/portfolios/{portfolio}/projects/{project}',  [OppPortfolioController::class, 'removeProject']);

    // Opportunity — Forms
    Route::get('/opp/projects/{project}/forms',      [OppFormController::class, 'index']);
    Route::post('/opp/forms',                        [OppFormController::class, 'store']);
    Route::put('/opp/forms/{form}',                  [OppFormController::class, 'update']);
    Route::delete('/opp/forms/{form}',               [OppFormController::class, 'destroy']);

    // Opportunity — Search
    Route::get('/opp/search',                        [OppSearchController::class, 'search']);
    Route::get('/opp/saved-searches',                [OppSearchController::class, 'savedSearches']);
    Route::post('/opp/saved-searches',               [OppSearchController::class, 'saveSearch']);
    Route::delete('/opp/saved-searches/{search}',    [OppSearchController::class, 'deleteSavedSearch']);

    // Opportunity — Favorites
    Route::get('/opp/favorites',                     [OppFavoriteController::class, 'index']);
    Route::post('/opp/favorites/toggle',             [OppFavoriteController::class, 'toggle']);
    }); // end opportunity product access

    // ── BAI HR API ──────────────────────────────────────────────────
    Route::middleware('product.access:hr')->group(function () {
    Route::post('/hr/attendance/clock-in',                    [\App\Http\Controllers\Api\HrAttendanceApiController::class, 'clockIn']);
    Route::post('/hr/attendance/clock-out',                   [\App\Http\Controllers\Api\HrAttendanceApiController::class, 'clockOut']);
    Route::post('/hr/attendance/{log}/regularize',            [\App\Http\Controllers\Api\HrAttendanceApiController::class, 'regularize']);
    Route::get('/hr/attendance/today',                        [\App\Http\Controllers\Api\HrAttendanceApiController::class, 'todayStatus']);
    Route::post('/hr/leave-requests',                         [\App\Http\Controllers\Api\HrLeaveApiController::class, 'store']);
    Route::post('/hr/leave-requests/{leaveRequest}/approve',   [\App\Http\Controllers\Api\HrLeaveApiController::class, 'approve']);
    Route::post('/hr/leave-requests/{leaveRequest}/reject',   [\App\Http\Controllers\Api\HrLeaveApiController::class, 'reject']);
    Route::post('/hr/leave-requests/{leaveRequest}/cancel',   [\App\Http\Controllers\Api\HrLeaveApiController::class, 'cancel']);
    Route::get('/hr/leave-balances/{profile}',                [\App\Http\Controllers\Api\HrLeaveApiController::class, 'balances']);
    Route::get('/hr/salary-components',                        [\App\Http\Controllers\Api\HrPayrollApiController::class, 'listComponents']);
    Route::post('/hr/salary-components',                       [\App\Http\Controllers\Api\HrPayrollApiController::class, 'storeComponent']);
    Route::put('/hr/salary-components/{component}',            [\App\Http\Controllers\Api\HrPayrollApiController::class, 'updateComponent']);
    Route::delete('/hr/salary-components/{component}',         [\App\Http\Controllers\Api\HrPayrollApiController::class, 'deleteComponent']);
    Route::get('/hr/salary-structures/{profile}',              [\App\Http\Controllers\Api\HrPayrollApiController::class, 'getStructure']);
    Route::post('/hr/salary-structures/{profile}',             [\App\Http\Controllers\Api\HrPayrollApiController::class, 'saveStructure']);
    Route::post('/hr/payroll-runs',                            [\App\Http\Controllers\Api\HrPayrollApiController::class, 'processRun']);
    Route::post('/hr/payroll-runs/{run}/finalize',            [\App\Http\Controllers\Api\HrPayrollApiController::class, 'finalizeRun']);
    Route::post('/hr/payroll-runs/{run}/mark-paid',           [\App\Http\Controllers\Api\HrPayrollApiController::class, 'markPaid']);
    Route::post('/hr/reviews/{review}/submit',                [\App\Http\Controllers\Api\HrReviewApiController::class, 'submitReview']);
    Route::post('/hr/review-ratings',                         [\App\Http\Controllers\Api\HrReviewApiController::class, 'rateItem']);
    Route::post('/hr/expense-claims',                         [\App\Http\Controllers\Api\HrExpenseApiController::class, 'store']);
    Route::post('/hr/expense-claims/{claim}/submit',          [\App\Http\Controllers\Api\HrExpenseApiController::class, 'submit']);
    Route::post('/hr/expense-claims/{claim}/approve',         [\App\Http\Controllers\Api\HrExpenseApiController::class, 'approve']);
    Route::post('/hr/expense-claims/{claim}/reject',          [\App\Http\Controllers\Api\HrExpenseApiController::class, 'reject']);
    Route::post('/hr/expense-claims/{claim}/reimburse',       [\App\Http\Controllers\Api\HrExpenseApiController::class, 'reimburse']);
    Route::post('/hr/job-postings',                           [\App\Http\Controllers\Api\HrRecruitmentApiController::class, 'storePosting']);
    Route::post('/hr/candidates',                             [\App\Http\Controllers\Api\HrRecruitmentApiController::class, 'storeCandidate']);
    Route::post('/hr/candidates/{candidate}/move',            [\App\Http\Controllers\Api\HrRecruitmentApiController::class, 'moveCandidate']);
    Route::post('/hr/interviews',                             [\App\Http\Controllers\Api\HrRecruitmentApiController::class, 'scheduleInterview']);
    Route::put('/hr/interviews/{interview}',                  [\App\Http\Controllers\Api\HrRecruitmentApiController::class, 'submitFeedback']);
    Route::post('/hr/surveys',                                [\App\Http\Controllers\Api\HrSurveyApiController::class, 'store']);
    Route::post('/hr/surveys/{survey}/publish',               [\App\Http\Controllers\Api\HrSurveyApiController::class, 'publish']);
    Route::post('/hr/surveys/{survey}/close',                 [\App\Http\Controllers\Api\HrSurveyApiController::class, 'close']);
    Route::post('/hr/surveys/{survey}/respond',               [\App\Http\Controllers\Api\HrSurveyApiController::class, 'submitResponse']);
    Route::post('/hr/announcements',                          [\App\Http\Controllers\Api\HrAnnouncementApiController::class, 'store']);
    Route::put('/hr/announcements/{announcement}',            [\App\Http\Controllers\Api\HrAnnouncementApiController::class, 'update']);
    Route::delete('/hr/announcements/{announcement}',         [\App\Http\Controllers\Api\HrAnnouncementApiController::class, 'destroy']);
    Route::post('/hr/announcements/{announcement}/pin',       [\App\Http\Controllers\Api\HrAnnouncementApiController::class, 'pin']);
    Route::post('/hr/recognitions',                           [\App\Http\Controllers\Api\HrRecognitionApiController::class, 'store']);
    Route::delete('/hr/recognitions/{recognition}',           [\App\Http\Controllers\Api\HrRecognitionApiController::class, 'destroy']);
    }); // end hr product access

    // Global Search (cross-product)
    Route::middleware('throttle:global-search')
        ->get('/global-search', [\App\Http\Controllers\Api\GlobalSearchController::class, 'search']);

    // Webhooks
    Route::get('/webhooks',                                   [WebhookController::class, 'index']);
    Route::post('/webhooks',                                  [WebhookController::class, 'store']);
    Route::put('/webhooks/{webhook}',                         [WebhookController::class, 'update']);
    Route::delete('/webhooks/{webhook}',                      [WebhookController::class, 'destroy']);
    Route::get('/webhooks/{webhook}/logs',                    [WebhookController::class, 'logs']);
    Route::post('/webhooks/{webhook}/test',                   [WebhookController::class, 'test']);
});
