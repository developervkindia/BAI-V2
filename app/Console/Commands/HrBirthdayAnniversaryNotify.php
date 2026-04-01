<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;
use App\Models\EmployeeProfile;
use App\Models\Notification;

class HrBirthdayAnniversaryNotify extends Command
{
    protected $signature = 'hr:birthday-anniversary-notify {--org= : Organization ID (optional, run for all if omitted)}';
    protected $description = 'Send notifications for upcoming birthdays and work anniversaries';

    public function handle(): int
    {
        $orgId = $this->option('org');
        $organizations = $orgId
            ? Organization::where('id', $orgId)->active()->get()
            : Organization::active()->get();

        if ($organizations->isEmpty()) {
            $this->warn('No active organizations found.');
            return Command::FAILURE;
        }

        $totalNotifications = 0;

        foreach ($organizations as $org) {
            $birthdayCount = $this->notifyBirthdays($org);
            $anniversaryCount = $this->notifyAnniversaries($org);
            $orgTotal = $birthdayCount + $anniversaryCount;
            $totalNotifications += $orgTotal;
            $this->info("Organization [{$org->name}]: {$birthdayCount} birthday + {$anniversaryCount} anniversary notification(s) sent.");
        }

        $this->newLine();
        $this->info("Done. Total notifications sent: {$totalNotifications}");

        return Command::SUCCESS;
    }

    private function notifyBirthdays(Organization $org): int
    {
        $today = now();
        $count = 0;

        // Find employees whose birthday is today (match month and day)
        $employees = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->with('user')
            ->get();

        if ($employees->isEmpty()) {
            return 0;
        }

        // Get all active employees in the org to notify them
        $allActiveEmployees = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        foreach ($employees as $birthdayEmployee) {
            $employeeName = $birthdayEmployee->user?->name ?? 'An employee';

            // Notify all other active employees about this birthday
            foreach ($allActiveEmployees as $recipient) {
                if ($recipient->user_id === $birthdayEmployee->user_id) {
                    // Send a personal birthday wish to the birthday person
                    Notification::create([
                        'user_id' => $recipient->user_id,
                        'type' => 'hr_birthday',
                        'title' => 'Happy Birthday!',
                        'body' => "Wishing you a wonderful birthday, {$employeeName}!",
                        'data' => [
                            'employee_profile_id' => $birthdayEmployee->id,
                            'organization_id' => $org->id,
                            'event_type' => 'birthday_self',
                        ],
                        'notifiable_type' => EmployeeProfile::class,
                        'notifiable_id' => $birthdayEmployee->id,
                        'created_at' => now(),
                    ]);
                } else {
                    Notification::create([
                        'user_id' => $recipient->user_id,
                        'type' => 'hr_birthday',
                        'title' => "Birthday: {$employeeName}",
                        'body' => "Today is {$employeeName}'s birthday! Wish them a happy birthday.",
                        'data' => [
                            'employee_profile_id' => $birthdayEmployee->id,
                            'organization_id' => $org->id,
                            'event_type' => 'birthday_colleague',
                        ],
                        'notifiable_type' => EmployeeProfile::class,
                        'notifiable_id' => $birthdayEmployee->id,
                        'created_at' => now(),
                    ]);
                }

                $count++;
            }
        }

        return $count;
    }

    private function notifyAnniversaries(Organization $org): int
    {
        $today = now();
        $count = 0;

        // Find employees whose work anniversary is today (match month and day, but not the joining year itself)
        $employees = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->whereNotNull('date_of_joining')
            ->whereMonth('date_of_joining', $today->month)
            ->whereDay('date_of_joining', $today->day)
            ->whereYear('date_of_joining', '<', $today->year)
            ->with('user')
            ->get();

        if ($employees->isEmpty()) {
            return 0;
        }

        // Get all active employees in the org to notify them
        $allActiveEmployees = EmployeeProfile::where('organization_id', $org->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        foreach ($employees as $anniversaryEmployee) {
            $employeeName = $anniversaryEmployee->user?->name ?? 'An employee';
            $yearsCompleted = $today->year - $anniversaryEmployee->date_of_joining->year;
            $yearLabel = $yearsCompleted === 1 ? 'year' : 'years';

            foreach ($allActiveEmployees as $recipient) {
                if ($recipient->user_id === $anniversaryEmployee->user_id) {
                    // Personal anniversary notification
                    Notification::create([
                        'user_id' => $recipient->user_id,
                        'type' => 'hr_work_anniversary',
                        'title' => 'Happy Work Anniversary!',
                        'body' => "Congratulations on completing {$yearsCompleted} {$yearLabel} with us!",
                        'data' => [
                            'employee_profile_id' => $anniversaryEmployee->id,
                            'organization_id' => $org->id,
                            'event_type' => 'anniversary_self',
                            'years_completed' => $yearsCompleted,
                        ],
                        'notifiable_type' => EmployeeProfile::class,
                        'notifiable_id' => $anniversaryEmployee->id,
                        'created_at' => now(),
                    ]);
                } else {
                    Notification::create([
                        'user_id' => $recipient->user_id,
                        'type' => 'hr_work_anniversary',
                        'title' => "Work Anniversary: {$employeeName}",
                        'body' => "{$employeeName} has completed {$yearsCompleted} {$yearLabel} with us today!",
                        'data' => [
                            'employee_profile_id' => $anniversaryEmployee->id,
                            'organization_id' => $org->id,
                            'event_type' => 'anniversary_colleague',
                            'years_completed' => $yearsCompleted,
                        ],
                        'notifiable_type' => EmployeeProfile::class,
                        'notifiable_id' => $anniversaryEmployee->id,
                        'created_at' => now(),
                    ]);
                }

                $count++;
            }
        }

        return $count;
    }
}
