<?php

namespace Tests\Feature\Controllers\V1\AuthController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_user_details(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $role = Role::create(['name' => 'user']);

        $user->assignRole($role);

        $token = JWTAuth::fromUser($user);

        $this->withToken($token)
            ->getJson(route('me'))
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'roles']])
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'John Doe',
                    'email' => 'john@doe.com',
                    'roles' => ['user'],
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_get_user_details(): void
    {
        $this->json('GET', route('me'))->assertUnauthorized();
    }
}
