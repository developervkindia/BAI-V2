<?php

use App\Models\Organization;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $product = Product::firstOrCreate(
            ['key' => 'knowledge_base'],
            [
                'name' => 'Knowledge Base',
                'tagline' => 'Internal wiki, SOPs & stack documentation',
                'color' => 'sky',
                'route_prefix' => 'knowledge',
                'is_available' => true,
                'sort_order' => 8,
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
        $product = Product::where('key', 'knowledge_base')->first();
        if (! $product) {
            return;
        }

        DB::table('organization_subscriptions')->where('product_id', $product->id)->delete();
    }
};
