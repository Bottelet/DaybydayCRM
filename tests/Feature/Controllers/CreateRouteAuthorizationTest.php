<?php

namespace Tests\Feature\Controllers;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

/**
 * Tests that the /create routes for all major domains are:
 *  - ACCESSIBLE to an authenticated owner (positive path)
 *  - BLOCKED (redirect + 403 JSON) for a user without the permission
 *
 * These tests exist to prevent regression of the bug where owners were
 * locked out of create pages due to missing permissions in the DB or
 * incomplete asOwner() setup.
 */
#[Group('create-authorization')]
class CreateRouteAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public static function createRoutes(): array
    {
        return [
            'clients.create' => ['clients.create'],
            'tasks.create'   => ['tasks.create'],
            'leads.create'   => ['leads.create'],
            'users.create'   => ['users.create'],
        ];
    }

    // ─────────────────────────────────────────────
    //  Owner can access every /create route
    // ─────────────────────────────────────────────

    #[Test]
    public function it_allows_owner_to_access_client_create_page()
    {
        $response = $this->get(route('clients.create'));
        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_owner_to_access_task_create_page()
    {
        $response = $this->get(route('tasks.create'));
        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_owner_to_access_lead_create_page()
    {
        $response = $this->get(route('leads.create'));
        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_owner_to_access_user_create_page()
    {
        $response = $this->get(route('users.create'));
        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────
    //  User without permission is redirected (web)
    // ─────────────────────────────────────────────

    #[Test]
    public function it_redirects_user_without_client_create_permission()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('clients.create'));

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function it_redirects_user_without_task_create_permission()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('tasks.create'));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function it_redirects_user_without_lead_create_permission()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('leads.create'));

        $response->assertRedirect(route('leads.index'));
        $response->assertSessionHas('flash_message_warning');
    }

    #[Test]
    public function it_redirects_user_without_user_create_permission()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('users.create'));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('flash_message_warning');
    }

    // ─────────────────────────────────────────────
    //  JSON requests without permission get 403
    // ─────────────────────────────────────────────

    #[Test]
    public function json_request_without_client_create_permission_gets_403()
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('clients.create'))
            ->assertForbidden()
            ->assertJsonFragment(['message' => __("You don't have permission to create a client")]);
    }

    #[Test]
    public function json_request_without_task_create_permission_gets_403()
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('tasks.create'))
            ->assertForbidden()
            ->assertJsonFragment(['message' => __("You don't have permission to create a task")]);
    }

    #[Test]
    public function json_request_without_lead_create_permission_gets_403()
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('leads.create'))
            ->assertForbidden()
            ->assertJsonFragment(['message' => __("You don't have permission to create a lead")]);
    }

    #[Test]
    public function json_request_without_user_create_permission_gets_403()
    {
        $this->actingAs(User::factory()->create());

        $this->getJson(route('users.create'))
            ->assertForbidden()
            ->assertJsonFragment(['message' => __("You don't have permission to create a user")]);
    }

    // ─────────────────────────────────────────────
    //  Single-permission grants work (not just owner)
    // ─────────────────────────────────────────────

    #[Test]
    public function it_allows_user_with_only_client_create_permission_to_access_client_create()
    {
        $this->withPermissions([PermissionName::CLIENT_CREATE]);

        $response = $this->get(route('clients.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_user_with_only_task_create_permission_to_access_task_create()
    {
        $this->withPermissions([PermissionName::TASK_CREATE]);

        $response = $this->get(route('tasks.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_user_with_only_lead_create_permission_to_access_lead_create()
    {
        $this->withPermissions([PermissionName::LEAD_CREATE]);

        $response = $this->get(route('leads.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function it_allows_user_with_only_user_create_permission_to_access_user_create()
    {
        $this->withPermissions([PermissionName::USER_CREATE]);

        $response = $this->get(route('users.create'));

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────
    //  Unauthenticated users are redirected to login
    // ─────────────────────────────────────────────

    #[Test]
    #[DataProvider('createRoutes')]
    public function it_redirects_unauthenticated_user_to_login(string $routeName)
    {
        // Explicitly log out so there is no authenticated user
        auth()->logout();

        $response = $this->get(route($routeName));

        $response->assertRedirect(route('login'));
    }
}
