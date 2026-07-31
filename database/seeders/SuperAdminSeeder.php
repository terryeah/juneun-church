<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the initial super_admin account for the developer.
 *
 * The credentials come from environment variables so production seeds
 * never contain a hard-coded password.
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->where('email', env('SUPER_ADMIN_EMAIL', 'terryeah7@gmail.com'))->first();

        if (! $user && ! env('SUPER_ADMIN_PASSWORD')) {
            $this->command?->warn('SUPER_ADMIN_PASSWORD is not set; skipping super admin creation.');

            return;
        }

        $user ??= User::query()->create([
            'email' => env('SUPER_ADMIN_EMAIL', 'terryeah7@gmail.com'),
            'name' => env('SUPER_ADMIN_NAME', 'Terry'),
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD')),
        ]);

        $user->assignRole('super_admin');
    }
}
