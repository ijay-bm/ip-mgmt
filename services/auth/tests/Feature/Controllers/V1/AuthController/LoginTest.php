<?php

namespace Tests\Feature\Controllers\V1\AuthController;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_user_can_login(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
        ]);

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_unregistered_user_email_cannot_login(): void
    {
        $this->postJson(route('login'), [
            'email' => 'john@doe.com',
            'password' => 'password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('error', 'Invalid credentials');
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
            ->assertJsonPath('error', 'Invalid credentials');
    }
}
