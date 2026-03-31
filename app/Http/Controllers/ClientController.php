<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $org     = $request->user()->currentOrganization();
        $clients = Client::where('organization_id', $org->id)
            ->withCount('projects')
            ->orderBy('name')
            ->get();

        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'timezone'=> 'nullable|string|max:100',
            'notes'   => 'nullable|string',
        ]);

        $org = $request->user()->currentOrganization();
        Client::create(array_merge($data, ['organization_id' => $org->id]));

        return back()->with('success', 'Client created.');
    }

    public function show(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);

        $client->load(['projects.client']);
        $billingWeeks = \App\Models\ProjectBillingWeek::whereIn('project_id', $client->projects->pluck('id'))
            ->where('locked_at', '!=', null)
            ->with('project')
            ->orderByDesc('week_start')
            ->get();

        return view('clients.show', compact('client', 'billingWeeks'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);

        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'timezone'=> 'nullable|string|max:100',
            'notes'   => 'nullable|string',
        ]);

        $client->update($data);

        return back()->with('success', 'Client updated.');
    }

    public function destroy(Request $request, Client $client)
    {
        $this->authorizeClient($request, $client);
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }

    /** API: return clients list for dropdowns */
    public function apiIndex(Request $request)
    {
        $org = $request->user()->currentOrganization();
        $clients = Client::where('organization_id', $org->id)
            ->orderBy('name')
            ->get(['id', 'name', 'company']);

        return response()->json($clients);
    }

    private function authorizeClient(Request $request, Client $client): void
    {
        abort_unless($client->organization_id === $request->user()->currentOrganization()->id, 403);
    }
}
