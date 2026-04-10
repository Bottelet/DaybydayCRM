<?php

namespace Tests\Unit\Controllers\Client;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

#[Group('authorization-fix')]
class ClientAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::factory()->create();

        // Create or get the client-delete permission
        $deletePermission = Permission::firstOrCreate(
            ['name' => 'client-delete'],
            [
                'display_name' => 'Delete client',
                'description' => 'Permission to delete client',
                'grouping' => 'client',
                'external_id' => Str::uuid()->toString(),
            ]
        );

        // Create role with client-delete permission
        $roleWithPermission = Role::create([
            'name' => 'client-deleter',
            'display_name' => 'Client Deleter',
            'description' => 'Can delete clients',
            'external_id' => Str::uuid()->toString(),
        ]);
        $roleWithPermission->attachPermission($deletePermission);

        // Create role without client-delete permission
        $roleWithoutPermission = Role::create([
            'name' => 'client-viewer',
            'display_name' => 'Client Viewer',
            'description' => 'Cannot delete clients',
            'external_id' => Str::uuid()->toString(),
        ]);

        // Create users
        $this->userWithPermission = User::factory()->create();
        $this->userWithPermission->attachRole($roleWithPermission);

        $this->userWithoutPermission = User::factory()->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_client_delete_permission_can_delete_client()
    {
        $this->actingAs($this->userWithPermission);

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
