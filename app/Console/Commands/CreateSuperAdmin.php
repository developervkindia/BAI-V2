<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    protected $signature = 'super-admin:create {--email= : The email of the user to make super admin}';
    protected $description = 'Grant super admin privileges to a user';

    public function handle(): int
    {
        $email = $this->option('email') ?? $this->ask('Enter user email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        $user->update(['is_super_admin' => true]);

        $this->info("User '{$user->name}' ({$user->email}) is now a Super Admin.");
        return 0;
    }
}
