<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrAttendanceLog;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HrAttendanceApiController extends Controller
{
    /**
     * Clock in for today.
     */
    public function clockIn(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();
        $profile = EmployeeProfile::where('user_id', auth()->id())
            ->where('organization_id', $org->id)
            ->firstOrFail();

        // Check no existing log for today
        $existing = HrAttendanceLog::where('employee_profile_id', $profile->id)
            ->where('organization_id', $org->id)
            ->where('date', Carbon::today())
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already clocked in today.',
            ], 422);
        }

        $log = HrAttendanceLog::create([
            'organization_id' => $org->id,
            'employee_profile_id' => $profile->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'clock_in_ip' => $request->ip(),
            'source' => 'web',
            'status' => 'present',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully.',
            'log' => $log,
        ]);
    }

    /**
     * Clock out for today.
     */
    public function clockOut(Request $request)
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();
        $profile = EmployeeProfile::where('user_id', auth()->id())
            ->where('organization_id', $org->id)
            ->firstOrFail();

        $log = HrAttendanceLog::where('employee_profile_id', $profile->id)
            ->where('organization_id', $org->id)
            ->where('date', Carbon::today())
            ->whereNotNull('clock_in')
            ->whereNull('clock_out')
            ->firstOrFail();

        $clockOut = Carbon::now();
        $totalHours = round($log->clock_in->diffInMinutes($clockOut) / 60, 2);

        $log->update([
            'clock_out' => $clockOut,
            'clock_out_ip' => $request->ip(),
            'total_hours' => $totalHours,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully.',
            'log' => $log->fresh(),
        ]);
    }

    /**
     * Regularize an attendance log.
     */
    public function regularize(Request $request, HrAttendanceLog $log)
    {
        abort_unless(auth()->check(), 401);

        $validated = $request->validate([
            'clock_in' => 'required|date',
            'clock_out' => 'required|date|after:clock_in',
            'remarks' => 'required|string|max:500',
        ]);

        $clockIn = Carbon::parse($validated['clock_in']);
        $clockOut = Carbon::parse($validated['clock_out']);
        $totalHours = round($clockIn->diffInMinutes($clockOut) / 60, 2);

        $log->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_hours' => $totalHours,
            'remarks' => $validated['remarks'],
            'regularized_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance regularized successfully.',
            'log' => $log->fresh(),
        ]);
    }

    /**
     * Get today's attendance status for current user.
     */
    public function todayStatus()
    {
        abort_unless(auth()->check(), 401);

        $org = auth()->user()->currentOrganization();
        $profile = EmployeeProfile::where('user_id', auth()->id())
            ->where('organization_id', $org->id)
            ->first();

        if (!$profile) {
            return response()->json([
                'success' => true,
                'log' => null,
                'is_clocked_in' => false,
            ]);
        }

        $log = HrAttendanceLog::where('employee_profile_id', $profile->id)
            ->where('organization_id', $org->id)
            ->where('date', Carbon::today())
            ->first();

        $isClockedIn = $log && $log->clock_in && !$log->clock_out;

        return response()->json([
            'success' => true,
            'log' => $log,
            'is_clocked_in' => $isClockedIn,
        ]);
    }
}
