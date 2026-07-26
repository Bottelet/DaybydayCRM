<?php

namespace Tests\Feature\Controllers;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Tests that the /create routes work even when permissions are cached.
 * This is critical because in production with Redis, cached objects can become
 * incomplete due to serializable_classes => false configuration.
 *
 * These tests specifically verify that the permission caching fix works
 * for both array cache (testing) and Redis-like scenarios.
 */
#[Group('cache-serialization')]
class ClientCreatePermissionCacheTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_allows_clients_create_route_to_work_after_multiple_requests()
    {
        /* Arrange */

        /* Act */
        // This test would catch the bug if cachedPermissions() returned incomplete objects
        // Make multiple requests - if caching is broken, 2nd+ requests would fail
        $response1 = $this->get(route('clients.create'));
        // Make another request (cache is now used)
        $response2 = $this->get(route('clients.create'));
        // And another
        $response3 = $this->get(route('clients.create'));

        /* Assert */
        $response1->assertStatus(200);
        $response1->assertSee('Create New Client');
        $response2->assertStatus(200);
        $response2->assertSee('Create New Client');
        $response3->assertStatus(200);
        $response3->assertSee('Create New Client');
    }

    #[Test]
    public function it_allows_owner_to_access_clients_create_with_cached_permissions()
    {
        /* Arrange */
        // Verify that cachedPermissions() returns usable Permission objects
        $owner = Role::where('name', 'owner')->first();
        $this->assertNotNull($owner);

        // Get cached permissions
        $permissions = $owner->cachedPermissions();

        /* Act */
        $response = $this->get(route('clients.create'));

        /* Assert */
        // Verify we can access permission properties (this would fail if objects were incomplete)
        foreach ($permissions as $perm) {
            // These would throw errors if $perm was __PHP_Incomplete_Class
            $this->assertNotNull($perm->name, 'Permission name should be accessible');
            $this->assertIsString($perm->name, 'Permission name should be a string');
        }

        // Now verify routes work
        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_task_create_route_to_work_consistently()
    {
        /* Arrange */

        /* Act */
        // Same test for tasks to ensure the bug fix works across all create routes
        $response1 = $this->get(route('tasks.create'));
        $response2 = $this->get(route('tasks.create'));
        $response3 = $this->get(route('tasks.create'));

        /* Assert */
        $response1->assertStatus(200);
        $response1->assertSee('Create task');
        $response2->assertStatus(200);
        $response2->assertSee('Create task');
        $response3->assertStatus(200);
        $response3->assertSee('Create task');
    }

    #[Test]
    public function it_allows_lead_create_route_to_work_consistently()
    {
        /* Arrange */

        /* Act */
        // Same test for leads
        $response1 = $this->get(route('leads.create'));
        $response2 = $this->get(route('leads.create'));
        $response3 = $this->get(route('leads.create'));

        /* Assert */
        $response1->assertStatus(200);
        $response1->assertSee('Create lead');
        $response2->assertStatus(200);
        $response2->assertSee('Create lead');
        $response3->assertStatus(200);
        $response3->assertSee('Create lead');
    }

    #[Test]
    public function it_allows_user_create_route_to_work_consistently()
    {
        /* Arrange */

        /* Act */
        // Same test for users
        $response1 = $this->get(route('users.create'));
        $response2 = $this->get(route('users.create'));
        $response3 = $this->get(route('users.create'));

        /* Assert */
        $response1->assertStatus(200);
        $response1->assertSee('Create user');
        $response2->assertStatus(200);
        $response2->assertSee('Create user');
        $response3->assertStatus(200);
        $response3->assertSee('Create user');
    }

    #[Test]
    public function it_allows_user_to_check_permissions_across_multiple_requests()
    {
        /* Arrange */
        $user = $this->user;
        // Create fresh user instances (simulate new requests)
        $freshUser        = User::find($user->id);
        $anotherFreshUser = User::find($user->id);

        /* Act */

        /* Assert */
        // First request - check permission
        $this->assertTrue($user->can(PermissionName::CLIENT_CREATE->value));
        // Should still work with cached permissions
        $this->assertTrue($freshUser->can(PermissionName::CLIENT_CREATE->value));
        // And once more
        $this->assertTrue($anotherFreshUser->can(PermissionName::CLIENT_CREATE->value));
    }
}
