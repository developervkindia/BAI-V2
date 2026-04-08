<?php

use App\Models\Organization;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $product = Product::firstOrCreate(
            ['key' => 'docs'],
            [
                'name' => 'BAI Docs',
                'tagline' => 'Documents, spreadsheets, forms & presentations',
                'color' => 'sky',
                'route_prefix' => 'docs',
                'is_available' => true,
                'sort_order' => 3,
            ]
        );

        Organization::query()->each(function (Organization $org) use ($product) {
            $org->subscriptions()->firstOrCreate(
                ['product_id' => $product->id],
                ['plan' => 'free', 'status' => 'active', 'starts_at' => now()]
            );
        });
    }

    public function down(): void
    {
        $product = Product::where('key', 'docs')->first();

        if ($product) {
            $product->subscriptions()->delete();
        }
    }
};
