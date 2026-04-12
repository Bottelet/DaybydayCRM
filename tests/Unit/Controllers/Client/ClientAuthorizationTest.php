<?php

namespace Tests\Unit\Controllers\Client;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\User;
use App\Enums\PermissionName;
use Carbon\Carbon;
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

        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->client = Client::factory()->create();
        $this->userWithPermission = User::factory()->create();
        $this->userWithoutPermission = User::factory()->create();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function user_with_client_delete_permission_can_delete_client()
    {
        /** Arrange */
        $this->user = $this->userWithPermission;
        $this->withPermissions(PermissionName::CLIENT_DELETE);

        /** Act */
        $response = $this->delete(route('clients.destroy', $this->client->external_id));

        /** Assert */
        $response->assertStatus(302);
        $this->assertSoftDeleted('clients', ['id' => $this->client->id]);
    }

    // endregion

    // region failure_path

    #[Test]
    public function user_without_client_delete_permission_cannot_delete_client()
    {
        /** Arrange */
        $this->actingAs($this->userWithoutPermission);

        /** Act */
        $response = $this->delete(route('clients.destroy', $this->client->external_id));

        /** Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('clients', ['id' => $this->client->id, 'deleted_at' => null]);
    }

    // endregion
}
