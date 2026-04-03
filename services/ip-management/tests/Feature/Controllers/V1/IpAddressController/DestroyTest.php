<?php

namespace Tests\Feature\Controllers\V1\IpAddressController;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    private string $normalUserToken;

    private User $superAdminUser;
    private string $superAdminUserToken;

    private IpAddress $ipAddress;

    public function setUp(): void
    {
        parent::setUp();

        $this->normalUserToken = $this->mintNormalUserToken();

        $this->superAdminUser = $this->makeSuperAdminUser();
        $this->superAdminUserToken = $this->mintToken($this->superAdminUser);

        activity()->withoutLogs(function () {
            $this->ipAddress = IpAddress::factory()->create();
        });
    }

    public function test_normal_user_cant_delete_ip_address(): void
    {
        $this->withToken($this->normalUserToken)
            ->deleteJson(route('ip-addresses.destroy', $this->ipAddress))
            ->assertForbidden();
    }

    public function test_super_admin_user_can_delete_ip_address(): void
    {
        $this->withToken($this->superAdminUserToken)
            ->deleteJson(route('ip-addresses.destroy', $this->ipAddress))
            ->assertNoContent();

        $this->assertDatabaseMissing('ip_addresses', [
            'id' => $this->ipAddress->id,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->superAdminUser->id,
            'event' => 'deleted',
            'subject_type' => IpAddress::class,
            'subject_id' => $this->ipAddress->id,
            'properties->session_id' => $this->superAdminUser->sessionId,
            'properties->old->user_id' => $this->ipAddress->user_id,
            'properties->old->ip_address' => $this->ipAddress->ip_address,
            'properties->old->label' => $this->ipAddress->label,
            'properties->old->comment' => $this->ipAddress->comment,
        ]);
    }

    public function test_unauthenticated_user_cant_delete_ip_address(): void
    {
        $this->deleteJson(route('ip-addresses.destroy', $this->ipAddress), [])->assertUnauthorized();
    }
}
