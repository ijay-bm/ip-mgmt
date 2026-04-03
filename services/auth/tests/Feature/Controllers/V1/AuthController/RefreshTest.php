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

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);
    }

    public function test_authenticated_user_can_refresh_token(): void
    {
        $token = JWTAuth::fromUser($this->user);

        $this->withToken($token)
            ->postJson(route('refresh'))
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_unauthenticated_user_cant_refresh_token(): void
    {
        $this->postJson(route('refresh'))->assertUnauthorized();
    }

    public function test_using_old_token_throws_error_for_general_routes(): void
    {
        $token = $this->postJson(route('login'), [
            'email' => $this->user->email,
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

    public function test_token_is_invalid_after_expiration(): void
    {
        $now = Carbon::parse('2026-04-01 01:00:00');
        Carbon::setTestNow($now);

        $token = JWTAuth::fromUser($this->user);

        Carbon::setTestNow($now->clone()->addHours((int) config('jwt.ttl')));

        $this->withToken($token)->getJson(route('me'))->assertUnauthorized();
    }

    public function test_token_can_be_refreshed_after_expiration_if_within_refresh_window(): void
    {
        $now = Carbon::parse('2026-04-01 01:00:00');
        Carbon::setTestNow($now);

        $token = JWTAuth::fromUser($this->user);

        Carbon::setTestNow($now->clone()->addMinutes((int) config('jwt.ttl') + 1));

        $this->withToken($token)->getJson(route('me'))->assertUnauthorized();

        $this->withToken($token)->postJson(route('refresh'))->assertOk();
    }

    public function test_token_cannot_be_refreshed_after_expiration_if_outside_refresh_window(): void
    {
        $now = Carbon::parse('2026-04-01 01:00:00');
        Carbon::setTestNow($now);

        $token = JWTAuth::fromUser($this->user);

        Carbon::setTestNow($now->clone()->addMinutes((int) config('jwt.refresh_ttl') + 1));

        $this->withToken($token)->getJson(route('me'))->assertUnauthorized();
    }

    public function test_session_id_persists_after_refresh(): void
    {
        // 1. Login to get a real token with a session_id
        $loginResponse = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $oldSessionId = $loginResponse->json('session_id');
        $oldToken = $loginResponse->json('access_token');

        // 2. Refresh the token
        $refreshResponse = $this->withToken($oldToken)->postJson(route('refresh'))->assertOk();

        // 3. Assert the session_id remains the same [cite: 11, 52]
        $this->assertEquals($oldSessionId, $refreshResponse->json('session_id'));
    }
}
