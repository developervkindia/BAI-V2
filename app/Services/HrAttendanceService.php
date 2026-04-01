<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\HrAttendanceLog;
use App\Models\HrAttendancePolicy;
use App\Models\HrShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HrAttendanceService
{
    /**
     * Determine the attendance status for a given log entry.
     *
     * Checks grace_minutes, half_day_after_minutes, absent_after_minutes from the policy.
     * If no clock_in, returns 'absent'. If late beyond grace, returns 'late'.
     * If total hours < half_day threshold, returns 'half_day'.
     */
    public function calculateDayStatus(HrAttendanceLog $log, ?HrShift $shift, ?HrAttendancePolicy $policy): string
    {
        // No clock in means absent
        if (!$log->clock_in) {
            return 'absent';
        }

        // If no policy or shift, we can only confirm presence
        if (!$policy || !$shift) {
            return 'present';
        }

        // Calculate how many minutes late the employee clocked in relative to the shift start
        $shiftStart = Carbon::parse($log->date->format('Y-m-d') . ' ' . $shift->start_time);
        $clockIn = Carbon::parse($log->clock_in);
        $lateMinutes = max(0, $clockIn->diffInMinutes($shiftStart, false) * -1);

        // If late beyond the absent threshold, mark absent
        if ($policy->absent_after_minutes && $lateMinutes >= $policy->absent_after_minutes) {
            return 'absent';
        }

        // Check total hours against half-day threshold
        $totalHours = $log->total_hours ?? 0;
        if ($policy->half_day_after_minutes) {
            $halfDayThresholdHours = $policy->half_day_after_minutes / 60;
            if ($totalHours > 0 && $totalHours < $halfDayThresholdHours) {
                return 'half_day';
            }
        }

        // Check if late beyond grace period
        $graceMinutes = $shift->grace_minutes ?? ($policy->late_mark_after_minutes ?? 0);
        if ($lateMinutes > $graceMinutes && $graceMinutes > 0) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Create or update an attendance log for the employee on the given date.
     */
    public function markAttendance(EmployeeProfile $employee, Carbon $date, array $data): HrAttendanceLog
    {
        $attributes = array_merge($data, [
            'organization_id' => $employee->organization_id,
            'employee_profile_id' => $employee->id,
            'date' => $date->toDateString(),
        ]);

        // Calculate total hours if both clock_in and clock_out are provided
        if (isset($attributes['clock_in']) && isset($attributes['clock_out'])) {
            $clockIn = Carbon::parse($attributes['clock_in']);
            $clockOut = Carbon::parse($attributes['clock_out']);
            $attributes['total_hours'] = round($clockOut->diffInMinutes($clockIn) / 60, 2);
        }

        return HrAttendanceLog::updateOrCreate(
            [
                'employee_profile_id' => $employee->id,
                'date' => $date->toDateString(),
            ],
            $attributes
        );
    }

    /**
     * Calculate overtime hours for the given log entry.
     *
     * If total_hours exceeds shift duration + overtime_threshold_minutes from policy,
     * the excess is overtime.
     */
    public function calculateOvertime(HrAttendanceLog $log, ?HrShift $shift, ?HrAttendancePolicy $policy): float
    {
        if (!$shift || !$policy || !$log->total_hours) {
            return 0.0;
        }

        // Calculate shift duration in hours
        $shiftStart = Carbon::parse($shift->start_time);
        $shiftEnd = Carbon::parse($shift->end_time);

        // Handle night shifts where end time is next day
        if ($shift->is_night_shift || $shiftEnd->lte($shiftStart)) {
            $shiftEnd->addDay();
        }

        $shiftDurationMinutes = $shiftStart->diffInMinutes($shiftEnd);

        // Subtract break duration if applicable
        if ($shift->break_duration_minutes) {
            $shiftDurationMinutes -= $shift->break_duration_minutes;
        }

        $shiftDurationHours = $shiftDurationMinutes / 60;

        // Add overtime threshold
        $overtimeThresholdHours = ($policy->overtime_threshold_minutes ?? 0) / 60;
        $totalThreshold = $shiftDurationHours + $overtimeThresholdHours;

        // Overtime is the excess beyond the threshold
        $overtime = $log->total_hours - $totalThreshold;

        return max(0.0, round($overtime, 2));
    }

    /**
     * Return all attendance logs for the employee in the given month.
     */
    public function getMonthlyAttendance(EmployeeProfile $employee, int $month, int $year): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return HrAttendanceLog::where('employee_profile_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get();
    }

    /**
     * Get attendance grid for all direct reports of a manager for the month.
     *
     * Returns an array keyed by employee_profile_id, each containing the employee
     * profile and their attendance logs indexed by date.
     */
    public function getMonthlyTeamAttendance(User $manager, int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Get all direct reports of this manager
        $directReports = EmployeeProfile::where('reporting_manager_id', $manager->id)
            ->where('status', 'active')
            ->get();

        if ($directReports->isEmpty()) {
            return [];
        }

        $employeeIds = $directReports->pluck('id')->toArray();

        // Fetch all attendance logs for these employees in the month
        $logs = HrAttendanceLog::whereIn('employee_profile_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date')
            ->get()
            ->groupBy('employee_profile_id');

        $result = [];
        foreach ($directReports as $employee) {
            $employeeLogs = $logs->get($employee->id, collect());

            $result[$employee->id] = [
                'employee' => $employee,
                'attendance' => $employeeLogs->keyBy(fn ($log) => $log->date->format('Y-m-d')),
                'summary' => [
                    'present' => $employeeLogs->where('status', 'present')->count(),
                    'absent' => $employeeLogs->where('status', 'absent')->count(),
                    'late' => $employeeLogs->where('status', 'late')->count(),
                    'half_day' => $employeeLogs->where('status', 'half_day')->count(),
                    'total_hours' => round($employeeLogs->sum('total_hours'), 2),
                    'overtime_hours' => round($employeeLogs->sum('overtime_hours'), 2),
                ],
            ];
        }

        return $result;
    }
}
