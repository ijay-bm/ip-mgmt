<?php

namespace App\Traits;

use Spatie\Activitylog\Models\Activity;

trait HasCauserContext
{
    /**
     * *Will fail on factory created models
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $user = auth()->user();

        // *Should I add an early return if no user or use null coalescing?

        $activity->causer_type = $user->type;
        $activity->causer_id = $user->id;

        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => $user->sessionId,
            'causer_name' => $user->name,
            'causer_email' => $user->email,
            'causer_roles' => $user->roles,
        ]);
    }
}
