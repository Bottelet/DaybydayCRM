<?php

namespace Tests\Feature\Integrations;

use App\Models\Integration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class IntegrationsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_persists_only_validated_fields_and_returns_ok_when_storing(): void
    {
        // Arrange
        $payload = [
            'api_type'      => 'billing',
            'name'          => 'Xero',
            'client_id'     => 'client',
            'client_secret' => 'secret',
            'api_key'       => 'key',
            'is_admin'      => true,
        ];

        // Act
        $response = $this->post(route('integrations.store'), $payload);

        // Assert
        $response->assertOk();
        $this->assertDatabaseHas('integrations', [
            'api_type'  => 'billing',
            'name'      => 'Xero',
            'client_id' => 'client',
        ]);

        $this->assertArrayNotHasKey('is_admin', Integration::query()->where('api_type', 'billing')->first()->getAttributes());
    }
}
