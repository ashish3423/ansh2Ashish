<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:assign-role {email} {role}', function (string $email, string $role) {
    if (! in_array($role, User::ROLES, true)) {
        $this->error('Role must be one of: '.implode(', ', User::ROLES).'.');

        return Command::FAILURE;
    }

    $user = User::where('email', $email)->first();
    if (! $user) {
        $this->error("No user found with email {$email}.");

        return Command::FAILURE;
    }

    $user->update(['role' => $role]);
    $this->info("Updated {$email} to role {$role}.");

    return Command::SUCCESS;
})->purpose('Assign a role to a user during trusted application setup');
