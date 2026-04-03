<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;

readonly class User implements Authenticatable
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public array $roles,
        public string $sessionId,
    ) {}

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPasswordName()
    {
        return '';
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return '';
    }

    public function isSuperAdmin(): bool
    {
        return in_array('super-admin', $this->roles);
    }
}
