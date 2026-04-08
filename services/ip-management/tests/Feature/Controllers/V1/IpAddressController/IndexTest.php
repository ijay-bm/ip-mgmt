<?php

namespace Tests\Feature\Controllers\V1\IpAddressController;

use App\Models\IpAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private string $userToken;

    public function setUp(): void
    {
        parent::setUp();

        $this->userToken = $this->mintNormalUserToken();

        activity()->withoutLogs(function () {
            IpAddress::factory()->count(5)->create();
        });
    }

    public function test_authenticated_user_can_get_ip_addresses(): void
    {
        $this->withToken($this->userToken)
            ->getJson(route('ip-addresses.index'))
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_unauthenticated_user_cant_get_ip_addresses(): void
    {
        $this->getJson(route('ip-addresses.index'))->assertUnauthorized();
    }

    public function test_payload_has_correct_structure(): void
    {
        $this->withToken($this->userToken)
            ->getJson(route('ip-addresses.index'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'ip_address', 'label', 'comment', 'is_owner', 'created_at', 'updated_at'],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links' => [
                        '*' => ['url', 'label', 'page', 'active'],
                    ],
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }
}
