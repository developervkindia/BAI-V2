<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'key' => 'projects',
                'name' => 'BAI Projects',
                'tagline' => 'End-to-end project management & tracking',
                'color' => 'amber',
                'route_prefix' => 'projects',
                'is_available' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'board',
                'name' => 'BAI Board',
                'tagline' => 'Visual kanban boards & team collaboration',
                'color' => 'indigo',
                'route_prefix' => 'board',
                'is_available' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'docs',
                'name' => 'BAI Docs',
                'tagline' => 'Documents, spreadsheets, forms & presentations',
                'color' => 'sky',
                'route_prefix' => 'docs',
                'is_available' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'desk',
                'name' => 'BAI Desk',
                'tagline' => 'Customer support & smart ticketing',
                'color' => 'violet',
                'route_prefix' => 'desk',
                'is_available' => false,
                'sort_order' => 4,
            ],
            [
                'key' => 'crm',
                'name' => 'BAI CRM',
                'tagline' => 'Contacts, pipelines & revenue automation',
                'color' => 'emerald',
                'route_prefix' => 'crm',
                'is_available' => false,
                'sort_order' => 5,
            ],
            [
                'key' => 'opportunity',
                'name' => 'Opportunity',
                'tagline' => 'Task management, goals & collaboration',
                'color' => 'teal',
                'route_prefix' => 'opportunity',
                'is_available' => true,
                'sort_order' => 6,
            ],
            [
                'key' => 'hr',
                'name' => 'BAI HR',
                'tagline' => 'Complete HR management & employee engagement',
                'color' => 'rose',
                'route_prefix' => 'hr',
                'is_available' => true,
                'sort_order' => 7,
            ],
            [
                'key' => 'knowledge_base',
                'name' => 'Knowledge Base',
                'tagline' => 'Internal wiki, SOPs & stack documentation',
                'color' => 'sky',
                'route_prefix' => 'knowledge',
                'is_available' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['key' => $product['key']], $product);
        }
    }
}
