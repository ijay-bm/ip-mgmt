<?php

namespace Tests\Feature\Controllers\V1\AuthController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_can_login(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $response = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);

        $sessionId = JWTAuth::setToken($response->json('access_token'))->getPayload()->get('session_id');

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $user->id,
            'event' => 'login',
            'properties->session_id' => $sessionId,
        ]);
    }

    public function test_unregistered_user_email_cannot_login(): void
    {
        $this->postJson(route('login'), [
            'email' => 'john@doe.com',
            'password' => 'password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error', 'These credentials do not match our records.');
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'yeet',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error', 'These credentials do not match our records.');
    }
}
