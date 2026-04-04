<?php

namespace App\Models;

use App\Traits\HasCauserContext;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

// TODO SoftDeletes?
#[Fillable(['user_id', 'ip_address', 'label', 'comment'])]
class IpAddress extends Model
{
    /** @use HasFactory<\Database\Factories\IpAddressFactory> */
    use HasFactory;
    use LogsActivity;
    use HasCauserContext;

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
}
