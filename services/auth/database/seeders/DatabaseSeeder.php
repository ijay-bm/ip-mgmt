<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Role::create([
            'name' => 'super-admin',
            'guard_name' => 'api',
        ]);

        $userA = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $userA->assignRole('super-admin');

        User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
    }
}
