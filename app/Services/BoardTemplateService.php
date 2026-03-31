<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardList;

class BoardTemplateService
{
    public static function getTemplates(): array
    {
        return [
            'blank' => [
                'name' => 'Blank Board',
                'description' => 'Start from scratch with an empty board.',
                'icon' => 'blank',
                'color' => 'from-gray-400 to-gray-500',
                'lists' => [],
            ],
            'agile-sprint' => [
                'name' => 'Agile Sprint',
                'description' => 'Sprint workflow with backlog, progress, review, and done stages.',
                'icon' => 'sprint',
                'color' => 'from-violet-500 to-fuchsia-500',
                'lists' => ['Backlog', 'Sprint To Do', 'In Progress', 'Code Review', 'QA Testing', 'Done'],
            ],
            'bug-tracking' => [
                'name' => 'Bug Tracking',
                'description' => 'Track and resolve issues with triage and resolution stages.',
                'icon' => 'bug',
                'color' => 'from-rose-500 to-orange-500',
                'lists' => ['Reported', 'Triaged', 'In Progress', 'In Review', 'Resolved', 'Closed'],
            ],
            'product-roadmap' => [
                'name' => 'Product Roadmap',
                'description' => 'Plan features from ideation through development to release.',
                'icon' => 'roadmap',
                'color' => 'from-cyan-500 to-blue-500',
                'lists' => ['Ideas', 'Next Up', 'In Development', 'Testing', 'Shipped'],
            ],
            'devops-pipeline' => [
                'name' => 'DevOps Pipeline',
                'description' => 'Manage infrastructure requests, deployments, and maintenance.',
                'icon' => 'devops',
                'color' => 'from-amber-500 to-orange-500',
                'lists' => ['Requests', 'In Progress', 'Monitoring', 'Maintenance', 'Completed'],
            ],
            'release-management' => [
                'name' => 'Release Management',
                'description' => 'Coordinate releases from preparation through deployment.',
                'icon' => 'release',
                'color' => 'from-emerald-500 to-green-600',
                'lists' => ['Upcoming', 'Preparation', 'Staging', 'Deploying', 'Released', 'Review'],
            ],
        ];
    }

    public static function applyTemplate(Board $board, string $templateKey): void
    {
        $templates = self::getTemplates();
        $template = $templates[$templateKey] ?? null;

        if (!$template || empty($template['lists'])) {
            return;
        }

        $position = 1024;

        foreach ($template['lists'] as $listName) {
            BoardList::create([
                'board_id' => $board->id,
                'name' => $listName,
                'position' => $position,
            ]);
            $position += 1024;
        }
    }
}
