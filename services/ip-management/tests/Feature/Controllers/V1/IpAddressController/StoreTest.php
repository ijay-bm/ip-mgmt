<?php

namespace Tests\Feature\Controllers\V1\IpAddressController;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User([
            'id' => 2,
            'email' => 'gandalf@ring.com',
            'name' => 'Gandalf the Grey',
        ]);

        IpAddress::factory()->create(['ip_address' => '127.0.0.1']);
    }

    public function test_authenticated_user_can_store_ip_address(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), [
                'ip_address' => '127.0.0.2',
                'label' => 'The Shire',
                'comment' => 'Home of the hobbits',
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'ip_address', 'label', 'comment', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('ip_addresses', [
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.2',
            'label' => 'The Shire',
            'comment' => 'Home of the hobbits',
        ]);
    }

    public function test_authenticated_user_can_store_ipv6_address(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), [
                'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                'label' => 'Mordor',
            ])
            ->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'ip_address', 'label', 'comment', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('ip_addresses', [
            'user_id' => $this->user->id,
            'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'label' => 'Mordor',
        ]);
    }

    public function test_unauthenticated_user_cant_store_ip_address(): void
    {
        $this->postJson(route('ip-addresses.store'), [])->assertUnauthorized();
    }

    public function test_throws_validation_error_when_ip_address_is_invalid(): void
    {
        $payload = [
            'label' => 'Middle Earth',
            'comment' => 'A big place',
        ];

        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), $payload)
            ->assertJsonValidationErrors('ip_address');

        $this->actingAs($this->user)
            ->postJson(
                route('ip-addresses.store'),
                array_merge($payload, [
                    'ip_address' => '',
                ]),
            )
            ->assertJsonValidationErrors('ip_address');

        $this->actingAs($this->user)
            ->postJson(
                route('ip-addresses.store'),
                array_merge($payload, [
                    'ip_address' => null,
                ]),
            )
            ->assertJsonValidationErrors('ip_address');

        $this->actingAs($this->user)
            ->postJson(
                route('ip-addresses.store'),
                array_merge($payload, [
                    'ip_address' => 'not_an_ip_address',
                ]),
            )
            ->assertJsonValidationErrors('ip_address');

        $this->actingAs($this->user)
            ->postJson(
                route('ip-addresses.store'),
                array_merge($payload, [
                    'ip_address' => '122.122.122',
                ]),
            )
            ->assertJsonValidationErrors('ip_address');

        $this->actingAs($this->user)
            ->postJson(
                route('ip-addresses.store'),
                array_merge($payload, [
                    'ip_address' => '127.0.0.1',
                ]),
            )
            ->assertJsonValidationErrors('ip_address');
    }

    public function test_throws_validation_error_when_lebel_is_invalid(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), [
                'ip_address' => fake()->ipv4(),
            ])
            ->assertJsonValidationErrors('label');

        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), [
                'ip_address' => fake()->ipv4(),
                'label' => null,
            ])
            ->assertJsonValidationErrors('label');

        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), [
                'ip_address' => fake()->ipv4(),
                'label' => '',
            ])
            ->assertJsonValidationErrors('label');

        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), [
                'ip_address' => fake()->ipv4(),
                'label' => fake()->words(101),
            ])
            ->assertJsonValidationErrors('label');
    }

    public function test_throws_validation_error_when_comment_is_invalid(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('ip-addresses.store'), [
                'ip_address' => fake()->ipv4(),
                'label' => fake()->words(),
                'comment' => fake()->words(101),
            ])
            ->assertJsonValidationErrors('label');
    }
}
