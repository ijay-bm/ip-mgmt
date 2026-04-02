<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'ip_address', 'label', 'comment'])]
class IpAddress extends Model
{
    /** @use HasFactory<\Database\Factories\IpAddressFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (IpAddress $ipAddress) {
            if (auth()->check()) {
                $ipAddress->user_id = auth()->id();
            }
        });
    }
}
