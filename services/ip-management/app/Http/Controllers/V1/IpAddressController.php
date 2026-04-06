<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIpAddressRequest;
use App\Http\Requests\UpdateIpAddressRequest;
use App\Http\Resources\IpAddressResource;
use App\Models\IpAddress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IpAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $ipAddresses = QueryBuilder::for(IpAddress::class)
            ->allowedFilters(
                AllowedFilter::callback('search', fn (Builder $query, $value) => $query->search($value)),

                AllowedFilter::callback('created_at_from', function ($query, $value) {
                    $query->where('created_at', '>=', $value);
                }),
                AllowedFilter::callback('created_at_to', function ($query, $value) {
                    $query->where('created_at', '<=', $value);
                }),
            )
            ->allowedSorts(
                'id',
                'user_id',
                'ip_address',
                'label',
                'comment',
                'created_at',
                'updated_at',
            )
            ->defaultSort('-created_at')
            ->paginate($request->input('per_page', 10));

        return IpAddressResource::collection($ipAddresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIpAddressRequest $request): IpAddressResource
    {
        $ipAddress = IpAddress::create($request->validated());

        return new IpAddressResource($ipAddress);
    }

    /**
     * Display the specified resource.
     */
    public function show(IpAddress $ipAddress): IpAddressResource
    {
        return new IpAddressResource($ipAddress);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIpAddressRequest $request, IpAddress $ipAddress): IpAddressResource
    {
        $ipAddress->update($request->validated());

        return new IpAddressResource($ipAddress);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IpAddress $ipAddress)
    {
        Gate::authorize('delete', $ipAddress);

        $ipAddress->delete();

        return response()->noContent();
    }
}
