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
                'key'          => 'smartprojects',
                'name'         => 'SmartProjects',
                'tagline'      => 'Project management & team tracking',
                'color'        => 'amber',
                'route_prefix' => 'projects',
                'is_available' => true,
                'sort_order'   => 2,
            ],
            [
                'key'          => 'smartboard',
                'name'         => 'SmartBoard',
                'tagline'      => 'Visual project management',
                'color'        => 'indigo',
                'route_prefix' => 'board',
                'is_available' => true,
                'sort_order'   => 1,
            ],
            [
                'key'          => 'smartdocs',
                'name'         => 'SmartDocs',
                'tagline'      => 'Collaborative documents & wikis',
                'color'        => 'sky',
                'route_prefix' => 'docs',
                'is_available' => false,
                'sort_order'   => 3,
            ],
            [
                'key'          => 'smartdesk',
                'name'         => 'SmartDesk',
                'tagline'      => 'Customer support & ticketing',
                'color'        => 'violet',
                'route_prefix' => 'desk',
                'is_available' => false,
                'sort_order'   => 4,
            ],
            [
                'key'          => 'smartcrm',
                'name'         => 'SmartCRM',
                'tagline'      => 'Contacts, deals & pipelines',
                'color'        => 'emerald',
                'route_prefix' => 'crm',
                'is_available' => false,
                'sort_order'   => 5,
            ],
            [
                'key'          => 'opportunity',
                'name'         => 'Opportunity',
                'tagline'      => 'Task management, goals & collaboration',
                'color'        => 'teal',
                'route_prefix' => 'opportunity',
                'is_available' => true,
                'sort_order'   => 6,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['key' => $product['key']], $product);
        }
    }
}
