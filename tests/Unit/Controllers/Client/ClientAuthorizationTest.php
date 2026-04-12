<?php

namespace Tests\Unit\Controllers\Client;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\User;
use App\Enums\PermissionName;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('authorization-fix')]
class ClientAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();

        // Create users
        $this->userWithPermission = User::factory()->create();
        $this->userWithoutPermission = User::factory()->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_client_delete_permission_can_delete_client()
    {
        $this->user = $this->userWithPermission;
        $this->withPermissions(PermissionName::CLIENT_DELETE);

        $response = $this->delete(route('clients.destroy', $this->client->external_id));

        $response->assertStatus(302); // Redirect on success
        $this->assertSoftDeleted('clients', ['id' => $this->client->id]);
    }

    #[Test]
    public function user_without_client_delete_permission_cannot_delete_client()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->delete(route('clients.destroy', $this->client->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('clients', ['id' => $this->client->id, 'deleted_at' => null]);
    }
}
