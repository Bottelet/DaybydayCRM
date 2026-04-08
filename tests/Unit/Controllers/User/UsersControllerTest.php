<?php

namespace Tests\Unit\Controllers\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;

    #[Test]
    #[Group('junie_repaired')]
    #[Group('security')]
    public function owner_can_update_user_role()
    {
        /** @var Role $targetRole */
        $targetRole = Role::first();

        $this->json(
            'PATCH',
            route('users.update', $this->user->external_id),
            [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'departments' => $this->user->department()->first()->id,
                'roles' => $targetRole->id,
            ])->assertRedirect();

        $this->assertEquals(
            [$targetRole->id],
            $this->user->roles()->get()->pluck('id')->toArray()
        );
    }

    #[Test]
    public function only_owner_role_can_update_user()
    {
        /** @var User $manager */
        $manager = factory(User::class)->create();
        $manager->roles()->save(Role::whereName('manager')->first());
        $this->actingAs($manager);

        $this->json(
            'PATCH',
            route('users.update', 1)
        )->assertForbidden();
    }
}
