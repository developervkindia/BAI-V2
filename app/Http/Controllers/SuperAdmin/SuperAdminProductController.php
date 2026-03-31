<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SuperAdminAuditLog;
use Illuminate\Http\Request;

class SuperAdminProductController extends Controller
{
    public function index()
    {
        $products = Product::withCount('subscriptions')
            ->orderBy('sort_order')
            ->get();

        return view('super-admin.products.index', compact('products'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'required|string|max:255',
            'is_available' => 'required|boolean',
        ]);

        $product->update([
            'name' => $request->name,
            'tagline' => $request->tagline,
            'is_available' => $request->is_available,
        ]);

        SuperAdminAuditLog::record(auth()->user(), 'product.updated', $product);

        return redirect()->back()->with('success', 'Product updated successfully.');
    }
}
