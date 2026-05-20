<?php

namespace Tests\Feature\Controllers\Role;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

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
    public function it_returns_404_when_updating_a_role_with_an_invalid_external_id()
    {
        /* Act */
        $response = $this->patch('/roles/update/invalid-external-id', [
            'permissions' => [],
        ]);

        /* Assert */
        $response->assertNotFound();
    }
}
