<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Plan Feature Matrix
    |--------------------------------------------------------------------------
    |
    | Each product key maps to its plan tiers. Each tier defines feature flags
    | (boolean) and numeric limits (null = unlimited). The PlanService reads
    | this config to enforce gating throughout the application.
    |
    */

    'board' => [
        'free' => [
            'max_boards'       => 5,
            'max_members'      => 10,
            'custom_fields'    => false,
            'automations'      => false,
            'templates'        => false,
            'board_chat'       => true,
            'calendar_view'    => true,
            'timeline_view'    => false,
            'dashboard_view'   => false,
        ],
        'pro' => [
            'max_boards'       => 50,
            'max_members'      => 100,
            'custom_fields'    => true,
            'automations'      => true,
            'templates'        => true,
            'board_chat'       => true,
            'calendar_view'    => true,
            'timeline_view'    => true,
            'dashboard_view'   => true,
        ],
        'enterprise' => [
            'max_boards'       => null,
            'max_members'      => null,
            'custom_fields'    => true,
            'automations'      => true,
            'templates'        => true,
            'board_chat'       => true,
            'calendar_view'    => true,
            'timeline_view'    => true,
            'dashboard_view'   => true,
        ],
    ],

    'projects' => [
        'free' => [
            'max_projects'     => 3,
            'max_members'      => 10,
            'sprints'          => false,
            'time_tracking'    => true,
            'timesheets'       => false,
            'billing'          => false,
            'custom_fields'    => false,
            'recurring_tasks'  => false,
            'documents'        => true,
            'budget'           => false,
            'workload'         => false,
        ],
        'pro' => [
            'max_projects'     => 50,
            'max_members'      => 100,
            'sprints'          => true,
            'time_tracking'    => true,
            'timesheets'       => true,
            'billing'          => true,
            'custom_fields'    => true,
            'recurring_tasks'  => true,
            'documents'        => true,
            'budget'           => true,
            'workload'         => true,
        ],
        'enterprise' => [
            'max_projects'     => null,
            'max_members'      => null,
            'sprints'          => true,
            'time_tracking'    => true,
            'timesheets'       => true,
            'billing'          => true,
            'custom_fields'    => true,
            'recurring_tasks'  => true,
            'documents'        => true,
            'budget'           => true,
            'workload'         => true,
        ],
    ],

    'opportunity' => [
        'free' => [
            'max_projects'     => 5,
            'max_members'      => 15,
            'goals'            => false,
            'portfolios'       => false,
            'reporting'        => false,
            'forms'            => true,
            'automations'      => false,
            'templates'        => false,
            'approvals'        => false,
        ],
        'pro' => [
            'max_projects'     => 50,
            'max_members'      => 100,
            'goals'            => true,
            'portfolios'       => true,
            'reporting'        => true,
            'forms'            => true,
            'automations'      => true,
            'templates'        => true,
            'approvals'        => true,
        ],
        'enterprise' => [
            'max_projects'     => null,
            'max_members'      => null,
            'goals'            => true,
            'portfolios'       => true,
            'reporting'        => true,
            'forms'            => true,
            'automations'      => true,
            'templates'        => true,
            'approvals'        => true,
        ],
    ],

    'hr' => [
        'free' => [
            'max_employees'    => 25,
            'attendance'       => true,
            'leave'            => true,
            'payroll'          => false,
            'performance'      => false,
            'expenses'         => false,
            'recruitment'      => false,
            'surveys'          => false,
            'announcements'    => true,
            'engagement'       => true,
            'onboarding'       => false,
        ],
        'pro' => [
            'max_employees'    => 250,
            'attendance'       => true,
            'leave'            => true,
            'payroll'          => true,
            'performance'      => true,
            'expenses'         => true,
            'recruitment'      => true,
            'surveys'          => true,
            'announcements'    => true,
            'engagement'       => true,
            'onboarding'       => true,
        ],
        'enterprise' => [
            'max_employees'    => null,
            'attendance'       => true,
            'leave'            => true,
            'payroll'          => true,
            'performance'      => true,
            'expenses'         => true,
            'recruitment'      => true,
            'surveys'          => true,
            'announcements'    => true,
            'engagement'       => true,
            'onboarding'       => true,
        ],
    ],

];
