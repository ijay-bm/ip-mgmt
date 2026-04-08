<?php

namespace Tests\Feature\Controllers\V1\IpAddressController;

use App\Models\IpAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private string $userToken;

    private IpAddress $ipAddress;

    public function setUp(): void
    {
        parent::setUp();

        $this->userToken = $this->mintNormalUserToken([
            'id' => 57,
            'email' => 'dovahkiin@dragonborn.com',
            'name' => 'Tiber Septim',
        ]);

        activity()->withoutLogs(function () {
            $this->ipAddress = IpAddress::factory()->create([
                'user_id' => 57,
                'ip_address' => '123.456.789.012',
                'label' => 'Cyrodiil',
            ]);
        });
    }

    public function test_authenticated_user_can_get_ip_address(): void
    {
        $this->withToken($this->userToken)
            ->getJson(route('ip-addresses.show', $this->ipAddress))
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'ip_address', 'label', 'comment', 'is_owner', 'created_at', 'updated_at'],
            ]);
    }

    public function test_user_can_get_another_users_ip_address(): void
    {
        // $ipAddressA = null;
        $ipAddressA = activity()->withoutLogs(
            fn() => IpAddress::factory()->create([
                'user_id' => 99,
            ]),
        );

        $this->withToken($this->userToken)->getJson(route('ip-addresses.show', $ipAddressA))->assertOk();
    }

    public function test_unauthenticated_user_cant_get_ip_address(): void
    {
        $this->getJson(route('ip-addresses.show', $this->ipAddress), [])->assertUnauthorized();
    }
}
