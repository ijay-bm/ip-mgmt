<?php

namespace Tests\Feature\Controllers\V1\IpAddressController;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    private User $normalUser;
    private User $superAdminUser;

    private IpAddress $ipAddress;

    public function setUp(): void
    {
        parent::setUp();

        $this->normalUser = new User([
            'id' => 78,
        ]);

        $this->superAdminUser = new User([
            'id' => 105,
            'roles' => ['super-admin'],
        ]);

        $this->ipAddress = IpAddress::factory()->create();
    }

    public function test_normal_user_cant_delete_ip_address(): void
    {
        $this->actingAs($this->normalUser)
            ->deleteJson(route('ip-addresses.destroy', $this->ipAddress))
            ->assertForbidden();
    }

    public function test_super_admin_user_can_delete_ip_address(): void
    {
        $this->actingAs($this->superAdminUser)
            ->deleteJson(route('ip-addresses.destroy', $this->ipAddress))
            ->assertNoContent();
    }

    public function test_unauthenticated_user_cant_delete_ip_address(): void
    {
        $this->deleteJson(route('ip-addresses.destroy', $this->ipAddress), [])->assertUnauthorized();
    }
}
