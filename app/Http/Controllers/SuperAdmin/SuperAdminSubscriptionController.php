<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Product;
use App\Models\SuperAdminAuditLog;
use Illuminate\Http\Request;

class SuperAdminSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $subscriptions = OrganizationSubscription::with(['organization', 'product'])
            ->when($request->product, function ($query, $product) {
                $query->where('product_id', $product);
            })
            ->when($request->plan, function ($query, $plan) {
                $query->where('plan', $plan);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->paginate(25);

        $products = Product::all();

        return view('super-admin.subscriptions.index', compact('subscriptions', 'products'));
    }

    public function store(Request $request, Organization $organization)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'plan' => 'required|in:free,pro,enterprise',
        ]);

        $subscription = OrganizationSubscription::create([
            'organization_id' => $organization->id,
            'product_id' => $request->product_id,
            'plan' => $request->plan,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        SuperAdminAuditLog::record(auth()->user(), 'subscription.created', $subscription);

        return redirect()->back()->with('success', 'Subscription created successfully.');
    }

    public function update(Request $request, OrganizationSubscription $subscription)
    {
        $request->validate([
            'plan' => 'required|in:free,pro,enterprise',
            'status' => 'required|string',
        ]);

        $subscription->update([
            'plan' => $request->plan,
            'status' => $request->status,
        ]);

        SuperAdminAuditLog::record(auth()->user(), 'subscription.updated', $subscription);

        return redirect()->back()->with('success', 'Subscription updated successfully.');
    }

    public function destroy(OrganizationSubscription $subscription)
    {
        SuperAdminAuditLog::record(auth()->user(), 'subscription.removed', $subscription);

        $subscription->delete();

        return redirect()->back()->with('success', 'Subscription removed successfully.');
    }
}
