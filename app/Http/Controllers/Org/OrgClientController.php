<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Mail\ClientPortalWelcomeMail;
use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\ClientPortalUser;
use App\Models\Organization;
use App\Models\ProjectBillingWeek;
use App\Services\ClientProjectProvisioningService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrgClientController extends Controller
{
    public function __construct(protected PermissionService $permissions) {}

    public function index(Request $request, Organization $organization)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.view', $organization), 403);

        $clients = Client::where('organization_id', $organization->id)
            ->withCount('projects')
            ->orderBy('name')
            ->get();

        return view('org.clients.index', compact('organization', 'clients'));
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        Client::create(array_merge($data, [
            'organization_id' => $organization->id,
            'stage' => Client::STAGE_PROSPECT,
        ]));

        return back()->with('success', 'Client created.');
    }

    public function show(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.view', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        $client->load(['projects.client', 'hiredProject', 'portalUsers', 'documents.uploadedBy']);

        $billingWeeks = ProjectBillingWeek::whereIn('project_id', $client->projects->pluck('id'))
            ->where('locked_at', '!=', null)
            ->with('project')
            ->orderByDesc('week_start')
            ->get();

        $canManage = $this->permissions->userCan($request->user(), 'org.clients.manage', $organization);

        return view('org.clients.show', compact('organization', 'client', 'billingWeeks', 'canManage'));
    }

    public function update(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $client->update($data);

        return back()->with('success', 'Client updated.');
    }

    public function destroy(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        $client->delete();

        return redirect()->route('org.clients.index', $organization)->with('success', 'Client deleted.');
    }

    public function approveRequirements(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        if ($client->stage !== Client::STAGE_PROSPECT) {
            return back()->with('error', 'Only prospects can be approved at this step.');
        }

        $client->update([
            'stage' => Client::STAGE_APPROVED,
            'requirements_approved_at' => now(),
        ]);

        return back()->with('success', 'Requirements approved. You can create the delivery project when ready.');
    }

    public function markLost(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        if ($client->stage === Client::STAGE_ACTIVE) {
            return back()->with('error', 'Cannot mark an active delivery client as lost.');
        }

        $client->update(['stage' => Client::STAGE_LOST]);

        return back()->with('success', 'Client marked as lost.');
    }

    public function createDeliveryProject(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        if ($client->hired_project_id) {
            return redirect()->route('projects.overview', $client->hiredProject)
                ->with('info', 'Delivery project already exists.');
        }

        if ($client->stage !== Client::STAGE_APPROVED) {
            return back()->with('error', 'Approve requirements first, then create the delivery project.');
        }

        $project = ClientProjectProvisioningService::createDeliveryProject($client, $request->user());

        return redirect()->route('projects.overview', $project)
            ->with('success', 'Delivery project created and linked to this client.');
    }

    public function invitePortalUser(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        $validated = $request->validateWithBag('portalInvite', [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        if (ClientPortalUser::where('email', $validated['email'])->exists()) {
            return back()
                ->withErrors(['email' => 'This email is already registered on the client portal.'], 'portalInvite')
                ->withInput();
        }

        $plain = Str::password(14, symbols: true);
        $portalUser = ClientPortalUser::create([
            'client_id' => $client->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $plain,
        ]);

        Mail::to($portalUser->email)->send(new ClientPortalWelcomeMail($client->load('organization'), $portalUser, $plain));

        return back()->with('success', 'Portal invitation sent to '.$portalUser->email.'.');
    }

    public function revokePortalUser(Request $request, Organization $organization, Client $client, ClientPortalUser $portalUser)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);
        abort_unless($portalUser->client_id === $client->id, 404);

        $portalUser->delete();

        return back()->with('success', 'Portal access removed.');
    }

    public function storeDocument(Request $request, Organization $organization, Client $client)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);

        $validated = $request->validate([
            'file' => 'required|file|max:20480',
            'visibility' => 'required|in:internal,portal',
        ]);

        $file = $validated['file'];
        $dir = 'client-documents/'.$client->id;
        $path = $file->store($dir, 'local');

        ClientDocument::create([
            'client_id' => $client->id,
            'uploaded_by_user_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'local',
            'visibility' => $validated['visibility'],
            'size_bytes' => $file->getSize(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function downloadDocument(Request $request, Organization $organization, Client $client, ClientDocument $document)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.view', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);
        abort_unless($document->client_id === $client->id, 404);

        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    public function destroyDocument(Request $request, Organization $organization, Client $client, ClientDocument $document)
    {
        $this->authorizeOrg($request, $organization);
        abort_unless($this->permissions->userCan($request->user(), 'org.clients.manage', $organization), 403);
        abort_unless($client->organization_id === $organization->id, 404);
        abort_unless($document->client_id === $client->id, 404);

        Storage::disk($document->disk)->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    private function authorizeOrg(Request $request, Organization $organization): void
    {
        if ($request->user()->is_super_admin) {
            return;
        }

        abort_unless($organization->hasUser($request->user()), 403);
    }
}
