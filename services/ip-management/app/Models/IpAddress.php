<?php

namespace App\Models;

use App\Traits\HasCauserContext;
use Database\Factories\IpAddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

// TODO SoftDeletes?
#[Fillable(['user_id', 'ip_address', 'label', 'comment'])]
class IpAddress extends Model
{
    use HasCauserContext;

    /** @use HasFactory<IpAddressFactory> */
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

    public function scopeSearch(Builder $query, string $value): void
    {
        $query->where(function (Builder $query) use ($value) {
            $query->where('ip_address', 'like', "%{$value}%")
                ->orWhere('label', 'like', "%{$value}%")
                ->orWhere('comment', 'like', "%{$value}%");
        });
    }
}
