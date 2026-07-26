<?php

namespace Tests\Feature\Roles;

use App\Http\Controllers\RolesController;
use App\Models\Role;
use App\Models\User;
use App\Services\Role\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

#[CoversClass(RolesController::class)]
class RoleTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_unprivileged_user_cannot_change_roles()
    {
        /* Arrange */
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Role $role */
        $role = Role::factory()->create();
        $user->roles()->save($role);
        $user = $user->fresh();
        $this->actingAs($user);
        $originalRoleName = $role->name;

        /* Act: gated by RedirectIfNotAdmin middleware (user.is.admin alias). */
        $response = $this->from(route('dashboard'))
            ->patch("/roles/update/{$role->external_id}", [
                'name'         => 'hacked-role',
                'display_name' => 'Hacked Role',
            ]);

        /* Assert */
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('flash_message_warning', __('Only Allowed for admins'));
        $this->assertEquals($originalRoleName, $role->refresh()->name);
        $this->assertDatabaseMissing('roles', [
            'id'   => $role->id,
            'name' => 'hacked-role',
        ]);
    }

    #[Test]
    public function it_unprivileged_user_cannot_access_roles()
    {
        /* Arrange */
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        /** @var Role $role */
        $role = Role::factory()->create();
        $user->roles()->save($role);

        /* Act: gated by RedirectIfNotAdmin middleware (user.is.admin alias). */
        $response = $this->from(route('dashboard'))->get("/roles/{$role->external_id}");

        /* Assert */
        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('flash_message_warning', __('Only Allowed for admins'));
    }

    #[Test]
    public function it_returns_validation_errors_when_description_is_missing(): void
    {
        /* Arrange */
        $this->asAdmin();

        /* Act */
        $response = $this->from(route('roles.create'))->post(route('roles.store'), [
            'name' => 'qa-role',
            // description intentionally missing
        ]);

        /* Assert */
        $response->assertRedirect(route('roles.create'));
        $response->assertSessionHasErrors('description');
        $this->assertDatabaseMissing('roles', ['name' => 'qa-role']);
    }

    #[Test]
    public function it_denies_role_creation_for_user_without_administrator_or_owner_role(): void
    {
        /* Arrange: roles.store is now gated by the user.is.admin middleware (same as
         * every other action in this controller), which runs before route model
         * binding resolves StoreRoleRequest, so it rejects first. */
        $user = User::factory()->withRole('employee')->create();
        $this->actingAs($user);

        /* Act */
        $response = $this->from(route('dashboard'))->post(route('roles.store'), [
            'name'        => 'sneaky-role',
            'description' => 'Should not be created',
        ]);

        /* Assert: RedirectIfNotAdmin rejects with its own flash message and
         * redirect()->back(), distinct from StoreRoleRequest::authorize()'s
         * AuthorizationException path (which would only fire now if this
         * middleware were ever removed again). */
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('flash_message_warning', 'Only allowed for admins');
        $this->assertDatabaseMissing('roles', ['name' => 'sneaky-role']);
    }

    #[Test]
    public function it_returns_web_error_when_role_creation_throws_exception()
    {
        /* Arrange */
        $this->asAdmin();
        $this->bindFailingRoleService();

        /* Act */
        $response = $this->from(route('roles.create'))->post(route('roles.store'), [
            'name'        => 'qa-role',
            'description' => 'QA role',
        ]);

        /* Assert */
        $response->assertRedirect(route('roles.create'));
        $response->assertSessionHasErrors(['role']);
    }

    #[Test]
    public function it_returns_json_error_when_role_creation_throws_exception()
    {
        /* Arrange */
        $this->asAdmin();
        $this->bindFailingRoleService();

        /* Act */
        $response = $this->post(route('roles.store'), [
            'name'        => 'qa-role',
            'description' => 'QA role',
        ], ['Accept' => 'application/json']);

        /* Assert */
        $response->assertStatus(500);
        $response->assertJson([
            'message' => __('Role could not be created. Please try again.'),
        ]);
    }

    private function bindFailingRoleService(): void
    {
        $this->app->instance(RoleService::class, new class () extends RoleService {
            public function create(array $validated): Role
            {
                throw new RuntimeException('Simulated role create failure');
            }
        });
    }
}
