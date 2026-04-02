<?php

namespace Tests\Feature\Controllers\V1\IpAddressController;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private IpAddress $ipAddress;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User([
            'id' => 57,
            'email' => 'dovahkiin@dragonborn.com',
            'name' => 'Tiber Septim',
        ]);

        $this->ipAddress = IpAddress::factory()->create([
            'user_id' => $this->user->id,
            'ip_address' => '123.456.789.012',
            'label' => 'Cyrodiil',
        ]);
    }

    public function test_authenticated_user_can_get_ip_address(): void
    {
        $this->actingAs($this->user)
            ->getJson(route('ip-addresses.show', $this->ipAddress))
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'ip_address', 'label', 'comment', 'created_at', 'updated_at'],
            ]);
    }

    public function test_user_can_get_another_users_ip_address(): void
    {
        $ipAddressA = IpAddress::factory()->create([
            'user_id' => 99,
        ]);

        $this->actingAs($this->user)->getJson(route('ip-addresses.show', $ipAddressA))->assertOk();
    }

    public function test_unauthenticated_user_cant_get_ip_address(): void
    {
        $this->getJson(route('ip-addresses.show', $this->ipAddress), [])->assertUnauthorized();
    }
}
