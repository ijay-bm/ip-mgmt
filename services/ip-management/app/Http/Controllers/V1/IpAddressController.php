<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIpAddressRequest;
use App\Http\Requests\UpdateIpAddressRequest;
use App\Http\Resources\IpAddressResource;
use App\Models\IpAddress;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class IpAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return IpAddressResource::collection(IpAddress::paginate($request->input('perPage', 10)));
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
