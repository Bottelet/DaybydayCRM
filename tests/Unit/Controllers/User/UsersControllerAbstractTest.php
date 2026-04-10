<?php

namespace Tests\Unit\Controllers\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsersControllerAbstractTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    #[Group('junie_repaired')]
    public function owner_can_update_user_role()
    {
        $this->asOwner();
        Cache::tags('role_user')->flush();

        // Create a different user to update, because we can't demote the only owner
        $targetUser = User::factory()->withRole('employee')->create();

        /** @var Role $targetRole */
        $targetRole = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager', 'description' => 'Manager role']);

        $this->json(
            'PATCH',
            route('users.update', $targetUser->external_id),
            [
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'departments' => $targetUser->department()->first()->id,
                'roles' => $targetRole->id,
            ]
        )->assertRedirect();

        $this->assertEquals(
            [$targetRole->id],
            $targetUser->roles()->get()->pluck('id')->toArray()
        );
    }

    #[Test]
    public function only_owner_role_can_update_user()
    {
        /** @var User $manager */
        $manager = User::factory()->withRole('manager')->create();
        $this->actingAs($manager);

        $this->json(
            'PATCH',
            route('users.update', 1)
        )->assertForbidden();
    }
}
