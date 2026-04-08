<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ScreenshotTest extends DuskTestCase
{
    public function test_capture_all_screenshots(): void
    {
        $user = User::find(2); // test@bai.com - owner of org with seeded data

        $pages = [
            'hub' => '/hub',
            'hr-dashboard' => '/hr',
            'hr-people' => '/hr/people',
            'hr-attendance' => '/hr/attendance/my',
            'hr-leave' => '/hr/leave',
            'hr-leave-apply' => '/hr/leave/apply',
            'hr-payroll' => '/hr/payroll',
            'hr-salary-structures' => '/hr/payroll/salary-structures',
            'hr-performance' => '/hr/performance',
            'hr-recruitment' => '/hr/recruitment',
            'hr-engagement' => '/hr/engagement',
            'hr-announcements' => '/hr/announcements',
            'hr-surveys' => '/hr/surveys',
            'hr-departments' => '/hr/departments',
            'hr-org-chart' => '/hr/people/org-chart',
            'hr-expenses' => '/hr/expenses',
        ];

        $this->browse(function (Browser $browser) use ($user, $pages) {
            foreach ($pages as $name => $path) {
                $browser->loginAs($user)
                    ->visit($path)
                    ->pause(2000)
                    ->screenshot($name);
            }
        });

        // Copy screenshots to public/screenshots
        $screenshotDir = base_path('tests/Browser/screenshots');
        $publicDir = public_path('screenshots');

        foreach ($pages as $name => $path) {
            $src = "$screenshotDir/$name.png";
            $dest = "$publicDir/$name.png";
            if (file_exists($src)) {
                copy($src, $dest);
            }
        }

        $this->assertTrue(true);
    }
}
