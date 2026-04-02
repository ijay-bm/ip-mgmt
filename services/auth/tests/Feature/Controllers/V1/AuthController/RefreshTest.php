<?php

namespace Tests\Feature\Controllers\V1\AuthController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $token = JWTAuth::fromUser($user);

        $this->withToken($token)
            ->postJson(route('refresh'))
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_using_old_token_throws_error_for_general_routes(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $token = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->json('access_token');

        $this->withToken($token)
            ->postJson(route('refresh'))
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);

        $this->expectException(\Tymon\JWTAuth\Exceptions\TokenBlacklistedException::class);
        JWTAuth::setToken($token)->checkOrFail();
    }

    public function test_token_expires_after_one_hour_for_general_routes(): void
    {
        Carbon::setTestNow('2026-04-01 01:00:00');

        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $token = JWTAuth::fromUser($user);

        Carbon::setTestNow('2026-04-01 02:00:01');

        $this->withToken($token)->getJson(route('me'))->assertUnauthorized();
    }
}
