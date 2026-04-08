<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /** API: return clients list for dropdowns */
    public function apiIndex(Request $request)
    {
        $org = $request->user()->currentOrganization();
        $clients = Client::where('organization_id', $org->id)
            ->orderBy('name')
            ->get(['id', 'name', 'company']);

        return response()->json($clients);
    }
}
