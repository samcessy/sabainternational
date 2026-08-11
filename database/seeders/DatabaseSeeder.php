<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // The first Super Administrator can't be admin-invited (there's no
        // admin yet to send the invitation) — it's seeded directly, matching
        // docs/architecture/authorization-model.md §4's bootstrap case.
        User::factory()->superAdministrator()->withTwoFactor()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(ProgramSeeder::class);
        $this->call(TeamMemberSeeder::class);
    }
}
