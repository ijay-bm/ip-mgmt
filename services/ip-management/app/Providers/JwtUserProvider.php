<?php

namespace App\Providers;

use App\Models\User;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtUserProvider implements UserProvider
{
    /**
     * Called by JWT-Auth to find the user by the 'sub' claim.
     */
    public function retrieveById($identifier)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();

            return new User([
                'id' => $identifier,
                'email' => $payload->get('email'),
                'name' => $payload->get('name'),
                'roles' => $payload->get('roles') ?? [],
            ]);
        } catch (Exception $e) {
            return new User(['id' => $identifier]);
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
}
