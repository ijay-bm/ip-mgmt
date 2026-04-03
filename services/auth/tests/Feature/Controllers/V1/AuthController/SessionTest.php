<?php

namespace Tests\Feature\Controllers\V1\AuthController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_session_id_persists_through_token_lifecycle(): void
    {
        // 1. Initial Login
        $firstAccessToken = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->json('access_token');

        $firstSessionId = JWTAuth::setToken($firstAccessToken)->getPayload()->get('session_id');

        // 2. Refresh the token
        $secondAccessToken = $this->withToken($firstAccessToken)
            ->postJson(route('refresh'))
            ->assertOk()
            ->json('access_token');

        $secondSessionId = JWTAuth::setToken($secondAccessToken)->getPayload()->get('session_id');

        // 3. Logout
        $this->withToken($secondAccessToken)->postJson(route('logout'))->assertOk();

        // 4. Assert the session_id remains the same
        $this->assertEquals($firstSessionId, $secondSessionId);

        // 5. Verify the activity log entry
        $this->assertDatabaseHas('activity_log', [
            'event' => 'login',
            'properties->session_id' => $firstSessionId,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'logout',
            'properties->session_id' => $secondSessionId,
        ]);
    }

    public function test_session_id_changes_on_second_login(): void
    {
        // 1. Initial Login
        $firstAccessToken = $this->postJson(route('login'), [
            'email' => $this->user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->json('access_token');

        $firstSessionId = JWTAuth::setToken($firstAccessToken)->getPayload()->get('session_id');

        // 2. Second Login
        $secondAccessToken = $this->withToken($firstAccessToken)
            ->postJson(route('login'), [
                'email' => $this->user->email,
                'password' => 'password',
            ])
            ->assertOk()
            ->json('access_token');

        $secondSessionId = JWTAuth::setToken($secondAccessToken)->getPayload()->get('session_id');

        // 3. Assert the session_id changes
        $this->assertNotEquals($firstSessionId, $secondSessionId);

        // 4. Verify the activity log entry
        $this->assertDatabaseHas('activity_log', [
            'event' => 'login',
            'properties->session_id' => $firstSessionId,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'login',
            'properties->session_id' => $secondSessionId,
        ]);
    }
}
