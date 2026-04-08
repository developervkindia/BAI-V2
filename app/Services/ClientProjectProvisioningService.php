<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class ClientProjectProvisioningService
{
    public static function createDeliveryProject(Client $client, User $actor): Project
    {
        $org = $client->organization;

        $name = $client->company
            ? "{$client->company} — Delivery"
            : "{$client->name} — Delivery";

        $project = Project::create([
            'organization_id' => $org->id,
            'owner_id' => $actor->id,
            'client_id' => $client->id,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'description' => $client->notes,
            'color' => '#F97316',
            'project_type' => 'fixed',
            'status' => 'in_progress',
            'visibility' => 'organization',
        ]);

        $project->members()->attach($actor->id, ['role' => 'manager']);
        $project->taskLists()->create(['name' => 'Tasks', 'position' => 1000]);

        $client->update([
            'stage' => Client::STAGE_ACTIVE,
            'hired_project_id' => $project->id,
        ]);

        return $project;
    }
}
