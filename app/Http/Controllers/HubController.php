<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductAccessService;
use Illuminate\Http\Request;

class HubController extends Controller
{
    public function __construct(protected ProductAccessService $productAccess) {}

    public function index(Request $request)
    {
        $user          = $request->user();
        $currentOrg    = $user->currentOrganization();
        $organizations = $user->allOrganizations();
        $allProducts   = Product::orderBy('sort_order')->get();
        $accessibleKeys = $this->productAccess
            ->getAccessibleProducts($user)
            ->pluck('key')
            ->toArray();

        return view('hub.index', [
            'currentOrg'     => $currentOrg,
            'organizations'  => $organizations,
            'allProducts'    => $allProducts,
            'accessibleKeys' => $accessibleKeys,
            'productConfig'  => config('products'),
        ]);
    }
}
