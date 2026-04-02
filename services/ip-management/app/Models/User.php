<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class User implements Authenticatable
{
    public $id;
    public $email;
    public $name;
    public $roles = []; // TODO placeholder
    public $permissions = []; // TODO placeholder

    public function __construct(array $attributes = [])
    {
        $this->id = Arr::get($attributes, 'id', Arr::get($attributes, 'sub'));
        $this->email = Arr::get($attributes, 'email');
        $this->name = Arr::get($attributes, 'name');
        $this->roles = Arr::get($attributes, 'roles', []); // TODO placeholder
        $this->permissions = Arr::get($attributes, 'permissions', []); // TODO placeholder
    }

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
}
