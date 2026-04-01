<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrExpenseClaim;
use App\Models\HrExpenseItem;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrExpenseApiController extends Controller
{
    /**
     * Store a new expense claim with items.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();
        $profile = EmployeeProfile::where('user_id', auth()->id())
            ->where('organization_id', $org->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.hr_expense_category_id' => 'required|exists:hr_expense_categories,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.expense_date' => 'required|date',
            'items.*.receipt_path' => 'nullable|string',
        ]);

        $totalAmount = collect($validated['items'])->sum('amount');

        $claim = HrExpenseClaim::create([
            'organization_id' => $org->id,
            'employee_profile_id' => $profile->id,
            'title' => $validated['title'],
            'total_amount' => $totalAmount,
            'status' => 'draft',
        ]);

        foreach ($validated['items'] as $itemData) {
            $claim->items()->create($itemData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Expense claim created successfully.',
            'claim' => $claim->fresh()->load('items.category'),
        ]);
    }

    /**
     * Submit an expense claim for approval.
     */
    public function submit(HrExpenseClaim $claim)
    {
        abort_unless(auth()->check(), 401);

        if ($claim->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft claims can be submitted.',
            ], 422);
        }

        $claim->update([
            'status' => 'submitted',
            'submitted_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense claim submitted for approval.',
            'claim' => $claim->fresh(),
        ]);
    }

    /**
     * Approve an expense claim.
     */
    public function approve(HrExpenseClaim $claim)
    {
        abort_unless(auth()->check(), 401);

        if ($claim->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted claims can be approved.',
            ], 422);
        }

        $claim->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense claim approved.',
            'claim' => $claim->fresh(),
        ]);
    }

    /**
     * Reject an expense claim.
     */
    public function reject(Request $request, HrExpenseClaim $claim)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($claim->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted claims can be rejected.',
            ], 422);
        }

        $claim->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense claim rejected.',
            'claim' => $claim->fresh(),
        ]);
    }

    /**
     * Mark an expense claim as reimbursed.
     */
    public function reimburse(HrExpenseClaim $claim)
    {
        abort_unless(auth()->check(), 401);

        if ($claim->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved claims can be reimbursed.',
            ], 422);
        }

        $claim->update([
            'status' => 'reimbursed',
            'reimbursed_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense claim marked as reimbursed.',
            'claim' => $claim->fresh(),
        ]);
    }
}
