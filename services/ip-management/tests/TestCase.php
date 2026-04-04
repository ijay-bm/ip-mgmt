<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'jwt.algo' => 'HS256',
            'jwt.secret' => Str::random(64),
        ]);
    }

    protected function makeUser(array $overrides = []): User
    {
        $defaults = [
            'id' => fake()->numberBetween(1, 1000),
            'type' => 'App\\Models\\User',
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'roles' => [],
            'sessionId' => Str::uuid()->toString(),
        ];

        $merged = array_merge($defaults, $overrides);

        return new User(...$merged);
    }

    protected function makeSuperAdminUser(array $overrides = []): User
    {
        return $this->makeUser(array_merge($overrides, ['roles' => ['super-admin']]));
    }

    protected function mintToken(User $user): string
    {
        $claims = [
            'sub' => $user->id,
            'user_type' => $user->type,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles,
            'session_id' => $user->sessionId,
        ];

        $payload = JWTAuth::factory()->customClaims($claims)->make();

        return JWTAuth::manager()->encode($payload)->get();
    }

    protected function mintNormalUserToken(array $overrides = []): string
    {
        return $this->mintToken($this->makeUser($overrides));
    }

    protected function mintSuperAdminUserToken(array $overrides = []): string
    {
        return $this->mintToken($this->makeUser(array_merge($overrides, ['roles' => ['super-admin']])));
    }
}
