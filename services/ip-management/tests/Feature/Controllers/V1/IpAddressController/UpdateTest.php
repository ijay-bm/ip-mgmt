<?php

namespace Tests\Feature\Controllers\V1\IpAddressController;

use App\Models\IpAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    private string $userToken;
    private User $user;

    private IpAddress $ipAddress;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = $this->makeUser([
            'id' => 57,
            'email' => 'geralt@witcher.com',
            'name' => 'Geralt of Rivia',
        ]);
        $this->userToken = $this->mintToken($this->user);

        activity()->withoutLogs(function () {
            $this->ipAddress = IpAddress::factory()->create([
                'user_id' => 57,
                'ip_address' => '2001:db8::ff00:42:8329',
                'label' => 'The Golden Sturgeon',
            ]);
        });
    }

    public function test_authenticated_user_can_update_ip_address(): void
    {
        $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $this->ipAddress), [
                'label' => 'Corvo Bianco',
                'comment' => 'Winery',
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'ip_address', 'label', 'comment', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('ip_addresses', [
            'id' => $this->ipAddress->id,
            'user_id' => 57,
            'ip_address' => $this->ipAddress->ip_address,
            'label' => 'Corvo Bianco',
            'comment' => 'Winery',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->user->id,
            'event' => 'updated',
            'subject_type' => IpAddress::class,
            'subject_id' => $this->ipAddress->id,
            'properties->session_id' => $this->user->sessionId,
            'properties->old->label' => $this->ipAddress->label,
            'properties->old->comment' => $this->ipAddress->comment,
            'properties->attributes->label' => 'Corvo Bianco',
            'properties->attributes->comment' => 'Winery',
        ]);
    }

    public function test_user_cant_update_another_users_ip_address(): void
    {
        $ipAddressA = activity()->withoutLogs(fn() => IpAddress::factory()->create(['user_id' => 99]));

        $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $ipAddressA), [
                'label' => 'Chateau',
            ])
            ->assertForbidden();
    }

    public function test_user_updating_ip_address_field_does_nothing(): void
    {
        $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $this->ipAddress), [
                'ip_address' => fake()->ipv4(),
                'label' => $this->ipAddress->label,
            ])
            ->assertOk();

        $this->assertDatabaseHas('ip_addresses', [
            'id' => $this->ipAddress->id,
            'user_id' => 57,
            'ip_address' => $this->ipAddress->ip_address,
            'label' => $this->ipAddress->label,
            'comment' => $this->ipAddress->comment,
        ]);
    }

    public function test_unauthenticated_user_cant_update_ip_address(): void
    {
        $this->putJson(route('ip-addresses.update', $this->ipAddress), [])->assertUnauthorized();
    }

    public function test_throws_validation_error_when_lebel_is_invalid(): void
    {
        $response = $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $this->ipAddress), [
                'ip_address' => fake()->ipv4(),
            ])
            ->assertJsonValidationErrors('label');

        $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $this->ipAddress), [
                'ip_address' => fake()->ipv4(),
                'label' => null,
            ])
            ->assertJsonValidationErrors('label');

        $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $this->ipAddress), [
                'ip_address' => fake()->ipv4(),
                'label' => '',
            ])
            ->assertJsonValidationErrors('label');

        $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $this->ipAddress), [
                'ip_address' => fake()->ipv4(),
                'label' => fake()->words(101),
            ])
            ->assertJsonValidationErrors('label');
    }

    public function test_throws_validation_error_when_comment_is_invalid(): void
    {
        $this->withToken($this->userToken)
            ->putJson(route('ip-addresses.update', $this->ipAddress), [
                'ip_address' => fake()->ipv4(),
                'label' => fake()->words(),
                'comment' => fake()->words(101),
            ])
            ->assertJsonValidationErrors('label');
    }
}
