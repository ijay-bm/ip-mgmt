<?php

namespace App\Http\Controllers\V1\IpAddress;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\IpAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class AuditLogIndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $logs = QueryBuilder::for(Activity::where('subject_type', IpAddress::class))
            ->allowedFilters(
                AllowedFilter::callback(
                    'search',
                    fn(Builder $query, $value) => $query->where(
                        fn(Builder $query) => $query
                            ->orWhere('description', 'like', "%{$value}%")
                            ->orWhere('properties->causer_name', 'like', "%{$value}%")
                            ->orWhere('properties->causer_email', 'like', "%{$value}%")
                            ->orWhere('properties->ip_address', 'like', "%{$value}%")
                            ->orWhere('properties->label', 'like', "%{$value}%"),
                    ),
                ),

                AllowedFilter::exact('event'),

                AllowedFilter::exact('description'),

                AllowedFilter::exact('causer_id'),

                AllowedFilter::exact('causer_id', 'causer_id'),

                AllowedFilter::exact('causer_type', 'causer_type'),

                AllowedFilter::callback('causer_name', function ($query, $value) {
                    if (is_array($value)) {
                        $query->whereIn('properties->causer_name', $value);
                    } else {
                        $query->where('properties->causer_name', 'LIKE', "%{$value}%");
                    }
                }),

                AllowedFilter::callback('causer_email', function ($query, $value) {
                    if (is_array($value)) {
                        $query->whereIn('properties->causer_email', $value);
                    } else {
                        $query->where('properties->causer_email', 'LIKE', "%{$value}%");
                    }
                }),

                AllowedFilter::callback('causer_roles', function ($query, $value) {
                    $query->whereRaw('JSON_CONTAINS(properties, ?, "$.causer_roles")', [
                        json_encode(is_array($value) ? $value : [$value]),
                    ]);
                }),

                AllowedFilter::callback('session_id', function ($query, $value) {
                    if (is_array($value)) {
                        $query->whereIn('properties->session_id', $value);
                    } else {
                        $query->where('properties->session_id', $value);
                    }
                }),

                AllowedFilter::callback('ip', function ($query, $value) {
                    if (is_array($value)) {
                        $query->whereIn('properties->ip', $value);
                    } else {
                        $query->where('properties->ip', $value);
                    }
                }),

                AllowedFilter::callback('user_agent', function ($query, $value) {
                    if (is_array($value)) {
                        $query->whereIn('properties->user_agent', $value);
                    } else {
                        $query->where('properties->user_agent', 'LIKE', "%{$value}%");
                    }
                }),

                AllowedFilter::callback('created_at_from', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('created_at_to', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            )
            ->allowedSorts(
                'created_at',
                'event',
                'causer_id',
                AllowedSort::callback(
                    'causer_name',
                    fn($query, $direction) => $query->orderBy('properties->causer_name', $direction ? 'DESC' : 'ASC'),
                ),
                AllowedSort::callback(
                    'ip',
                    fn($query, $direction) => $query->orderBy('properties->ip', $direction ? 'DESC' : 'ASC'),
                ),
                AllowedSort::callback(
                    'user_agent',
                    fn($query, $direction) => $query->orderBy('properties->user_agent', $direction ? 'DESC' : 'ASC'),
                ),
                AllowedSort::callback(
                    'session_id',
                    fn($query, $direction) => $query->orderBy('properties->session_id', $direction ? 'DESC' : 'ASC'),
                ),
            )
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 10));

        return ActivityLogResource::collection($logs);
    }
}
