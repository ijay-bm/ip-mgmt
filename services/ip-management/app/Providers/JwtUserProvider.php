<?php

namespace App\Providers;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Payload;

class JwtUserProvider implements UserProvider
{
    /**
     * Called by JWT-Auth to find the user by the 'sub' claim.
     */
    public function retrieveById($identifier): ?User
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();

            $this->validatePayload($payload);

            return $this->buildUser($payload);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * The following methods are required by the UserProvider interface
     * but are not used in a stateless JWT microservice.
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // No-op
    }

    public function retrieveByCredentials(array $credentials)
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // No-op
    }

    public function validatePayload(Payload $payload)
    {
        // *can switch to `passes`|`fails` for minmax but will need a boolean check in the caller
        Validator::make($payload->toArray(), [
            'id' => ['required', 'integer'],
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'roles' => ['present', 'array'],
            'roles.*' => ['string'],
        ])->validate();
    }

    public function buildUser(Payload $payload): User
    {
        return new User(
            id: $payload->get('id') ?? $payload->get('sub'),
            name: $payload->get('name'),
            email: $payload->get('email'),
            roles: $payload->get('roles') ?? [],
            sessionId: $payload->get('session_id'),
        );
    }
}
