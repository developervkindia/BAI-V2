# BAI HR - User Manual

## Table of Contents
1. [Getting Started](#1-getting-started)
2. [Role Guide: Organization Owner](#2-organization-owner)
3. [Role Guide: HR Admin](#3-hr-admin)
4. [Role Guide: Manager](#4-manager)
5. [Role Guide: Employee (Self-Service)](#5-employee-self-service)
6. [Module Reference](#6-module-reference)

---

## 1. Getting Started

### How to Access BAI HR

1. **Login** at `http://your-domain.com/login`
2. You land on the **BAI Hub** (`/hub`) - the product launcher
3. Click the **BAI HR** card (rose/cyan colored) to open the HR module
4. You'll see the **HR Dashboard** at `/hr`

> BAI HR is auto-provisioned FREE for every organization. No setup needed.

### First-Time Setup Checklist

| Step | Who Does It | Where |
|------|------------|-------|
| 1. Create Organization | Owner | `/organizations/create` |
| 2. Invite team members | Owner/Admin | `/org/{org}/users` > Invite |
| 3. Create departments | Owner/Admin | `/hr/departments` |
| 4. Set up employee profiles | Owner/Admin | `/org/{org}/users/{user}/edit` |
| 5. Configure leave types | Owner/Admin | (via database/API) |
| 6. Set up salary structures | Owner/Admin | (via database/API) |

---

## 2. Organization Owner

*The owner has FULL access to everything. They created the organization.*

### 2.1 Invite Team Members

**Where:** Organization Settings > Users (`/org/{org-id}/users`)

1. Click **Invite Member**
2. Enter the person's **email address**
3. Select role: **Admin** or **Member**
4. Click **Send Invitation**
5. The person receives an invite link (valid 7 days)
6. When they accept, their **Employee Profile is auto-created**

> You can also **Resend** or **Cancel** pending invitations from the same page.

### 2.2 Edit Employee Details

**Where:** `/org/{org-id}/users/{user-id}/edit`

After inviting a member, edit their full profile:
- **Employee ID** (e.g., EMP-001)
- **Designation** (e.g., Senior Developer)
- **Department** (e.g., Engineering)
- **Date of Joining**
- **Employment Type** (full_time, part_time, contract, intern)
- **Reporting Manager** (select from existing members)
- **Work Location, Shift**
- **Personal Details** (DOB, gender, blood group, nationality)
- **Contact Info** (phone, personal email, work phone)
- **Address** (current & permanent)
- **Bank Details** (bank name, account number, IFSC, branch)
- **Emergency Contact** (name, phone)

### 2.3 Manage Roles

**Where:** `/org/{org-id}/roles`

- **Create custom roles** with specific permission sets
- **Assign roles to users** via `/org/{org-id}/users/{user-id}/role`
- Default roles: Owner (full access), Admin (full access), Member (limited)

### 2.4 Activate / Deactivate Users

**Where:** `/org/{org-id}/users/{user-id}`

- **Deactivate:** Sets employee status to 'inactive', keeps data but blocks access
- **Activate:** Restores access

---

## 3. HR Admin

*Admins have the same access as Owners for day-to-day HR operations.*

### 3.1 Create Departments

**Where:** HR > People > Departments (`/hr/departments`)

1. Click **Add Department**
2. Fill in:
   - **Name** (e.g., "Engineering")
   - **Code** (e.g., "ENG")
   - **Department Head** (select from users)
   - **Parent Department** (for hierarchy, optional)
   - **Active** toggle
3. Click **Save**

Departments appear in the sidebar and are used for filtering employees.

### 3.2 People Directory

**Where:** HR > People > Directory (`/hr/people`)

- **Grid view** or **List view** toggle
- **Search** by name, employee ID
- **Filter** by department, status (active/inactive/on_leave), employment type
- Click any employee to view their **full profile** (`/hr/people/{id}`)

### 3.3 Organization Chart

**Where:** HR > People > Org Chart (`/hr/people/org-chart`)

- Visual tree of reporting relationships
- Expand/collapse branches
- Search within the chart

### 3.4 Attendance Management

#### View All Attendance (`/hr/attendance`)
- Select **month/year** from dropdowns
- Select **employee** from dropdown to view their attendance
- **Calendar view** showing color-coded days (present/absent/late/half-day/leave/holiday)
- **Table view** with clock-in/out times, hours, overtime

#### Team Attendance (`/hr/attendance/team`)
- Grid: employees vs days of month
- Color-coded cells: P=Present, A=Absent, H=Half-day, LT=Late, L=Leave

#### Attendance Reports (`/hr/attendance/reports`)
- Summary: avg attendance %, total late marks, absentees
- Table: per-employee present/absent/late/overtime stats
- Export to CSV

### 3.5 Leave Management

#### Leave Overview (`/hr/leave`)
- Balance cards for each leave type
- Recent leave requests with status

#### Approvals (`/hr/leave/approvals`)
- Queue of **pending** leave requests
- For each request: employee name, leave type, dates, days, reason
- Click **Approve** to approve instantly
- Click **Reject** to enter rejection reason and reject

#### Leave Calendar (`/hr/leave/calendar`)
- Monthly calendar showing who's on leave
- Color-coded by leave type
- Navigate between months

### 3.6 Payroll

#### Payroll Dashboard (`/hr/payroll`)
- Stats: employees on payroll, last payroll amount, status
- Recent payroll runs with status badges

#### Run Payroll (`/hr/payroll/run`)
1. Select **Month** and **Year**
2. Review employee list
3. Click **Process Payroll**
4. System calculates: gross, deductions (PF, ESI, TDS, PT), net pay
5. View the run at `/hr/payroll/runs/{id}`

#### Payroll Run Detail (`/hr/payroll/runs/{id}`)
- Summary: total employees, gross, deductions, net
- Individual entries table
- **Finalize** button (locks the run)
- **Mark Paid** button (after bank transfer)
- Click any entry to view **Payslip**

#### Payslip (`/hr/payroll/payslip/{id}`)
- Professional payslip layout
- Earnings breakdown, Deductions breakdown
- Net pay highlighted
- **Print** button

### 3.7 Performance Management

#### Create Review Cycle
Review cycles are created via API:
```
POST /api/hr/reviews/{review}/submit
Body: { overall_rating, strengths, improvements }
```

#### View Cycles (`/hr/performance/cycles`)
- All review cycles with status (draft/active/closed)
- Click to see cycle detail with all reviews

#### Cycle Detail (`/hr/performance/cycles/{id}`)
- Stats: total reviews, submitted, pending, avg rating
- Reviews table: employee, type, rating, status

### 3.8 Expense Management

#### All Claims (`/hr/expenses`)
- Filter: All / Draft / Submitted / Approved / Rejected / Reimbursed
- Table: title, employee, amount, status, date

#### Approvals (`/hr/expenses/approvals`)
- Pending expense claims
- **Approve** or **Reject** (with reason)

#### Reimburse (`/hr/expenses/{id}`)
- After approval, click **Mark Reimbursed**

### 3.9 Recruitment

#### Job Postings (`/hr/recruitment`)
- Click **New Posting** to create:
  - Title, Department, Employment Type, Location
  - Description, Requirements, Salary Range, Positions
- Postings show candidate count and status

#### Add Candidates
- Open a posting > Click **Add Candidate**
  - Name, Email, Phone, Source, Experience, Expected CTC

#### Pipeline View (`/hr/recruitment/{id}/pipeline`)
- **Kanban board** with columns: Applied > Screening > Interview > Offer > Hired / Rejected
- Move candidates between stages
- Schedule interviews from Interview stage

### 3.10 Engagement

#### Engagement Feed (`/hr/engagement`)
- Combined feed of announcements and recognitions
- Sidebar: upcoming birthdays and anniversaries
- **Give Recognition** button: select employee, type (badge/award/shoutout), title, description

#### Birthdays (`/hr/engagement/birthdays`)
- Grouped: This Week, This Month, Next Month

#### Anniversaries (`/hr/engagement/anniversaries`)
- Grouped: This Week, This Month

### 3.11 Surveys

#### Create Survey (`/hr/surveys/create`)
1. Enter **Title**, Description, Type (pulse/engagement/custom)
2. Toggle **Anonymous** if needed
3. Set Start/End dates
4. **Add Questions:**
   - Text (free-form answer)
   - Rating (1-5 stars)
   - Multiple Choice (checkboxes)
   - Single Choice (radio buttons)
   - Yes/No
5. Submit to create as **Draft**

#### Publish Survey
- From survey list (`/hr/surveys`), click **Publish** on a draft survey
- Survey becomes **Active** and employees can respond

#### View Results (`/hr/surveys/{id}`)
- Response analytics per question
- Bar charts for choices, star distribution for ratings

### 3.12 Announcements

#### Create Announcement (`/hr/announcements/create`)
- **Title**, Body, Type (general/policy/event/celebration)
- Target specific departments (optional)
- **Pin** important announcements
- Set publish date

#### Manage (`/hr/announcements`)
- Pin/Unpin toggle
- Delete with confirmation
- View full announcement

---

## 4. Manager

*Managers see their direct reports' data. They have the same Member-level access plus team views.*

### 4.1 Team Attendance
**Where:** HR > Attendance > Team View (`/hr/attendance/team`)
- See all direct reports' attendance for the month
- Color-coded grid

### 4.2 Leave Approvals
**Where:** HR > Leave > Approvals (`/hr/leave/approvals`)
- Approve or reject leave requests from your team
- See reason, dates, balance info

### 4.3 Expense Approvals
**Where:** HR > Expenses > Approvals (`/hr/expenses/approvals`)
- Approve or reject expense claims from your team

### 4.4 Performance Reviews
**Where:** HR > Performance (`/hr/performance`)
- Submit **manager reviews** for direct reports
- Rate individual goals and KRAs
- View review cycle progress

---

## 5. Employee (Self-Service)

*Every employee can access these features for themselves.*

### 5.1 View/Edit Own Profile
**Where:** Profile > Full Profile (`/profile/full`)
- View personal details, employment info, education, experience, skills
- Edit: phone, personal email, DOB, gender, blood group, nationality, emergency contact, addresses

### 5.2 Clock In / Clock Out
**Where:** HR > Attendance > My Attendance (`/hr/attendance/my`)

1. Click the **Clock In** button when you start work
2. Live timer shows elapsed time
3. Click **Clock Out** when done
4. Monthly calendar shows your attendance history

> You can only clock in once per day. If you miss it, request regularization.

### 5.3 Apply for Leave
**Where:** HR > Leave > Apply Leave (`/hr/leave/apply`)

1. Select **Leave Type** (shows available balance)
2. Pick **Start Date** and **End Date**
3. Toggle **Half Day** if needed (select first/second half)
4. Enter **Reason**
5. Click **Submit**
6. Request goes to manager for approval

### 5.4 View Leave Balance & History
- **Overview** (`/hr/leave`): Balance cards for each leave type
- **My Leaves** (`/hr/leave/my`): History of all requests with status
- **Cancel** pending requests from My Leaves page

### 5.5 View Payslips
**Where:** HR > Payroll > My Payslips (`/hr/payroll/my-payslips`)
- List of all processed payslips
- Click to view detailed breakdown
- **Print** payslip

### 5.6 Self-Review
**Where:** HR > Performance > My Review (`/hr/performance/my-review`)

When a review cycle is active:
1. Rate yourself (1-5 stars)
2. Fill in **Strengths** and **Areas for Improvement**
3. Rate each assigned **Goal** individually
4. Click **Submit**

### 5.7 Submit Expense Claims
**Where:** HR > Expenses > Create (`/hr/expenses/create`)

1. Enter claim **Title**
2. Add line items:
   - Category (travel, food, etc.)
   - Description
   - Amount
   - Date
   - Receipt (upload)
3. Click **Create** (saves as Draft)
4. Click **Submit for Approval** when ready

Track your claims at `/hr/expenses/my`

### 5.8 Respond to Surveys
**Where:** HR > Surveys (`/hr/surveys`)

1. Find active surveys in the list
2. Click **Take Survey** / Respond
3. Answer each question (text, rating, choices)
4. Click **Submit**

### 5.9 View Announcements
**Where:** HR > Announcements (`/hr/announcements`)
- Pinned announcements at top
- All announcements chronologically
- Click to read full content

### 5.10 Engagement
**Where:** HR > Engagement (`/hr/engagement`)
- See team birthdays and anniversaries
- Give recognition to colleagues (shoutout, badge, award)

---

## 6. Module Reference

### URL Quick Reference

| Page | URL | Who Can Access |
|------|-----|---------------|
| HR Dashboard | `/hr` | All |
| People Directory | `/hr/people` | All |
| Org Chart | `/hr/people/org-chart` | All |
| Departments | `/hr/departments` | Admin |
| My Attendance | `/hr/attendance/my` | All |
| All Attendance | `/hr/attendance` | Admin |
| Team Attendance | `/hr/attendance/team` | Managers |
| Attendance Reports | `/hr/attendance/reports` | Admin |
| Leave Overview | `/hr/leave` | All |
| Apply Leave | `/hr/leave/apply` | All |
| My Leaves | `/hr/leave/my` | All |
| Leave Calendar | `/hr/leave/calendar` | All |
| Leave Approvals | `/hr/leave/approvals` | Managers |
| Payroll Dashboard | `/hr/payroll` | Admin |
| Run Payroll | `/hr/payroll/run` | Admin |
| My Payslips | `/hr/payroll/my-payslips` | All |
| Performance | `/hr/performance` | All |
| Review Cycles | `/hr/performance/cycles` | Admin |
| My Review | `/hr/performance/my-review` | All |
| Expenses | `/hr/expenses` | Admin |
| Create Expense | `/hr/expenses/create` | All |
| My Expenses | `/hr/expenses/my` | All |
| Expense Approvals | `/hr/expenses/approvals` | Managers |
| Recruitment | `/hr/recruitment` | Admin |
| Engagement | `/hr/engagement` | All |
| Birthdays | `/hr/engagement/birthdays` | All |
| Anniversaries | `/hr/engagement/anniversaries` | All |
| Surveys | `/hr/surveys` | All |
| Create Survey | `/hr/surveys/create` | Admin |
| Announcements | `/hr/announcements` | All |
| Create Announcement | `/hr/announcements/create` | Admin |

### API Endpoints Reference

| Action | Method | Endpoint |
|--------|--------|----------|
| Clock In | POST | `/api/hr/attendance/clock-in` |
| Clock Out | POST | `/api/hr/attendance/clock-out` |
| Today's Status | GET | `/api/hr/attendance/today` |
| Apply Leave | POST | `/api/hr/leave-requests` |
| Approve Leave | POST | `/api/hr/leave-requests/{id}/approve` |
| Reject Leave | POST | `/api/hr/leave-requests/{id}/reject` |
| Cancel Leave | POST | `/api/hr/leave-requests/{id}/cancel` |
| Process Payroll | POST | `/api/hr/payroll-runs` |
| Finalize Payroll | POST | `/api/hr/payroll-runs/{id}/finalize` |
| Mark Paid | POST | `/api/hr/payroll-runs/{id}/mark-paid` |
| Submit Review | POST | `/api/hr/reviews/{id}/submit` |
| Rate KRA/Goal | POST | `/api/hr/review-ratings` |
| Create Expense | POST | `/api/hr/expense-claims` |
| Submit Expense | POST | `/api/hr/expense-claims/{id}/submit` |
| Approve Expense | POST | `/api/hr/expense-claims/{id}/approve` |
| Reject Expense | POST | `/api/hr/expense-claims/{id}/reject` |
| Reimburse | POST | `/api/hr/expense-claims/{id}/reimburse` |
| Create Job Posting | POST | `/api/hr/job-postings` |
| Add Candidate | POST | `/api/hr/candidates` |
| Move Candidate | POST | `/api/hr/candidates/{id}/move` |
| Schedule Interview | POST | `/api/hr/interviews` |
| Interview Feedback | PUT | `/api/hr/interviews/{id}` |
| Create Survey | POST | `/api/hr/surveys` |
| Publish Survey | POST | `/api/hr/surveys/{id}/publish` |
| Respond to Survey | POST | `/api/hr/surveys/{id}/respond` |
| Close Survey | POST | `/api/hr/surveys/{id}/close` |
| Create Announcement | POST | `/api/hr/announcements` |
| Pin Announcement | POST | `/api/hr/announcements/{id}/pin` |
| Give Recognition | POST | `/api/hr/recognitions` |

### Artisan Commands

| Command | Purpose | Frequency |
|---------|---------|-----------|
| `php artisan hr:accrue-leaves` | Monthly leave accrual | Monthly (cron) |
| `php artisan hr:carry-forward-leaves` | Year-end balance carry forward | Yearly (Jan 1) |
| `php artisan hr:expire-comp-offs` | Expire old comp-off leaves | Monthly (cron) |
| `php artisan hr:birthday-anniversary-notify` | Send birthday/anniversary notifications | Daily (cron) |

### Status Badges Color Guide

| Status | Color | Used In |
|--------|-------|---------|
| Active / Present / Approved | Green | Everywhere |
| Pending / Late | Amber/Yellow | Leave, Expenses |
| Draft | Gray | Payroll, Surveys |
| Rejected / Absent | Red | Leave, Expenses |
| On Leave | Blue | Attendance |
| Holiday | Purple | Attendance |
| Cancelled | Gray | Leave |
| Finalized | Green | Payroll |
| Paid / Reimbursed | Emerald | Payroll, Expenses |
