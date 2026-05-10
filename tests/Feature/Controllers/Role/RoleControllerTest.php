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
        $this->withoutMiddleware();
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        /** @var Role $role */
        $role = Role::factory()->create();
        $user->roles()->save($role);

        /* Act */
        $response = $this->patch("/roles/update/{$role->external_id}");

        /* Assert */
        $response->assertRedirect();
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
}
