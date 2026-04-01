<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveBalance;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrLeaveApiController extends Controller
{
    /**
     * Store a new leave request.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();
        $profile = EmployeeProfile::where('user_id', auth()->id())
            ->where('organization_id', $org->id)
            ->firstOrFail();

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:hr_leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'boolean',
            'half_day_period' => 'nullable|in:first_half,second_half',
            'reason' => 'required|string|max:1000',
        ]);

        // Calculate days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $days = $startDate->diffInDays($endDate) + 1;

        if (!empty($validated['is_half_day']) && $validated['is_half_day']) {
            $days = 0.5;
        }

        // Check balance
        $balance = HrLeaveBalance::where('employee_profile_id', $profile->id)
            ->where('hr_leave_type_id', $validated['leave_type_id'])
            ->where('year', Carbon::now()->year)
            ->first();

        if ($balance && $balance->available < $days) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient leave balance. Available: ' . $balance->available . ' days.',
            ], 422);
        }

        $leaveRequest = HrLeaveRequest::create([
            'organization_id' => $org->id,
            'employee_profile_id' => $profile->id,
            'hr_leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days' => $days,
            'is_half_day' => $validated['is_half_day'] ?? false,
            'half_day_period' => $validated['half_day_period'] ?? null,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'applied_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully.',
            'leave_request' => $leaveRequest->load('leaveType'),
        ]);
    }

    /**
     * Approve a leave request.
     */
    public function approve(HrLeaveRequest $leaveRequest)
    {
        abort_unless(auth()->check(), 401);

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be approved.',
            ], 422);
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'actioned_at' => Carbon::now(),
        ]);

        // Deduct balance
        $balance = HrLeaveBalance::where('employee_profile_id', $leaveRequest->employee_profile_id)
            ->where('hr_leave_type_id', $leaveRequest->hr_leave_type_id)
            ->where('year', Carbon::now()->year)
            ->first();

        if ($balance) {
            $balance->update([
                'used' => $balance->used + $leaveRequest->days,
                'available' => $balance->available - $leaveRequest->days,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved.',
            'leave_request' => $leaveRequest->fresh()->load('leaveType'),
        ]);
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, HrLeaveRequest $leaveRequest)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be rejected.',
            ], 422);
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'],
            'actioned_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected.',
            'leave_request' => $leaveRequest->fresh()->load('leaveType'),
        ]);
    }

    /**
     * Cancel a leave request (own requests only).
     */
    public function cancel(HrLeaveRequest $leaveRequest)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();
        $profile = EmployeeProfile::where('user_id', auth()->id())
            ->where('organization_id', $org->id)
            ->firstOrFail();

        // Only own requests
        if ($leaveRequest->employee_profile_id !== $profile->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel your own leave requests.',
            ], 403);
        }

        $wasApproved = $leaveRequest->status === 'approved';

        $leaveRequest->update([
            'status' => 'cancelled',
            'actioned_at' => Carbon::now(),
        ]);

        // Restore balance if was approved
        if ($wasApproved) {
            $balance = HrLeaveBalance::where('employee_profile_id', $leaveRequest->employee_profile_id)
                ->where('hr_leave_type_id', $leaveRequest->hr_leave_type_id)
                ->where('year', Carbon::now()->year)
                ->first();

            if ($balance) {
                $balance->update([
                    'used' => $balance->used - $leaveRequest->days,
                    'available' => $balance->available + $leaveRequest->days,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave request cancelled.',
            'leave_request' => $leaveRequest->fresh()->load('leaveType'),
        ]);
    }

    /**
     * Get leave balances for current year.
     */
    public function balances(EmployeeProfile $profile)
    {
        abort_unless(auth()->check(), 401);

        $balances = HrLeaveBalance::where('employee_profile_id', $profile->id)
            ->where('year', Carbon::now()->year)
            ->with('leaveType')
            ->get();

        return response()->json([
            'success' => true,
            'balances' => $balances,
        ]);
    }
}
