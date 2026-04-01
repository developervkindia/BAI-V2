<?php

namespace App\Services;

use App\Models\EmployeeProfile;
use App\Models\HrDepartment;
use App\Models\HrExit;
use App\Models\HrLeaveRequest;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HrDashboardService
{
    /**
     * Return headcount statistics for the organization.
     *
     * @return array{total: int, active: int, inactive: int, on_leave: int}
     */
    public function getHeadcountStats(Organization $org): array
    {
        $total = EmployeeProfile::where('organization_id', $org->id)->count();
        $active = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->count();
        $inactive = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', '!=', 'active')
            ->count();

        // Count employees currently on approved leave
        $today = Carbon::today();
        $onLeave = HrLeaveRequest::where('organization_id', $org->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today->toDateString())
            ->where('end_date', '>=', $today->toDateString())
            ->distinct('employee_profile_id')
            ->count('employee_profile_id');

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'on_leave' => $onLeave,
        ];
    }

    /**
     * Return department names with employee counts.
     */
    public function getDepartmentBreakdown(Organization $org): Collection
    {
        return HrDepartment::where('hr_departments.organization_id', $org->id)
            ->where('hr_departments.is_active', true)
            ->withCount(['employees' => function ($query) {
                $query->where('status', 'active');
            }])
            ->orderByDesc('employees_count')
            ->get(['id', 'name', 'code']);
    }

    /**
     * Return employees who joined in the last N days.
     */
    public function getNewJoiners(Organization $org, int $days = 30): Collection
    {
        $cutoffDate = Carbon::today()->subDays($days);

        return EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->where('date_of_joining', '>=', $cutoffDate->toDateString())
            ->with('user:id,name,email')
            ->orderByDesc('date_of_joining')
            ->get();
    }

    /**
     * Return the most recent exits with employee profile.
     */
    public function getRecentExits(Organization $org, int $limit = 5): Collection
    {
        return HrExit::where('organization_id', $org->id)
            ->with('employeeProfile.user:id,name,email')
            ->orderByDesc('last_working_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Return employees with birthdays in the next N days.
     */
    public function getUpcomingBirthdays(Organization $org, int $days = 7): Collection
    {
        $today = Carbon::today();

        return EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->with('user:id,name,email')
            ->get()
            ->filter(function ($employee) use ($today, $days) {
                $birthday = $employee->date_of_birth->copy()->year($today->year);

                // If the birthday has already passed this year, check next year
                if ($birthday->lt($today)) {
                    $birthday->addYear();
                }

                return $birthday->diffInDays($today) <= $days;
            })
            ->sortBy(function ($employee) use ($today) {
                $birthday = $employee->date_of_birth->copy()->year($today->year);
                if ($birthday->lt($today)) {
                    $birthday->addYear();
                }
                return $birthday;
            })
            ->values();
    }

    /**
     * Return employees with work anniversaries in the next N days.
     */
    public function getUpcomingAnniversaries(Organization $org, int $days = 7): Collection
    {
        $today = Carbon::today();

        return EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_joining')
            ->with('user:id,name,email')
            ->get()
            ->filter(function ($employee) use ($today, $days) {
                // Skip employees who joined this year (no anniversary yet)
                if ($employee->date_of_joining->year >= $today->year) {
                    return false;
                }

                $anniversary = $employee->date_of_joining->copy()->year($today->year);

                // If the anniversary has already passed this year, check next year
                if ($anniversary->lt($today)) {
                    $anniversary->addYear();
                }

                return $anniversary->diffInDays($today) <= $days;
            })
            ->sortBy(function ($employee) use ($today) {
                $anniversary = $employee->date_of_joining->copy()->year($today->year);
                if ($anniversary->lt($today)) {
                    $anniversary->addYear();
                }
                return $anniversary;
            })
            ->values();
    }
}
