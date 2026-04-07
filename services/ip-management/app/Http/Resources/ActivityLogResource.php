<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Activitylog\Models\Activity;

/**
 * @mixin Activity
 */
class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'description' => $this->description,

            'causer' => [
                'id' => $this->causer_id,
                'type' => $this->causer_type,
                'name' => $this->getExtraProperty('causer_name'),
                'email' => $this->getExtraProperty('causer_email'),
                'roles' => $this->getExtraProperty('causer_roles') ?? [],
            ],

            'session' => [
                'id' => $this->getExtraProperty('session_id'),
                'ip' => $this->getExtraProperty('ip'),
                'user_agent' => $this->getExtraProperty('user_agent'),
            ],

            'changes' => $this->changes,

            'created_at' => $this->created_at,
        ];
    }
}
