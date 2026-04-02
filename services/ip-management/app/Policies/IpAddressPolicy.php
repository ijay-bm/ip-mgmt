<?php

namespace App\Policies;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IpAddressPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IpAddress $ipAddress): Response
    {
        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        return (int) $user->id === (int) $ipAddress->user_id
            ? Response::allow()
            : Response::deny(__('ip_address.not_owner'));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IpAddress $ipAddress): Response
    {
        return $user->isSuperAdmin() ? Response::allow() : Response::deny(__('ip_address.cannot_delete'));
    }
}
