<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\SuperAdminAuditLog;
use App\Models\User;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'organizations' => Organization::count(),
            'users' => User::count(),
            'active_subscriptions' => OrganizationSubscription::where('status', 'active')->count(),
        ];

        $recentOrgs = Organization::latest()->take(5)->get();
        $recentUsers = User::latest()->take(5)->get();

        $subscriptionBreakdown = OrganizationSubscription::where('status', 'active')
            ->selectRaw('plan, count(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        return view('super-admin.dashboard', compact(
            'stats',
            'recentOrgs',
            'recentUsers',
            'subscriptionBreakdown'
        ));
    }

    public function auditLog()
    {
        $logs = SuperAdminAuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('super-admin.audit-log', compact('logs'));
    }
}
