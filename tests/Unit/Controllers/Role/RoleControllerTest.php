<?php

namespace Tests\Unit\Controllers\Role;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
//    use DatabaseTransactions, WithoutMiddleware;

    /** @test */
    public function unprivileged_user_cannot_change_roles()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $this->actingAs($user);

        /** @var Role $role */
        $role = factory(Role::class)->create();
        $user->roles()->save($role);

        $this->get("/roles/{$role->external_id}")
            ->assertRedirect()
            ->assertSessionHas('flash_message_warning');

        $this->patch("/roles/update/{$role->external_id}")
            ->assertRedirect()
            ->assertSessionHas('flash_message_warning');
    }
}