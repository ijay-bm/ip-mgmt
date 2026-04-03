<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

// TODO SoftDeletes?
#[Fillable(['user_id', 'ip_address', 'label', 'comment'])]
class IpAddress extends Model
{
    /** @use HasFactory<\Database\Factories\IpAddressFactory> */
    use HasFactory;
    use LogsActivity;

    protected static function booted(): void
    {
        static::creating(function (IpAddress $ipAddress) {
            if (auth()->check()) {
                $ipAddress->user_id = auth()->id();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = auth()->user();

        $activity->causer_type = null;
        $activity->causer_id = $user->getAuthIdentifier();

        $activity->properties = $activity->properties->merge([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => $user->sessionId,
        ]);
    }
}
