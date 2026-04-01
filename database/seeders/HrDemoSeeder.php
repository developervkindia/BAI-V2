<?php

namespace Database\Seeders;

use App\Models\EmployeeProfile;
use App\Models\HrAnnouncement;
use App\Models\HrAttendanceLog;
use App\Models\HrCandidate;
use App\Models\HrDepartment;
use App\Models\HrDesignation;
use App\Models\HrExpenseCategory;
use App\Models\HrExpenseClaim;
use App\Models\HrExpenseItem;
use App\Models\HrJobPosting;
use App\Models\HrLeaveBalance;
use App\Models\HrLeaveRequest;
use App\Models\HrLeaveType;
use App\Models\HrRecognition;
use App\Models\HrReview;
use App\Models\HrReviewCycle;
use App\Models\HrSalaryComponent;
use App\Models\HrSalaryStructure;
use App\Models\HrSalaryStructureComponent;
use App\Models\HrSurvey;
use App\Models\HrSurveyQuestion;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HrDemoSeeder extends Seeder
{
    private Organization $org;
    private array $users = [];
    private array $profiles = [];
    private array $departments = [];
    private array $designations = [];
    private array $leaveTypes = [];
    private array $salaryComponents = [];
    private array $expenseCategories = [];

    public function run(): void
    {
        $this->org = Organization::firstOrFail();
        $owner = User::find($this->org->owner_id);

        $this->command->info('Creating HR Demo Data...');

        // ═══════════════════════════════════════════════════════════════
        // 1. CREATE ALL USERS & ATTACH TO ORGANIZATION
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating users...');
        $this->seedUsers($owner);

        // ═══════════════════════════════════════════════════════════════
        // 2. CREATE HR DEPARTMENTS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating departments...');
        $this->seedDepartments();

        // ═══════════════════════════════════════════════════════════════
        // 3. CREATE HR DESIGNATIONS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating designations...');
        $this->seedDesignations();

        // ═══════════════════════════════════════════════════════════════
        // 4. CREATE EMPLOYEE PROFILES (linked to departments/designations)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating employee profiles...');
        $this->seedEmployeeProfiles($owner);

        // ═══════════════════════════════════════════════════════════════
        // 5. CREATE LEAVE TYPES
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating leave types...');
        $this->seedLeaveTypes();

        // ═══════════════════════════════════════════════════════════════
        // 6. CREATE LEAVE BALANCES FOR ALL EMPLOYEES
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating leave balances...');
        $this->seedLeaveBalances();

        // ═══════════════════════════════════════════════════════════════
        // 7. CREATE SALARY COMPONENTS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating salary components...');
        $this->seedSalaryComponents();

        // ═══════════════════════════════════════════════════════════════
        // 8. CREATE SALARY STRUCTURES FOR ALL EMPLOYEES
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating salary structures...');
        $this->seedSalaryStructures();

        // ═══════════════════════════════════════════════════════════════
        // 9. CREATE ATTENDANCE LOGS (last 30 days)
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating attendance logs...');
        $this->seedAttendanceLogs();

        // ═══════════════════════════════════════════════════════════════
        // 10. CREATE LEAVE REQUESTS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating leave requests...');
        $this->seedLeaveRequests();

        // ═══════════════════════════════════════════════════════════════
        // 11. CREATE ANNOUNCEMENTS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating announcements...');
        $this->seedAnnouncements();

        // ═══════════════════════════════════════════════════════════════
        // 12. CREATE RECOGNITIONS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating recognitions...');
        $this->seedRecognitions();

        // ═══════════════════════════════════════════════════════════════
        // 13. CREATE JOB POSTINGS & CANDIDATES
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating job postings & candidates...');
        $this->seedJobPostings();

        // ═══════════════════════════════════════════════════════════════
        // 14. CREATE REVIEW CYCLE & REVIEWS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating review cycle & reviews...');
        $this->seedReviewCycle();

        // ═══════════════════════════════════════════════════════════════
        // 15. CREATE EXPENSE CATEGORIES & CLAIMS
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating expense claims...');
        $this->seedExpenseClaims();

        // ═══════════════════════════════════════════════════════════════
        // 16. CREATE SURVEY
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('  → Creating survey...');
        $this->seedSurvey();

        $this->command->info('HR Demo Data seeded successfully!');
    }

    // ─────────────────────────────────────────────────────────────────
    // USERS
    // ─────────────────────────────────────────────────────────────────
    private function seedUsers(User $owner): void
    {
        $teamData = [
            // Delivery Managers (admin)
            ['name' => 'Vikas Kumawat',      'email' => 'vikas@company.test',            'role' => 'admin',  'dept' => 'Management',        'designation' => 'Senior Delivery Manager', 'gender' => 'male'],
            ['name' => 'Varun Garg',          'email' => 'varun@company.test',            'role' => 'admin',  'dept' => 'Management',        'designation' => 'Delivery Manager',        'gender' => 'male'],
            ['name' => 'Rohit Panchal',       'email' => 'rohit@company.test',            'role' => 'admin',  'dept' => 'Management',        'designation' => 'Delivery Manager',        'gender' => 'male'],

            // Business Analysts
            ['name' => 'Akash Sharma',        'email' => 'akash.sharma@company.test',     'role' => 'member', 'dept' => 'Business Analysis', 'designation' => 'Senior Business Analyst', 'gender' => 'male'],
            ['name' => 'Ayush Singh',         'email' => 'ayush.singh@company.test',      'role' => 'member', 'dept' => 'Business Analysis', 'designation' => 'Business Analyst',        'gender' => 'male'],
            ['name' => 'Lakshami Thakur',     'email' => 'lakshami.thakur@company.test',  'role' => 'member', 'dept' => 'Business Analysis', 'designation' => 'Business Analyst',        'gender' => 'female'],

            // Figma Designers
            ['name' => 'Abhishek Kumar',      'email' => 'abhishek.kumar@company.test',   'role' => 'member', 'dept' => 'Design',            'designation' => 'Senior UI/UX Designer',   'gender' => 'male'],
            ['name' => 'Rekha Bhartaj',       'email' => 'rekha.bhartaj@company.test',    'role' => 'member', 'dept' => 'Design',            'designation' => 'UI/UX Designer',          'gender' => 'female'],
            ['name' => 'Harshit Panesar',     'email' => 'harshit.panesar@company.test',  'role' => 'member', 'dept' => 'Design',            'designation' => 'UI Designer',             'gender' => 'male'],

            // Front-end Designers
            ['name' => 'Monik Verma',         'email' => 'monik.verma@company.test',      'role' => 'member', 'dept' => 'Design',            'designation' => 'Senior Frontend Designer','gender' => 'male'],
            ['name' => 'Deepak Joshi',        'email' => 'deepak.joshi@company.test',     'role' => 'member', 'dept' => 'Design',            'designation' => 'Frontend Designer',       'gender' => 'male'],
            ['name' => 'Surkhab Arora',       'email' => 'surkhab.arora@company.test',    'role' => 'member', 'dept' => 'Design',            'designation' => 'Frontend Designer',       'gender' => 'male'],
            ['name' => 'Rishav Koundal',      'email' => 'rishav.koundal@company.test',   'role' => 'member', 'dept' => 'Design',            'designation' => 'Frontend Designer',       'gender' => 'male'],

            // Front-end Developers
            ['name' => 'Tejender Verma',      'email' => 'tejender.verma@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Senior Frontend Developer','gender' => 'male'],
            ['name' => 'Nishant Sharma',      'email' => 'nishant.sharma@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Ankit Dhiman',        'email' => 'ankit.dhiman@company.test',     'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Prabhjot Singh',      'email' => 'prabhjot.singh@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Lakhveer Singh',      'email' => 'lakhveer.singh@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Shubham Dhiman',      'email' => 'shubham.dhiman@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Priyanka Sharma',     'email' => 'priyanka.sharma@company.test',  'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'female'],
            ['name' => 'Gaurav Mittal',       'email' => 'gaurav.mittal@company.test',    'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Sukhwinder Singh',    'email' => 'sukhwinder.singh@company.test', 'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Parambir Singh',      'email' => 'parambir.singh@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Senior Frontend Developer','gender' => 'male'],
            ['name' => 'Vikram Binda',        'email' => 'vikram.binda@company.test',     'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Mansi Singh',         'email' => 'mansi.singh@company.test',      'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'female'],
            ['name' => 'Karan Pathania',      'email' => 'karan.pathania@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Harjeet Singh',       'email' => 'harjeet.singh@company.test',    'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Anju Kumari',         'email' => 'anju.kumari@company.test',      'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'female'],
            ['name' => 'Ravinder Singh',      'email' => 'ravinder.singh@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],
            ['name' => 'Surendra Maurya',     'email' => 'surendra.maurya@company.test',  'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Frontend Developer',      'gender' => 'male'],

            // Back-end Developers
            ['name' => 'Dilkash Singh',       'email' => 'dilkash.singh@company.test',    'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Backend Developer',       'gender' => 'male'],
            ['name' => 'Neha Shandilya',      'email' => 'neha.shandilya@company.test',   'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Backend Developer',       'gender' => 'female'],
            ['name' => 'Aman Kumar',          'email' => 'aman.kumar@company.test',       'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Backend Developer',       'gender' => 'male'],
            ['name' => 'Rishabh Dwivedi',     'email' => 'rishabh.dwivedi@company.test',  'role' => 'member', 'dept' => 'Engineering',       'designation' => 'Backend Developer',       'gender' => 'male'],

            // App Developers
            ['name' => 'Pankaj Singh',        'email' => 'pankaj.singh@company.test',     'role' => 'member', 'dept' => 'Mobile',            'designation' => 'Senior Mobile Developer', 'gender' => 'male'],
            ['name' => 'Gaurav Singh',        'email' => 'gaurav.singh@company.test',     'role' => 'member', 'dept' => 'Mobile',            'designation' => 'Mobile Developer',        'gender' => 'male'],
            ['name' => 'Aman Yadav',          'email' => 'aman.yadav@company.test',       'role' => 'member', 'dept' => 'Mobile',            'designation' => 'Mobile Developer',        'gender' => 'male'],
            ['name' => 'Harshita Rawat',      'email' => 'harshita.rawat@company.test',   'role' => 'member', 'dept' => 'Mobile',            'designation' => 'Mobile Developer',        'gender' => 'female'],
            ['name' => 'Aniket Kumar',        'email' => 'aniket.kumar@company.test',     'role' => 'member', 'dept' => 'Mobile',            'designation' => 'Mobile Developer',        'gender' => 'male'],
            ['name' => 'Gaurav Dhiman',       'email' => 'gaurav.dhiman@company.test',    'role' => 'member', 'dept' => 'Mobile',            'designation' => 'Mobile Developer',        'gender' => 'male'],
            ['name' => 'Prabhjot Kaur',       'email' => 'prabhjot.kaur@company.test',    'role' => 'member', 'dept' => 'Mobile',            'designation' => 'Mobile Developer',        'gender' => 'female'],

            // Testers
            ['name' => 'Tannu Jaglan',        'email' => 'tannu.jaglan@company.test',     'role' => 'member', 'dept' => 'QA',                'designation' => 'Senior QA Engineer',      'gender' => 'female'],
            ['name' => 'Khushbu Dogra',       'email' => 'khushbu.dogra@company.test',    'role' => 'member', 'dept' => 'QA',                'designation' => 'QA Engineer',             'gender' => 'female'],
            ['name' => 'Ramandeep Kaur',      'email' => 'ramandeep.kaur@company.test',   'role' => 'member', 'dept' => 'QA',                'designation' => 'QA Engineer',             'gender' => 'female'],
            ['name' => 'Sneha Kumari',        'email' => 'sneha.kumari@company.test',     'role' => 'member', 'dept' => 'QA',                'designation' => 'QA Engineer',             'gender' => 'female'],
            ['name' => 'Puneet Kumar',        'email' => 'puneet.kumar@company.test',     'role' => 'member', 'dept' => 'QA',                'designation' => 'QA Engineer',             'gender' => 'male'],
            ['name' => 'Sidharath Kumar',     'email' => 'sidharath.kumar@company.test',  'role' => 'member', 'dept' => 'QA',                'designation' => 'QA Engineer',             'gender' => 'male'],
        ];

        foreach ($teamData as $td) {
            $user = User::firstOrCreate(
                ['email' => $td['email']],
                ['name' => $td['name'], 'password' => Hash::make('password')]
            );

            if (!$this->org->members()->where('user_id', $user->id)->exists()) {
                $this->org->members()->attach($user->id, ['role' => $td['role']]);
            }

            $this->users[$td['email']] = [
                'user'        => $user,
                'dept'        => $td['dept'],
                'designation' => $td['designation'],
                'gender'      => $td['gender'],
                'role'        => $td['role'],
            ];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // DEPARTMENTS
    // ─────────────────────────────────────────────────────────────────
    private function seedDepartments(): void
    {
        $deptData = [
            ['name' => 'Management',        'code' => 'MGMT', 'head_email' => 'vikas@company.test',          'description' => 'Delivery management and leadership'],
            ['name' => 'Business Analysis',  'code' => 'BA',   'head_email' => 'akash.sharma@company.test',  'description' => 'Requirements gathering and business analysis'],
            ['name' => 'Design',             'code' => 'DES',  'head_email' => 'abhishek.kumar@company.test','description' => 'UI/UX design and frontend design'],
            ['name' => 'Engineering',        'code' => 'ENG',  'head_email' => 'tejender.verma@company.test','description' => 'Frontend and backend software development'],
            ['name' => 'Mobile',             'code' => 'MOB',  'head_email' => 'pankaj.singh@company.test',  'description' => 'Mobile application development'],
            ['name' => 'QA',                 'code' => 'QA',   'head_email' => 'tannu.jaglan@company.test',  'description' => 'Quality assurance and testing'],
        ];

        foreach ($deptData as $dd) {
            $headUser = $this->users[$dd['head_email']]['user'];
            $dept = HrDepartment::firstOrCreate(
                ['organization_id' => $this->org->id, 'code' => $dd['code']],
                [
                    'name'        => $dd['name'],
                    'head_id'     => $headUser->id,
                    'description' => $dd['description'],
                    'is_active'   => true,
                ]
            );
            $this->departments[$dd['name']] = $dept;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // DESIGNATIONS
    // ─────────────────────────────────────────────────────────────────
    private function seedDesignations(): void
    {
        $desigData = [
            // Management
            ['name' => 'Senior Delivery Manager', 'level' => 8, 'dept' => 'Management'],
            ['name' => 'Delivery Manager',         'level' => 7, 'dept' => 'Management'],

            // Business Analysis
            ['name' => 'Senior Business Analyst',  'level' => 6, 'dept' => 'Business Analysis'],
            ['name' => 'Business Analyst',          'level' => 5, 'dept' => 'Business Analysis'],

            // Design
            ['name' => 'Senior UI/UX Designer',    'level' => 6, 'dept' => 'Design'],
            ['name' => 'UI/UX Designer',            'level' => 5, 'dept' => 'Design'],
            ['name' => 'UI Designer',               'level' => 4, 'dept' => 'Design'],
            ['name' => 'Senior Frontend Designer',  'level' => 6, 'dept' => 'Design'],
            ['name' => 'Frontend Designer',          'level' => 5, 'dept' => 'Design'],

            // Engineering
            ['name' => 'Senior Frontend Developer', 'level' => 6, 'dept' => 'Engineering'],
            ['name' => 'Frontend Developer',         'level' => 5, 'dept' => 'Engineering'],
            ['name' => 'Backend Developer',          'level' => 5, 'dept' => 'Engineering'],

            // Mobile
            ['name' => 'Senior Mobile Developer',  'level' => 6, 'dept' => 'Mobile'],
            ['name' => 'Mobile Developer',          'level' => 5, 'dept' => 'Mobile'],

            // QA
            ['name' => 'Senior QA Engineer',        'level' => 6, 'dept' => 'QA'],
            ['name' => 'QA Engineer',                'level' => 5, 'dept' => 'QA'],
        ];

        foreach ($desigData as $dd) {
            $dept = $this->departments[$dd['dept']];
            $desig = HrDesignation::firstOrCreate(
                ['organization_id' => $this->org->id, 'name' => $dd['name'], 'hr_department_id' => $dept->id],
                [
                    'level'       => $dd['level'],
                    'description' => $dd['name'] . ' in ' . $dd['dept'],
                ]
            );
            $this->designations[$dd['name'] . '|' . $dd['dept']] = $desig;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // EMPLOYEE PROFILES
    // ─────────────────────────────────────────────────────────────────
    private function seedEmployeeProfiles(User $owner): void
    {
        $vikas = $this->users['vikas@company.test']['user'];
        $bloodGroups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-'];
        $banks = ['HDFC Bank', 'ICICI Bank', 'SBI', 'Axis Bank', 'Kotak Mahindra', 'PNB'];
        $locations = ['Chandigarh Office', 'Remote', 'Hybrid'];
        $idx = 0;

        foreach ($this->users as $email => $data) {
            $user = $data['user'];
            $deptName = $data['dept'];
            $desigName = $data['designation'];
            $gender = $data['gender'];
            $role = $data['role'];
            $idx++;

            // Determine reporting manager
            if ($role === 'admin') {
                $reportingManagerId = $owner->id;
            } else {
                $reportingManagerId = $vikas->id;
            }

            // Find department and designation
            $dept = $this->departments[$deptName] ?? null;
            $desigKey = $desigName . '|' . $deptName;
            $desig = $this->designations[$desigKey] ?? null;

            $profile = EmployeeProfile::firstOrCreate(
                ['organization_id' => $this->org->id, 'user_id' => $user->id],
                [
                    'employee_id'            => 'BAI-' . str_pad($idx, 3, '0', STR_PAD_LEFT),
                    'designation'            => $desigName,
                    'department'             => $deptName,
                    'date_of_joining'        => Carbon::create(2026, 1, 1)->subMonths(rand(6, 48))->subDays(rand(0, 28)),
                    'employment_type'        => 'full_time',
                    'reporting_manager_id'   => $reportingManagerId,
                    'work_location'          => $locations[array_rand($locations)],
                    'shift'                  => 'General (9:30AM-6:30PM)',
                    'phone'                  => '+91 ' . rand(70000, 99999) . rand(10000, 99999),
                    'date_of_birth'          => Carbon::create(2026, 1, 1)->subYears(rand(23, 38))->subDays(rand(0, 365)),
                    'gender'                 => $gender,
                    'blood_group'            => $bloodGroups[array_rand($bloodGroups)],
                    'nationality'            => 'Indian',
                    'emergency_contact_name' => 'Emergency Contact',
                    'emergency_contact_phone'=> '+91 ' . rand(70000, 99999) . rand(10000, 99999),
                    'current_address'        => rand(1, 500) . ', Sector ' . rand(1, 56) . ', Chandigarh',
                    'bank_name'              => $banks[array_rand($banks)],
                    'ifsc_code'              => 'HDFC000' . rand(1000, 9999),
                    'status'                 => 'active',
                ]
            );

            // Link HR department and designation via direct DB update (columns may not be in fillable)
            if ($dept) {
                DB::table('employee_profiles')
                    ->where('id', $profile->id)
                    ->update([
                        'hr_department_id'  => $dept->id,
                        'hr_designation_id' => $desig ? $desig->id : null,
                    ]);
            }

            $this->profiles[$email] = $profile;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // LEAVE TYPES
    // ─────────────────────────────────────────────────────────────────
    private function seedLeaveTypes(): void
    {
        $types = [
            [
                'name'               => 'Casual Leave',
                'code'               => 'CL',
                'color'              => '#3B82F6',
                'is_paid'            => true,
                'max_days_per_year'  => 12,
                'accrual_type'       => 'monthly',
                'accrual_count'      => 1,
                'carry_forward_limit'=> 0,
                'encashable'         => false,
                'requires_approval'  => true,
                'min_days'           => 0.5,
                'max_consecutive_days'=> 3,
                'sandwich_policy'    => false,
            ],
            [
                'name'               => 'Sick Leave',
                'code'               => 'SL',
                'color'              => '#EF4444',
                'is_paid'            => true,
                'max_days_per_year'  => 8,
                'accrual_type'       => 'annual',
                'accrual_count'      => 8,
                'carry_forward_limit'=> 0,
                'encashable'         => false,
                'requires_approval'  => true,
                'min_days'           => 0.5,
                'max_consecutive_days'=> 5,
                'sandwich_policy'    => false,
            ],
            [
                'name'               => 'Earned Leave',
                'code'               => 'EL',
                'color'              => '#10B981',
                'is_paid'            => true,
                'max_days_per_year'  => 15,
                'accrual_type'       => 'monthly',
                'accrual_count'      => 1.25,
                'carry_forward_limit'=> 30,
                'encashable'         => true,
                'requires_approval'  => true,
                'min_days'           => 1,
                'max_consecutive_days'=> 10,
                'sandwich_policy'    => true,
            ],
            [
                'name'               => 'Work From Home',
                'code'               => 'WFH',
                'color'              => '#8B5CF6',
                'is_paid'            => true,
                'max_days_per_year'  => 24,
                'accrual_type'       => 'none',
                'accrual_count'      => 0,
                'carry_forward_limit'=> 0,
                'encashable'         => false,
                'requires_approval'  => true,
                'min_days'           => 1,
                'max_consecutive_days'=> 5,
                'sandwich_policy'    => false,
            ],
            [
                'name'               => 'Compensatory Off',
                'code'               => 'CO',
                'color'              => '#F59E0B',
                'is_paid'            => true,
                'max_days_per_year'  => 0,
                'accrual_type'       => 'none',
                'accrual_count'      => 0,
                'carry_forward_limit'=> 0,
                'encashable'         => false,
                'requires_approval'  => true,
                'min_days'           => 1,
                'max_consecutive_days'=> 1,
                'sandwich_policy'    => false,
            ],
        ];

        foreach ($types as $t) {
            $lt = HrLeaveType::firstOrCreate(
                ['organization_id' => $this->org->id, 'code' => $t['code']],
                array_merge($t, ['organization_id' => $this->org->id, 'is_active' => true])
            );
            $this->leaveTypes[$t['code']] = $lt;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // LEAVE BALANCES
    // ─────────────────────────────────────────────────────────────────
    private function seedLeaveBalances(): void
    {
        // 3 months into 2026 (Q1 complete), so accrued amounts reflect that
        $balanceDefaults = [
            'CL'  => ['opening' => 0, 'accrued' => 3, 'used' => 1, 'available' => 2],
            'SL'  => ['opening' => 0, 'accrued' => 8, 'used' => 0, 'available' => 8],
            'EL'  => ['opening' => 5, 'accrued' => 3.75, 'used' => 0, 'available' => 8.75],
            'WFH' => ['opening' => 24, 'accrued' => 0, 'used' => 3, 'available' => 21],
            'CO'  => ['opening' => 0, 'accrued' => 0, 'used' => 0, 'available' => 0],
        ];

        foreach ($this->profiles as $email => $profile) {
            foreach ($this->leaveTypes as $code => $leaveType) {
                $defaults = $balanceDefaults[$code];
                // Add some randomness per employee
                $usedVariation = rand(0, 2);
                $used = $defaults['used'] + $usedVariation;
                $available = $defaults['accrued'] + $defaults['opening'] - $used;
                if ($available < 0) {
                    $available = 0;
                    $used = $defaults['accrued'] + $defaults['opening'];
                }

                HrLeaveBalance::firstOrCreate(
                    [
                        'organization_id'    => $this->org->id,
                        'employee_profile_id'=> $profile->id,
                        'hr_leave_type_id'   => $leaveType->id,
                        'year'               => 2026,
                    ],
                    [
                        'opening_balance'  => $defaults['opening'],
                        'accrued'          => $defaults['accrued'],
                        'used'             => $used,
                        'adjusted'         => 0,
                        'carried_forward'  => $defaults['opening'],
                        'encashed'         => 0,
                        'available'        => $available,
                    ]
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // SALARY COMPONENTS
    // ─────────────────────────────────────────────────────────────────
    private function seedSalaryComponents(): void
    {
        $components = [
            ['name' => 'Basic Salary',       'code' => 'BASIC',  'type' => 'earning',   'calculation_type' => 'fixed',      'percentage_of' => null,    'is_taxable' => true,  'is_statutory' => true,  'sort_order' => 1],
            ['name' => 'HRA',                'code' => 'HRA',    'type' => 'earning',   'calculation_type' => 'percentage', 'percentage_of' => 'BASIC', 'is_taxable' => true,  'is_statutory' => false, 'sort_order' => 2],
            ['name' => 'Special Allowance',  'code' => 'SA',     'type' => 'earning',   'calculation_type' => 'fixed',      'percentage_of' => null,    'is_taxable' => true,  'is_statutory' => false, 'sort_order' => 3],
            ['name' => 'PF Employee',        'code' => 'PF_EMP', 'type' => 'deduction', 'calculation_type' => 'percentage', 'percentage_of' => 'BASIC', 'is_taxable' => false, 'is_statutory' => true,  'sort_order' => 4],
            ['name' => 'Professional Tax',   'code' => 'PT',     'type' => 'deduction', 'calculation_type' => 'fixed',      'percentage_of' => null,    'is_taxable' => false, 'is_statutory' => true,  'sort_order' => 5],
        ];

        foreach ($components as $c) {
            $comp = HrSalaryComponent::firstOrCreate(
                ['organization_id' => $this->org->id, 'code' => $c['code']],
                array_merge($c, ['organization_id' => $this->org->id, 'is_active' => true])
            );
            $this->salaryComponents[$c['code']] = $comp;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // SALARY STRUCTURES
    // ─────────────────────────────────────────────────────────────────
    private function seedSalaryStructures(): void
    {
        // CTC ranges by designation keyword
        $ctcRanges = [
            'Senior Delivery Manager' => [1500000, 1800000],
            'Delivery Manager'        => [1200000, 1500000],
            'Senior Business Analyst' => [700000, 800000],
            'Business Analyst'        => [600000, 700000],
            'Senior UI/UX Designer'   => [700000, 800000],
            'UI/UX Designer'          => [550000, 650000],
            'UI Designer'             => [500000, 600000],
            'Senior Frontend Designer'=> [700000, 800000],
            'Frontend Designer'       => [500000, 600000],
            'Senior Frontend Developer'=> [900000, 1200000],
            'Frontend Developer'      => [500000, 800000],
            'Backend Developer'       => [600000, 800000],
            'Senior Mobile Developer' => [800000, 1000000],
            'Mobile Developer'        => [400000, 600000],
            'Senior QA Engineer'      => [600000, 700000],
            'QA Engineer'             => [400000, 550000],
        ];

        foreach ($this->profiles as $email => $profile) {
            $desigName = $this->users[$email]['designation'];
            $range = $ctcRanges[$desigName] ?? [400000, 600000];
            // Pick a random CTC within range, rounded to nearest 10000
            $ctc = round(rand($range[0], $range[1]) / 10000) * 10000;

            $structure = HrSalaryStructure::firstOrCreate(
                ['organization_id' => $this->org->id, 'employee_profile_id' => $profile->id, 'is_current' => true],
                [
                    'annual_ctc'     => $ctc,
                    'effective_from' => Carbon::create(2026, 1, 1),
                    'effective_until'=> null,
                ]
            );

            // Calculate component breakdown
            $monthlyCTC = $ctc / 12;
            $basicMonthly = round($monthlyCTC * 0.40, 2);   // 40% of CTC
            $hraMonthly = round($basicMonthly * 0.50, 2);    // 50% of basic
            $pfMonthly = round($basicMonthly * 0.12, 2);     // 12% of basic
            $ptMonthly = 200;                                  // Fixed professional tax
            $saMonthly = round($monthlyCTC - $basicMonthly - $hraMonthly, 2); // Remainder as special allowance

            $componentBreakdown = [
                'BASIC'  => $basicMonthly,
                'HRA'    => $hraMonthly,
                'SA'     => $saMonthly,
                'PF_EMP' => $pfMonthly,
                'PT'     => $ptMonthly,
            ];

            foreach ($componentBreakdown as $code => $monthlyAmount) {
                if (!isset($this->salaryComponents[$code])) {
                    continue;
                }
                HrSalaryStructureComponent::firstOrCreate(
                    [
                        'hr_salary_structure_id' => $structure->id,
                        'hr_salary_component_id' => $this->salaryComponents[$code]->id,
                    ],
                    [
                        'monthly_amount' => round($monthlyAmount, 2),
                        'annual_amount'  => round($monthlyAmount * 12, 2),
                    ]
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // ATTENDANCE LOGS (last 30 days)
    // ─────────────────────────────────────────────────────────────────
    private function seedAttendanceLogs(): void
    {
        $today = Carbon::create(2026, 3, 31);
        $startDate = $today->copy()->subDays(29);
        $profileList = array_values($this->profiles);

        for ($date = $startDate->copy(); $date->lte($today); $date->addDay()) {
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($profileList as $profile) {
                // 5% chance of absence (no log at all for that day)
                if (rand(1, 100) <= 5) {
                    continue;
                }

                // Determine clock-in time (9:00 to 10:30 range)
                $clockInHour = 9;
                $clockInMinute = rand(0, 59);
                $isLate = false;

                // 10% chance of being late (after 10:00)
                if (rand(1, 100) <= 10) {
                    $clockInHour = 10;
                    $clockInMinute = rand(0, 30);
                    $isLate = true;
                }

                $clockIn = $date->copy()->setTime($clockInHour, $clockInMinute, 0);
                // Work 8.5-9.5 hours
                $workMinutes = rand(510, 570); // 8.5 to 9.5 hours in minutes
                $clockOut = $clockIn->copy()->addMinutes($workMinutes);
                $totalHours = round($workMinutes / 60, 2);
                $overtime = $totalHours > 9 ? round($totalHours - 9, 2) : 0;

                $status = 'present';
                if ($isLate) {
                    $status = 'late';
                }

                HrAttendanceLog::firstOrCreate(
                    [
                        'organization_id'     => $this->org->id,
                        'employee_profile_id' => $profile->id,
                        'date'                => $date->toDateString(),
                    ],
                    [
                        'clock_in'       => $clockIn,
                        'clock_out'      => $clockOut,
                        'total_hours'    => $totalHours,
                        'overtime_hours' => $overtime,
                        'status'         => $status,
                        'source'         => 'web',
                    ]
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // LEAVE REQUESTS
    // ─────────────────────────────────────────────────────────────────
    private function seedLeaveRequests(): void
    {
        $vikas = $this->users['vikas@company.test']['user'];
        $profileEmails = array_keys($this->profiles);
        $clType = $this->leaveTypes['CL'];
        $slType = $this->leaveTypes['SL'];
        $elType = $this->leaveTypes['EL'];
        $wfhType = $this->leaveTypes['WFH'];

        $leaveRequests = [
            // Approved casual leaves
            ['email' => 'nishant.sharma@company.test',   'type' => $clType, 'start' => '2026-03-10', 'end' => '2026-03-10', 'days' => 1, 'status' => 'approved', 'reason' => 'Personal work - need to visit bank'],
            ['email' => 'priyanka.sharma@company.test',  'type' => $clType, 'start' => '2026-03-14', 'end' => '2026-03-14', 'days' => 1, 'status' => 'approved', 'reason' => 'Family function at hometown'],
            ['email' => 'gaurav.mittal@company.test',    'type' => $clType, 'start' => '2026-03-20', 'end' => '2026-03-21', 'days' => 2, 'status' => 'approved', 'reason' => 'Attending a wedding ceremony'],
            ['email' => 'rekha.bhartaj@company.test',    'type' => $clType, 'start' => '2026-03-25', 'end' => '2026-03-25', 'days' => 1, 'status' => 'approved', 'reason' => 'Doctor appointment for routine checkup'],

            // Approved sick leaves
            ['email' => 'dilkash.singh@company.test',    'type' => $slType, 'start' => '2026-03-05', 'end' => '2026-03-06', 'days' => 2, 'status' => 'approved', 'reason' => 'Fever and cold, need rest'],
            ['email' => 'sneha.kumari@company.test',     'type' => $slType, 'start' => '2026-03-17', 'end' => '2026-03-17', 'days' => 1, 'status' => 'approved', 'reason' => 'Migraine, unable to work'],

            // Approved WFH
            ['email' => 'aman.kumar@company.test',       'type' => $wfhType, 'start' => '2026-03-24', 'end' => '2026-03-24', 'days' => 1, 'status' => 'approved', 'reason' => 'Waiting for internet installation at new apartment'],
            ['email' => 'pankaj.singh@company.test',     'type' => $wfhType, 'start' => '2026-03-26', 'end' => '2026-03-27', 'days' => 2, 'status' => 'approved', 'reason' => 'Working from home due to heavy rain'],
            ['email' => 'tannu.jaglan@company.test',     'type' => $wfhType, 'start' => '2026-03-12', 'end' => '2026-03-12', 'days' => 1, 'status' => 'approved', 'reason' => 'Plumber coming to fix kitchen pipes'],

            // Pending requests
            ['email' => 'harshit.panesar@company.test',  'type' => $clType, 'start' => '2026-04-07', 'end' => '2026-04-08', 'days' => 2, 'status' => 'pending',  'reason' => 'Going to hometown for family function'],
            ['email' => 'aman.yadav@company.test',       'type' => $elType, 'start' => '2026-04-14', 'end' => '2026-04-18', 'days' => 5, 'status' => 'pending',  'reason' => 'Planned vacation trip to Himachal Pradesh'],
            ['email' => 'rishabh.dwivedi@company.test',  'type' => $clType, 'start' => '2026-04-03', 'end' => '2026-04-03', 'days' => 1, 'status' => 'pending',  'reason' => 'Need to submit documents at passport office'],
            ['email' => 'gaurav.singh@company.test',     'type' => $wfhType, 'start' => '2026-04-01', 'end' => '2026-04-02', 'days' => 2, 'status' => 'pending',  'reason' => 'Furniture delivery expected at home'],

            // Rejected requests
            ['email' => 'karan.pathania@company.test',   'type' => $elType, 'start' => '2026-03-03', 'end' => '2026-03-07', 'days' => 5, 'status' => 'rejected', 'reason' => 'Planning to visit family in hometown', 'rejection_reason' => 'Critical sprint delivery scheduled for this week. Please reschedule.'],
            ['email' => 'prabhjot.kaur@company.test',    'type' => $clType, 'start' => '2026-03-19', 'end' => '2026-03-21', 'days' => 3, 'status' => 'rejected', 'reason' => 'Personal trip to Amritsar',           'rejection_reason' => 'Too many team members already on leave during this period.'],

            // Approved earned leave
            ['email' => 'monik.verma@company.test',      'type' => $elType, 'start' => '2026-03-03', 'end' => '2026-03-05', 'days' => 3, 'status' => 'approved', 'reason' => 'Short vacation to refresh and recharge'],
            ['email' => 'ayush.singh@company.test',      'type' => $elType, 'start' => '2026-03-17', 'end' => '2026-03-19', 'days' => 3, 'status' => 'approved', 'reason' => 'Family event - brother wedding preparations'],

            // Half-day leave
            ['email' => 'lakshami.thakur@company.test',  'type' => $clType, 'start' => '2026-03-28', 'end' => '2026-03-28', 'days' => 0.5, 'status' => 'approved', 'reason' => 'Half day - dentist appointment in afternoon', 'is_half_day' => true, 'half_day_period' => 'second_half'],
        ];

        foreach ($leaveRequests as $lr) {
            $profile = $this->profiles[$lr['email']] ?? null;
            if (!$profile) {
                continue;
            }

            $data = [
                'organization_id'     => $this->org->id,
                'employee_profile_id' => $profile->id,
                'hr_leave_type_id'    => $lr['type']->id,
                'start_date'          => $lr['start'],
                'end_date'            => $lr['end'],
                'days'                => $lr['days'],
                'is_half_day'         => $lr['is_half_day'] ?? false,
                'half_day_period'     => $lr['half_day_period'] ?? null,
                'reason'              => $lr['reason'],
                'status'              => $lr['status'],
                'applied_at'          => Carbon::parse($lr['start'])->subDays(rand(2, 7)),
            ];

            if ($lr['status'] === 'approved') {
                $data['approved_by'] = $vikas->id;
                $data['actioned_at'] = Carbon::parse($lr['start'])->subDays(rand(1, 3));
            } elseif ($lr['status'] === 'rejected') {
                $data['rejected_by'] = $vikas->id;
                $data['rejection_reason'] = $lr['rejection_reason'] ?? 'Cannot approve at this time.';
                $data['actioned_at'] = Carbon::parse($lr['start'])->subDays(rand(1, 3));
            }

            HrLeaveRequest::firstOrCreate(
                [
                    'organization_id'     => $this->org->id,
                    'employee_profile_id' => $profile->id,
                    'start_date'          => $lr['start'],
                    'end_date'            => $lr['end'],
                    'hr_leave_type_id'    => $lr['type']->id,
                ],
                $data
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // ANNOUNCEMENTS
    // ─────────────────────────────────────────────────────────────────
    private function seedAnnouncements(): void
    {
        $vikas = $this->users['vikas@company.test']['user'];

        $announcements = [
            [
                'title'        => 'Welcome to Q2 2026',
                'body'         => "Dear Team,\n\nAs we step into Q2 2026, I want to take a moment to acknowledge the incredible work everyone has put in during Q1. We delivered 3 major projects on time and our client satisfaction scores are at an all-time high.\n\nLet's keep the momentum going. Q2 brings exciting new challenges with the BAI platform expansion and two new client onboardings.\n\nKey focus areas for Q2:\n- Complete the BAI HR module rollout\n- Onboard 5 new team members\n- Improve our sprint velocity by 15%\n\nLooking forward to another great quarter together!\n\nBest regards,\nVikas Kumawat",
                'type'         => 'general',
                'is_pinned'    => true,
                'published_at' => Carbon::create(2026, 4, 1, 9, 0),
                'expires_at'   => Carbon::create(2026, 4, 30, 23, 59),
            ],
            [
                'title'        => 'Updated Leave Policy - Effective April 2026',
                'body'         => "Team,\n\nPlease note the following updates to our leave policy effective April 1, 2026:\n\n1. Work From Home (WFH) increased from 20 to 24 days per year\n2. Earned Leave carry forward limit increased to 30 days\n3. Comp-off must be availed within 30 days of accrual\n4. Half-day leave option now available for Casual and Sick leave\n5. Leave cancellation must be done 24 hours before the leave date\n\nPlease review the full policy document in the HR portal. Reach out to the HR team for any questions.\n\nRegards,\nHR Team",
                'type'         => 'policy',
                'is_pinned'    => false,
                'published_at' => Carbon::create(2026, 3, 25, 10, 0),
                'expires_at'   => Carbon::create(2026, 6, 30, 23, 59),
            ],
            [
                'title'        => 'Annual Day Celebration 2026 - Save the Date!',
                'body'         => "Hello Everyone!\n\nWe are thrilled to announce our Annual Day Celebration 2026!\n\nDate: Saturday, April 18, 2026\nTime: 5:00 PM onwards\nVenue: Hotel Lalit, Chandigarh\n\nHighlights:\n- Team performances and cultural events\n- Annual awards ceremony\n- DJ night and dinner\n- Best dressed competition\n\nPlease confirm your attendance and plus-one by April 10. Registration link will be shared on Slack.\n\nLet's celebrate our achievements together!\n\nCheers,\nEvents Committee",
                'type'         => 'event',
                'is_pinned'    => false,
                'published_at' => Carbon::create(2026, 3, 20, 11, 30),
                'expires_at'   => Carbon::create(2026, 4, 18, 23, 59),
            ],
            [
                'title'        => 'Employee Referral Bonus Updated - Earn Up to 50K!',
                'body'         => "Great news, team!\n\nWe have updated our Employee Referral Bonus program. Here are the new referral amounts:\n\n- Senior Developer / Lead: Rs. 50,000\n- Mid-level Developer / Designer: Rs. 30,000\n- Junior Developer / QA: Rs. 20,000\n- Intern: Rs. 10,000\n\nBonus payout schedule:\n- 50% after the referred candidate completes 3 months\n- 50% after the referred candidate completes 6 months\n\nWe have multiple open positions across Engineering, QA, and Design teams. Check the Careers section for current openings.\n\nRefer talented friends and earn while strengthening our team!\n\nHR Team",
                'type'         => 'policy',
                'is_pinned'    => false,
                'published_at' => Carbon::create(2026, 3, 15, 14, 0),
                'expires_at'   => Carbon::create(2026, 12, 31, 23, 59),
            ],
            [
                'title'        => 'Office Closed - Good Friday (April 3, 2026)',
                'body'         => "Dear All,\n\nPlease note that the office will remain closed on Friday, April 3, 2026, on account of Good Friday.\n\nRegular working hours will resume on Monday, April 6, 2026.\n\nFor any urgent matters, please reach out to your respective Delivery Managers.\n\nEnjoy the long weekend!\n\nRegards,\nAdmin Team",
                'type'         => 'general',
                'is_pinned'    => false,
                'published_at' => Carbon::create(2026, 3, 28, 16, 0),
                'expires_at'   => Carbon::create(2026, 4, 6, 9, 0),
            ],
        ];

        foreach ($announcements as $a) {
            HrAnnouncement::firstOrCreate(
                ['organization_id' => $this->org->id, 'title' => $a['title']],
                array_merge($a, [
                    'organization_id'    => $this->org->id,
                    'target_departments' => null,
                    'created_by'         => $vikas->id,
                ])
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // RECOGNITIONS
    // ─────────────────────────────────────────────────────────────────
    private function seedRecognitions(): void
    {
        $vikas = $this->users['vikas@company.test']['user'];
        $varun = $this->users['varun@company.test']['user'];
        $rohit = $this->users['rohit@company.test']['user'];
        $tejender = $this->users['tejender.verma@company.test']['user'];
        $akash = $this->users['akash.sharma@company.test']['user'];
        $tannu = $this->users['tannu.jaglan@company.test']['user'];

        $recognitions = [
            [
                'profile_email' => 'tejender.verma@company.test',
                'recognized_by' => $vikas->id,
                'type'          => 'award',
                'title'         => 'Outstanding Sprint Delivery',
                'description'   => 'Tejender led the frontend team through a challenging sprint and delivered all 28 story points on time with zero bugs. His code reviews were thorough and mentoring of junior devs was exceptional.',
                'badge_icon'    => 'star',
            ],
            [
                'profile_email' => 'tannu.jaglan@company.test',
                'recognized_by' => $varun->id,
                'type'          => 'badge',
                'title'         => 'Bug-Free Release Champion',
                'description'   => 'Tannu caught 15 critical bugs in the pre-release testing cycle that would have impacted production. Her test automation scripts saved the team 20+ hours of manual testing.',
                'badge_icon'    => 'shield',
            ],
            [
                'profile_email' => 'abhishek.kumar@company.test',
                'recognized_by' => $vikas->id,
                'type'          => 'shoutout',
                'title'         => 'Design System Excellence',
                'description'   => 'Abhishek created a comprehensive design system that reduced our design-to-development handoff time by 40%. The component library is now used across all our projects.',
                'badge_icon'    => 'lightbulb',
            ],
            [
                'profile_email' => 'pankaj.singh@company.test',
                'recognized_by' => $rohit->id,
                'type'          => 'badge',
                'title'         => 'Cross-Team Collaboration',
                'description'   => 'Pankaj went above and beyond to help the backend team resolve critical API integration issues for the mobile app. His proactive approach saved the project timeline by a week.',
                'badge_icon'    => 'users',
            ],
            [
                'profile_email' => 'akash.sharma@company.test',
                'recognized_by' => $vikas->id,
                'type'          => 'award',
                'title'         => 'Client Requirements Masterclass',
                'description'   => 'Akash conducted an exceptional requirements workshop with the new client, producing detailed user stories and acceptance criteria that significantly reduced development rework.',
                'badge_icon'    => 'star',
            ],
            [
                'profile_email' => 'neha.shandilya@company.test',
                'recognized_by' => $tejender->id,
                'type'          => 'award',
                'title'         => 'Rapid Growth in Backend Skills',
                'description'   => 'Neha has shown remarkable growth in her backend development skills over the past quarter. She independently built the payroll calculation engine with complex tax rules.',
                'badge_icon'    => 'trending-up',
            ],
            [
                'profile_email' => 'monik.verma@company.test',
                'recognized_by' => $akash->id,
                'type'          => 'shoutout',
                'title'         => 'Creative Frontend Solutions',
                'description'   => 'Monik developed reusable animation components that enhanced our product UX significantly. His CSS-only approach reduced page load time by 30%.',
                'badge_icon'    => 'lightbulb',
            ],
            [
                'profile_email' => 'ramandeep.kaur@company.test',
                'recognized_by' => $tannu->id,
                'type'          => 'badge',
                'title'         => 'QA Process Improvement',
                'description'   => 'Ramandeep streamlined our regression testing process and created detailed test documentation that reduced onboarding time for new QA team members from 2 weeks to 3 days.',
                'badge_icon'    => 'users',
            ],
        ];

        foreach ($recognitions as $r) {
            $profile = $this->profiles[$r['profile_email']] ?? null;
            if (!$profile) {
                continue;
            }

            HrRecognition::firstOrCreate(
                [
                    'organization_id'     => $this->org->id,
                    'employee_profile_id' => $profile->id,
                    'title'               => $r['title'],
                ],
                [
                    'recognized_by' => $r['recognized_by'],
                    'type'          => $r['type'],
                    'description'   => $r['description'],
                    'badge_icon'    => $r['badge_icon'],
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // JOB POSTINGS & CANDIDATES
    // ─────────────────────────────────────────────────────────────────
    private function seedJobPostings(): void
    {
        $vikas = $this->users['vikas@company.test']['user'];
        $engDept = $this->departments['Engineering'];
        $qaDept = $this->departments['QA'];
        $desDept = $this->departments['Design'];

        // Find designations for postings
        $srFeDev = $this->designations['Senior Frontend Developer|Engineering'] ?? null;
        $qaEng = $this->designations['QA Engineer|QA'] ?? null;
        $uiuxDes = $this->designations['UI/UX Designer|Design'] ?? null;

        $postings = [
            [
                'title'            => 'Senior React Developer',
                'hr_department_id' => $engDept->id,
                'hr_designation_id'=> $srFeDev ? $srFeDev->id : null,
                'description'      => "We are looking for an experienced Senior React Developer to join our growing Engineering team. You will be responsible for building and maintaining complex web applications using React, TypeScript, and modern frontend technologies.\n\nResponsibilities:\n- Architect and build scalable React applications\n- Conduct code reviews and mentor junior developers\n- Collaborate with design and backend teams\n- Write unit and integration tests\n- Optimize application performance",
                'requirements'     => "Required Skills:\n- 4+ years of experience with React.js\n- Strong proficiency in TypeScript\n- Experience with state management (Redux, Zustand, or similar)\n- Familiarity with RESTful APIs and GraphQL\n- Experience with testing frameworks (Jest, React Testing Library)\n- Good understanding of CI/CD pipelines\n\nNice to Have:\n- Experience with Next.js\n- Knowledge of TailwindCSS\n- Contribution to open-source projects",
                'employment_type'  => 'full_time',
                'location'         => 'Chandigarh / Remote',
                'salary_range_min' => 1000000,
                'salary_range_max' => 1500000,
                'positions'        => 2,
                'status'           => 'open',
            ],
            [
                'title'            => 'QA Automation Engineer',
                'hr_department_id' => $qaDept->id,
                'hr_designation_id'=> $qaEng ? $qaEng->id : null,
                'description'      => "Join our QA team as an Automation Engineer. You will be responsible for designing and implementing test automation frameworks to ensure the quality of our products.\n\nResponsibilities:\n- Design and maintain automation test frameworks\n- Write automated test scripts for web and mobile applications\n- Perform regression, smoke, and integration testing\n- Report and track bugs using project management tools\n- Collaborate with development teams on quality improvements",
                'requirements'     => "Required Skills:\n- 2+ years of experience in QA automation\n- Proficiency in Selenium, Cypress, or Playwright\n- Experience with API testing (Postman, REST Assured)\n- Knowledge of at least one programming language (JavaScript, Python, Java)\n- Understanding of Agile/Scrum methodologies\n\nNice to Have:\n- Experience with mobile testing (Appium)\n- Performance testing experience (JMeter, k6)\n- ISTQB certification",
                'employment_type'  => 'full_time',
                'location'         => 'Chandigarh',
                'salary_range_min' => 500000,
                'salary_range_max' => 800000,
                'positions'        => 1,
                'status'           => 'open',
            ],
            [
                'title'            => 'UI/UX Designer',
                'hr_department_id' => $desDept->id,
                'hr_designation_id'=> $uiuxDes ? $uiuxDes->id : null,
                'description'      => "We are seeking a talented UI/UX Designer to create intuitive and visually appealing interfaces for our web and mobile products.\n\nResponsibilities:\n- Create wireframes, prototypes, and high-fidelity mockups\n- Conduct user research and usability testing\n- Develop and maintain design systems\n- Collaborate closely with developers and product managers\n- Create responsive designs for web and mobile platforms",
                'requirements'     => "Required Skills:\n- 2+ years of experience in UI/UX design\n- Proficiency in Figma (primary tool)\n- Strong portfolio showcasing web/mobile design work\n- Understanding of design systems and component libraries\n- Knowledge of responsive design principles\n- Basic understanding of HTML/CSS\n\nNice to Have:\n- Experience with motion design (After Effects, Lottie)\n- Illustration skills\n- Experience with design tokens",
                'employment_type'  => 'full_time',
                'location'         => 'Chandigarh / Hybrid',
                'salary_range_min' => 500000,
                'salary_range_max' => 800000,
                'positions'        => 1,
                'status'           => 'open',
            ],
        ];

        $jobPostingModels = [];

        foreach ($postings as $p) {
            $posting = HrJobPosting::firstOrCreate(
                ['organization_id' => $this->org->id, 'title' => $p['title']],
                array_merge($p, [
                    'organization_id' => $this->org->id,
                    'posted_by'       => $vikas->id,
                    'posted_at'       => Carbon::create(2026, 3, 15, 10, 0),
                ])
            );
            $jobPostingModels[] = $posting;
        }

        // Candidates for the postings
        $candidates = [
            // Candidates for Senior React Developer
            [
                'posting_idx'         => 0,
                'name'                => 'Rahul Mehta',
                'email'               => 'rahul.mehta@gmail.com',
                'phone'               => '+91 98765 43210',
                'source'              => 'portal',
                'stage'               => 'interview',
                'current_company'     => 'Infosys',
                'current_designation' => 'Senior Software Engineer',
                'experience_years'    => 5.5,
                'expected_ctc'        => 1400000,
                'notes'               => 'Strong React and TypeScript skills. Cleared technical round with good feedback.',
            ],
            [
                'posting_idx'         => 0,
                'name'                => 'Anita Desai',
                'email'               => 'anita.desai@outlook.com',
                'phone'               => '+91 87654 32109',
                'source'              => 'referral',
                'stage'               => 'screening',
                'current_company'     => 'TCS',
                'current_designation' => 'React Developer',
                'experience_years'    => 4,
                'expected_ctc'        => 1200000,
                'notes'               => 'Referred by Tejender. Good portfolio, scheduling phone screen.',
            ],

            // Candidates for QA Automation Engineer
            [
                'posting_idx'         => 1,
                'name'                => 'Deepika Rathi',
                'email'               => 'deepika.rathi@yahoo.com',
                'phone'               => '+91 76543 21098',
                'source'              => 'portal',
                'stage'               => 'offer',
                'current_company'     => 'Wipro',
                'current_designation' => 'QA Analyst',
                'experience_years'    => 3,
                'expected_ctc'        => 700000,
                'notes'               => 'Excellent automation skills with Cypress and Playwright. Offer sent, awaiting response.',
            ],
            [
                'posting_idx'         => 1,
                'name'                => 'Mohit Kapoor',
                'email'               => 'mohit.kapoor@gmail.com',
                'phone'               => '+91 65432 10987',
                'source'              => 'portal',
                'stage'               => 'rejected',
                'current_company'     => 'Accenture',
                'current_designation' => 'Test Engineer',
                'experience_years'    => 2,
                'expected_ctc'        => 650000,
                'notes'               => 'Good manual testing skills but automation experience was insufficient for the role.',
            ],

            // Candidates for UI/UX Designer
            [
                'posting_idx'         => 2,
                'name'                => 'Kavya Nair',
                'email'               => 'kavya.nair@gmail.com',
                'phone'               => '+91 54321 09876',
                'source'              => 'portal',
                'stage'               => 'interview',
                'current_company'     => 'Zomato',
                'current_designation' => 'Product Designer',
                'experience_years'    => 3.5,
                'expected_ctc'        => 750000,
                'notes'               => 'Beautiful portfolio with strong Figma skills. Cleared design challenge round.',
            ],
            [
                'posting_idx'         => 2,
                'name'                => 'Saurabh Tiwari',
                'email'               => 'saurabh.tiwari@gmail.com',
                'phone'               => '+91 43210 98765',
                'source'              => 'referral',
                'stage'               => 'applied',
                'current_company'     => 'Freelance',
                'current_designation' => 'UI Designer',
                'experience_years'    => 2,
                'expected_ctc'        => 550000,
                'notes'               => 'Referred by Abhishek. Freelance background with diverse portfolio. Resume under review.',
            ],
        ];

        foreach ($candidates as $c) {
            $posting = $jobPostingModels[$c['posting_idx']];
            $referredBy = null;
            if ($c['source'] === 'referral') {
                if ($c['posting_idx'] === 0) {
                    $referredBy = $this->users['tejender.verma@company.test']['user']->id;
                } elseif ($c['posting_idx'] === 2) {
                    $referredBy = $this->users['abhishek.kumar@company.test']['user']->id;
                }
            }

            HrCandidate::firstOrCreate(
                ['organization_id' => $this->org->id, 'email' => $c['email']],
                [
                    'hr_job_posting_id'   => $posting->id,
                    'name'                => $c['name'],
                    'phone'               => $c['phone'],
                    'source'              => $c['source'],
                    'stage'               => $c['stage'],
                    'current_company'     => $c['current_company'],
                    'current_designation' => $c['current_designation'],
                    'experience_years'    => $c['experience_years'],
                    'expected_ctc'        => $c['expected_ctc'],
                    'notes'               => $c['notes'],
                    'referred_by'         => $referredBy,
                ]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // REVIEW CYCLE & REVIEWS
    // ─────────────────────────────────────────────────────────────────
    private function seedReviewCycle(): void
    {
        $vikas = $this->users['vikas@company.test']['user'];

        $cycle = HrReviewCycle::firstOrCreate(
            ['organization_id' => $this->org->id, 'name' => 'Q1 2026 Performance Review'],
            [
                'type'                    => 'quarterly',
                'start_date'              => Carbon::create(2026, 3, 25),
                'end_date'                => Carbon::create(2026, 4, 15),
                'self_review_deadline'    => Carbon::create(2026, 4, 5),
                'manager_review_deadline' => Carbon::create(2026, 4, 15),
                'status'                  => 'active',
                'created_by'              => $vikas->id,
            ]
        );

        // Create self-review records for first 10 employees
        $profileEmails = array_keys($this->profiles);
        $reviewEmployees = array_slice($profileEmails, 0, 10);

        $strengths = [
            'Strong technical skills and problem-solving abilities. Consistently delivers high-quality code with good test coverage.',
            'Excellent communication and collaboration. Always willing to help teammates and share knowledge.',
            'Great attention to detail in design work. User-centric approach leads to intuitive interfaces.',
            'Proactive in identifying and resolving issues before they become blockers. Good debugging skills.',
            'Fast learner who adapts quickly to new technologies. Shows genuine curiosity and initiative.',
            'Reliable and consistent delivery. Manages time effectively across multiple tasks.',
            'Strong analytical thinking. Breaks down complex requirements into clear, actionable items.',
            'Good mentoring skills. Helps junior team members grow and improve their skills.',
            'Creative problem solver who finds elegant solutions to complex challenges.',
            'Excellent at requirements gathering and stakeholder communication.',
        ];

        $improvements = [
            'Could improve documentation practices. Writing more detailed comments in complex code sections would help team productivity.',
            'Should work on time estimation skills. Tasks sometimes take longer than initially estimated.',
            'Could benefit from deeper understanding of backend systems to improve full-stack collaboration.',
            'Need to be more proactive in sprint planning discussions and raise blockers earlier.',
            'Should focus on writing more comprehensive unit tests for edge cases.',
            'Could improve presentation skills for client-facing meetings.',
            'Should explore new tools and frameworks to stay current with industry trends.',
            'Need to delegate more effectively instead of trying to handle everything personally.',
            'Could benefit from learning more about accessibility standards in design.',
            'Should focus on improving code review turnaround time.',
        ];

        $statuses = ['submitted', 'submitted', 'submitted', 'submitted', 'submitted', 'submitted', 'in_progress', 'in_progress', 'pending', 'pending'];
        $ratings = [4.2, 3.8, 4.5, 3.5, 4.0, 4.3, 3.7, 4.1, 0, 0];

        foreach ($reviewEmployees as $i => $email) {
            $profile = $this->profiles[$email];
            $user = $this->users[$email]['user'];
            $status = $statuses[$i];
            $rating = $ratings[$i];

            $reviewData = [
                'review_type'    => 'self',
                'status'         => $status,
            ];

            if ($status === 'submitted') {
                $reviewData['overall_rating'] = $rating;
                $reviewData['strengths'] = $strengths[$i];
                $reviewData['improvements'] = $improvements[$i];
                $reviewData['comments'] = 'Overall it was a productive quarter. I focused on improving my skills and contributing to team goals.';
                $reviewData['submitted_at'] = Carbon::create(2026, 3, 28)->addHours(rand(9, 18));
            } elseif ($status === 'in_progress') {
                $reviewData['overall_rating'] = $rating;
                $reviewData['strengths'] = $strengths[$i];
                $reviewData['improvements'] = null;
                $reviewData['comments'] = null;
            }

            HrReview::firstOrCreate(
                [
                    'hr_review_cycle_id'  => $cycle->id,
                    'employee_profile_id' => $profile->id,
                    'reviewer_id'         => $user->id,
                    'review_type'         => 'self',
                ],
                $reviewData
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // EXPENSE CATEGORIES & CLAIMS
    // ─────────────────────────────────────────────────────────────────
    private function seedExpenseClaims(): void
    {
        $vikas = $this->users['vikas@company.test']['user'];

        // Create expense categories
        $categories = [
            ['name' => 'Travel',             'max_amount' => 50000, 'requires_receipt' => true],
            ['name' => 'Food & Beverages',   'max_amount' => 5000,  'requires_receipt' => true],
            ['name' => 'Office Supplies',    'max_amount' => 10000, 'requires_receipt' => true],
            ['name' => 'Communication',      'max_amount' => 3000,  'requires_receipt' => false],
            ['name' => 'Training & Courses', 'max_amount' => 25000, 'requires_receipt' => true],
        ];

        foreach ($categories as $cat) {
            $catModel = HrExpenseCategory::firstOrCreate(
                ['organization_id' => $this->org->id, 'name' => $cat['name']],
                array_merge($cat, ['organization_id' => $this->org->id, 'is_active' => true])
            );
            $this->expenseCategories[$cat['name']] = $catModel;
        }

        $travelCat = $this->expenseCategories['Travel'];
        $foodCat = $this->expenseCategories['Food & Beverages'];
        $trainingCat = $this->expenseCategories['Training & Courses'];
        $commCat = $this->expenseCategories['Communication'];

        // Create 5 expense claims
        $claims = [
            [
                'email'        => 'akash.sharma@company.test',
                'title'        => 'Client Visit - Delhi (March 2026)',
                'total_amount' => 12500,
                'status'       => 'approved',
                'submitted_at' => Carbon::create(2026, 3, 15, 11, 0),
                'approved_at'  => Carbon::create(2026, 3, 17, 14, 30),
                'items'        => [
                    ['category' => 'Travel',           'description' => 'Chandigarh to Delhi Shatabdi train tickets (round trip)', 'amount' => 3200, 'date' => '2026-03-10'],
                    ['category' => 'Travel',           'description' => 'Uber rides for local travel in Delhi (3 rides)',          'amount' => 1800, 'date' => '2026-03-11'],
                    ['category' => 'Food & Beverages', 'description' => 'Lunch with client at Connaught Place restaurant',        'amount' => 2500, 'date' => '2026-03-11'],
                    ['category' => 'Travel',           'description' => 'Hotel stay - 1 night at Novotel Aerocity',               'amount' => 5000, 'date' => '2026-03-11'],
                ],
            ],
            [
                'email'        => 'tejender.verma@company.test',
                'title'        => 'Team Lunch - Sprint Completion Celebration',
                'total_amount' => 4500,
                'status'       => 'approved',
                'submitted_at' => Carbon::create(2026, 3, 22, 16, 0),
                'approved_at'  => Carbon::create(2026, 3, 23, 10, 0),
                'items'        => [
                    ['category' => 'Food & Beverages', 'description' => 'Team lunch at Pizza Hut for 8 people (sprint completion)', 'amount' => 4500, 'date' => '2026-03-20'],
                ],
            ],
            [
                'email'        => 'pankaj.singh@company.test',
                'title'        => 'Mobile Testing Devices & Accessories',
                'total_amount' => 8900,
                'status'       => 'submitted',
                'submitted_at' => Carbon::create(2026, 3, 28, 14, 0),
                'items'        => [
                    ['category' => 'Training & Courses', 'description' => 'Flutter Advanced Course on Udemy',                'amount' => 2900, 'date' => '2026-03-25'],
                    ['category' => 'Communication',      'description' => 'Mobile recharge for testing (3 months prepaid)',   'amount' => 1500, 'date' => '2026-03-26'],
                    ['category' => 'Travel',             'description' => 'Cab to Mohali for device pickup from service center','amount' => 500, 'date' => '2026-03-27'],
                    ['category' => 'Food & Beverages',   'description' => 'Working dinner during late night release',        'amount' => 4000, 'date' => '2026-03-27'],
                ],
            ],
            [
                'email'        => 'varun@company.test',
                'title'        => 'Client Meeting Travel - Bangalore',
                'total_amount' => 28500,
                'status'       => 'approved',
                'submitted_at' => Carbon::create(2026, 3, 8, 10, 0),
                'approved_at'  => Carbon::create(2026, 3, 10, 9, 30),
                'reimbursed_at'=> Carbon::create(2026, 3, 20, 12, 0),
                'items'        => [
                    ['category' => 'Travel',           'description' => 'Flight tickets Chandigarh-Bangalore (round trip)',  'amount' => 15000, 'date' => '2026-03-03'],
                    ['category' => 'Travel',           'description' => 'Hotel stay - 2 nights at Lemon Tree',              'amount' => 8000,  'date' => '2026-03-04'],
                    ['category' => 'Food & Beverages', 'description' => 'Client dinner at Bangalore restaurant',            'amount' => 3500,  'date' => '2026-03-04'],
                    ['category' => 'Travel',           'description' => 'Ola rides local Bangalore travel',                 'amount' => 2000,  'date' => '2026-03-05'],
                ],
            ],
            [
                'email'        => 'abhishek.kumar@company.test',
                'title'        => 'Design Conference Registration & Travel',
                'total_amount' => 7800,
                'status'       => 'rejected',
                'submitted_at' => Carbon::create(2026, 3, 12, 11, 0),
                'rejection_reason' => 'Conference registration should be pre-approved through the training budget. Please resubmit through the L&D portal.',
                'items'        => [
                    ['category' => 'Training & Courses', 'description' => 'UX India Conference 2026 registration fee', 'amount' => 5000, 'date' => '2026-03-10'],
                    ['category' => 'Travel',             'description' => 'Estimated travel to Mumbai for conference',  'amount' => 2800, 'date' => '2026-03-10'],
                ],
            ],
        ];

        foreach ($claims as $cl) {
            $profile = $this->profiles[$cl['email']] ?? null;
            if (!$profile) {
                continue;
            }

            $claimData = [
                'organization_id'     => $this->org->id,
                'employee_profile_id' => $profile->id,
                'total_amount'        => $cl['total_amount'],
                'status'              => $cl['status'],
                'submitted_at'        => $cl['submitted_at'],
            ];

            if ($cl['status'] === 'approved') {
                $claimData['approved_by'] = $vikas->id;
                $claimData['approved_at'] = $cl['approved_at'] ?? null;
                if (isset($cl['reimbursed_at'])) {
                    $claimData['reimbursed_at'] = $cl['reimbursed_at'];
                }
            } elseif ($cl['status'] === 'rejected') {
                $claimData['rejection_reason'] = $cl['rejection_reason'] ?? null;
            }

            $claim = HrExpenseClaim::firstOrCreate(
                ['organization_id' => $this->org->id, 'employee_profile_id' => $profile->id, 'title' => $cl['title']],
                $claimData
            );

            // Create expense items
            foreach ($cl['items'] as $item) {
                $catModel = $this->expenseCategories[$item['category']] ?? null;
                if (!$catModel) {
                    continue;
                }

                HrExpenseItem::firstOrCreate(
                    [
                        'hr_expense_claim_id'    => $claim->id,
                        'hr_expense_category_id' => $catModel->id,
                        'description'            => $item['description'],
                    ],
                    [
                        'amount'       => $item['amount'],
                        'expense_date' => $item['date'],
                    ]
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // SURVEY
    // ─────────────────────────────────────────────────────────────────
    private function seedSurvey(): void
    {
        $vikas = $this->users['vikas@company.test']['user'];

        $survey = HrSurvey::firstOrCreate(
            ['organization_id' => $this->org->id, 'title' => 'Employee Satisfaction Q1 2026'],
            [
                'description' => 'This quarterly pulse survey helps us understand employee satisfaction and identify areas for improvement. Your honest feedback is valuable and responses are anonymous.',
                'type'        => 'pulse',
                'is_anonymous'=> true,
                'status'      => 'active',
                'start_date'  => Carbon::create(2026, 3, 25),
                'end_date'    => Carbon::create(2026, 4, 5),
                'created_by'  => $vikas->id,
            ]
        );

        $questions = [
            [
                'question'    => 'On a scale of 1-5, how satisfied are you with your current role and responsibilities?',
                'type'        => 'rating',
                'options'     => ['min' => 1, 'max' => 5, 'labels' => ['Very Dissatisfied', 'Dissatisfied', 'Neutral', 'Satisfied', 'Very Satisfied']],
                'is_required' => true,
                'sort_order'  => 1,
            ],
            [
                'question'    => 'Do you feel you have adequate opportunities for professional growth and learning?',
                'type'        => 'multiple_choice',
                'options'     => ['Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly Disagree'],
                'is_required' => true,
                'sort_order'  => 2,
            ],
            [
                'question'    => 'How would you rate the communication and transparency from management?',
                'type'        => 'rating',
                'options'     => ['min' => 1, 'max' => 5, 'labels' => ['Very Poor', 'Poor', 'Average', 'Good', 'Excellent']],
                'is_required' => true,
                'sort_order'  => 3,
            ],
            [
                'question'    => 'Which areas do you think need the most improvement? (Select all that apply)',
                'type'        => 'multiple_choice',
                'options'     => ['Work-life balance', 'Compensation & benefits', 'Career growth', 'Team collaboration', 'Office infrastructure', 'Learning opportunities', 'Management communication'],
                'is_required' => false,
                'sort_order'  => 4,
            ],
            [
                'question'    => 'Do you have any suggestions or feedback to help us improve the workplace?',
                'type'        => 'text',
                'options'     => null,
                'is_required' => false,
                'sort_order'  => 5,
            ],
        ];

        foreach ($questions as $q) {
            HrSurveyQuestion::firstOrCreate(
                [
                    'hr_survey_id' => $survey->id,
                    'sort_order'   => $q['sort_order'],
                ],
                [
                    'question'    => $q['question'],
                    'type'        => $q['type'],
                    'options'     => $q['options'],
                    'is_required' => $q['is_required'],
                ]
            );
        }
    }
}
