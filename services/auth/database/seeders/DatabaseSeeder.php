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
        $this->createTestUsersAndRoles();
    }

    public function createTestUsersAndRoles()
    {
        Role::create([
            'name' => 'super-admin',
            'guard_name' => 'api',
        ]);

        Role::create([
            'name' => 'user',
            'guard_name' => 'api',
        ]);

        $userA = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $userA->assignRole('super-admin');

        $userB = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);
        $userB->assignRole('user');

        $userC = User::factory()->create([
            'name' => 'Tim',
            'email' => 'tim@example.com',
        ]);
        $userC->assignRole('user');

        $userD = User::factory()->create([
            'name' => 'Lin',
            'email' => 'lin@example.com',
        ]);
        $userD->assignRole('user');

        $users = User::factory(6)->create();

        foreach ($users as $user) {
            $user->assignRole(fake()->boolean() ? 'user' : 'super-admin');
        }
    }
}
