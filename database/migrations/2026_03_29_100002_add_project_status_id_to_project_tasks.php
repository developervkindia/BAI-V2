<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add the new column
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->foreignId('project_status_id')->nullable()->after('status')
                  ->constrained('project_statuses')->nullOnDelete();
        });

        // Step 2: Seed default statuses for every existing project
        $defaultStatuses = [
            ['name' => 'Open',        'slug' => 'open',        'color' => '#94A3B8', 'position' => 1, 'is_completed_state' => false, 'is_default' => true],
            ['name' => 'In Progress', 'slug' => 'in_progress', 'color' => '#3B82F6', 'position' => 2, 'is_completed_state' => false, 'is_default' => false],
            ['name' => 'Completed',   'slug' => 'completed',   'color' => '#22C55E', 'position' => 3, 'is_completed_state' => true,  'is_default' => false],
            ['name' => 'Deferred',    'slug' => 'deferred',    'color' => '#6B7280', 'position' => 4, 'is_completed_state' => false, 'is_default' => false],
        ];

        $projects = DB::table('projects')->pluck('id');
        $now = now();

        foreach ($projects as $projectId) {
            foreach ($defaultStatuses as $s) {
                DB::table('project_statuses')->insert(array_merge($s, [
                    'project_id' => $projectId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // Step 3: Backfill project_status_id on existing tasks
        $statusRows = DB::table('project_statuses')->get();
        $lookup = [];
        foreach ($statusRows as $row) {
            $lookup[$row->project_id . ':' . $row->slug] = $row->id;
        }

        DB::table('project_tasks')->orderBy('id')->chunk(500, function ($tasks) use ($lookup) {
            foreach ($tasks as $task) {
                $key = $task->project_id . ':' . $task->status;
                if (isset($lookup[$key])) {
                    DB::table('project_tasks')
                        ->where('id', $task->id)
                        ->update(['project_status_id' => $lookup[$key]]);
                }
            }
        });

        // Step 4: Change the old status column from enum to varchar for flexibility
        DB::statement("ALTER TABLE project_tasks MODIFY COLUMN status VARCHAR(50) DEFAULT 'open'");
    }

    public function down(): void
    {
        // Restore enum column
        DB::statement("ALTER TABLE project_tasks MODIFY COLUMN status ENUM('open','in_progress','completed','deferred') DEFAULT 'open'");

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_status_id');
        });
    }
};
