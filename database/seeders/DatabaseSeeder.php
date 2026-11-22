<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/** Productie: alleen de eerste beheerder. Die krijgt een mail om een wachtwoord te kiezen. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL', 'peter@dehon.eu');
        $user = User::firstOrCreate(['email' => $email], [
            'name' => env('SEED_ADMIN_NAME', 'Beheerder'),
            'password' => Str::random(40),
            'role' => Role::BEHEERDER,
        ]);
        $token = Password::broker()->createToken($user);
        $this->command?->info("Beheerder {$email}. Wachtwoord instellen: " . url(route('password.reset', ['token' => $token, 'email' => $email], false)));
    }
}
