<?php

namespace Tests\Feature\Controllers\V1\AuthController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $token = JWTAuth::fromUser($user);

        $this->withToken($token)
            ->getJson(route('me'))
            ->assertOk()
            ->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function test_unauthenticated_user_cannot_get_user_details(): void
    {
        $this->json('GET', route('me'))->assertUnauthorized();
    }
}
