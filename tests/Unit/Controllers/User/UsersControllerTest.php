<?php

namespace Tests\Unit\Controllers\User;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UsersControllerTest extends TestCase
{
//    use DatabaseTransactions, WithoutMiddleware;

    /**
     * @test
     * @link https://github.com/Bottelet/DaybydayCRM/pull/175
     */
    public function can_update_user_role()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->roles()->save(Role::skip(1)->first());

        /** @var Role $targetRole */
        $targetRole = Role::first();
        $this->json(
            'PATCH',
            route('users.update', $user->external_id),
            [
                'name'        => $user->name,
                'email'       => $user->email,
                'departments' => $user->department()->first()->id,
                'roles'       => $targetRole->id,
            ]
        )
            ->assertRedirect();


        self::assertEquals(
            [$targetRole->id],
            $user->roles()->get()->pluck('id')->toArray()
        );

    }
}
