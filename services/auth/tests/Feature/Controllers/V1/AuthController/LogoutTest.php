<?php

namespace Tests\Feature\Controllers\V1\AuthController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $token = JWTAuth::claims(['session_id' => 'test-session'])->fromUser($user);

        $this->withToken($token)->postJson(route('logout'))->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
            'event' => 'logout',
            'properties->session_id' => 'test-session',
        ]);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $this->postJson(route('logout'))->assertUnauthorized();
    }
}
