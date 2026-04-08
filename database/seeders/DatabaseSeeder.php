<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProductSeeder::class);
        $this->call(PermissionSeeder::class);

        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@bai.com',
            'password' => bcrypt('12345678'),
            'is_super_admin' => true,
        ]);

        $this->call(AppDeftVinnisoftSeeder::class);
        $this->call(KnowledgeBaseDemoSeeder::class);
    }
}
