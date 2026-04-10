<?php

namespace Tests\Unit\Controllers\Role;

use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleControllerAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function unprivileged_user_cannot_change_roles()
    {
        $this->withoutMiddleware();
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        /** @var Role $role */
        $role = Role::factory()->create();
        $user->roles()->save($role);

        $this->patch("/roles/update/{$role->external_id}")
            ->assertRedirect();
    }

    #[Test]
    public function unprivileged_user_cannot_access_roles()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        /** @var Role $role */
        $role = Role::factory()->create();
        $user->roles()->save($role);

        $this->get("/roles/{$role->external_id}")
            ->assertRedirect()
            ->assertSessionHas('flash_message_warning');
    }
}
