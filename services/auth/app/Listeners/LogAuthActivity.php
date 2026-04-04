<?php

namespace App\Listeners;

use App\Events\AuthLoggedIn;
use App\Events\AuthLoggedOut;
use Spatie\Activitylog\Facades\Activity;

class LogAuthActivity
{
    /**
     * Handle the event.
     */
    public function handle(AuthLoggedIn|AuthLoggedOut $event): void
    {
        $isLogin = $event instanceof AuthLoggedIn;

        Activity::causedBy($event->user)
            ->performedOn($event->user)
            ->withProperties([
                'ip' => $event->ip,
                'user_agent' => $event->userAgent,
                'session_id' => $event->sessionId,
                'causer_name' => $event->user->name,
                'causer_email' => $event->user->email,
                'causer_roles' => $event->user->getRoleNames()->toArray(),
            ])
            ->event($isLogin ? 'login' : 'logout')
            ->log($isLogin ? 'User logged in' : 'User logged out');
    }
}
