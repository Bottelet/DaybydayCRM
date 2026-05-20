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
class RoleControllerTest extends AbstractTestCase
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

        /* Act */
        $response = $this->patch("/roles/update/{$role->external_id}", [
            'name'         => 'hacked-role',
            'display_name' => 'Hacked Role',
        ]);

        /* Assert */
        $response->assertRedirect();
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

        /* Act */
        $response = $this->get("/roles/{$role->external_id}");

        /* Assert */
        $response->assertRedirect()
            ->assertSessionHas('flash_message_warning');
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
        $response = $this->json('POST', route('roles.store'), [
            'name'        => 'qa-role',
            'description' => 'QA role',
        ]);

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
