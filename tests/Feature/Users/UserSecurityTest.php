<?php

namespace Tests\Feature\Users;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('security')]
#[Group('user-controller')]
class UserSecurityTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected User $targetUser;

    protected User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->targetUser       = User::factory()->withRole('employee')->create();
        $this->user             = User::factory()->withRole('employee')->create();
        $this->unauthorizedUser = User::factory()->withRole('employee')->create();

        $this->actingAs($this->user);
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_authorized_user_can_edit_user()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::USER_UPDATE);

        /* Act */
        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));

        /* Assert */
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
        ]);
    }

    #[Test]
    public function it_unauthorized_user_cannot_edit_user()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);

        /* Act */
        $response = $this->json('GET', route('users.edit', $this->targetUser->external_id));

        /* Assert */
        $response->assertStatus(403);
        $response->assertJson(['message' => 'This action is unauthorized.']);
    }

    #[Test]
    public function it_authorized_user_can_update_user()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::USER_UPDATE);

        /* Act */
        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'       => 'Updated Name',
            'email'      => $this->targetUser->email,
            'department' => $this->targetUser->department()->first()->id,
            'role'       => $this->targetUser->roles->first()->id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'id'   => $this->targetUser->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function it_unauthorized_user_cannot_update_user()
    {
        /* Arrange */
        $this->actingAs($this->unauthorizedUser);
        $originalName = $this->targetUser->name;

        /* Act */
        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'       => 'Hacked Name',
            'email'      => $this->targetUser->email,
            'department' => $this->targetUser->department()->first()->id,
            'role'       => $this->targetUser->roles->first()->id,
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertEquals($originalName, $this->targetUser->refresh()->name);
    }

    #[Test]
    public function it_user_update_prevents_password_change_without_permission()
    {
        /* Arrange */
        $manager    = User::factory()->withRole('manager')->create();
        $this->user = $manager;
        $this->withPermissions(PermissionName::USER_UPDATE);
        $originalPassword = $this->targetUser->password;

        /* Act */
        $response = $this->json('PATCH', route('users.update', $this->targetUser->external_id), [
            'name'       => $this->targetUser->name,
            'email'      => $this->targetUser->email,
            'password'   => 'newpassword123',
            'department' => $this->targetUser->department()->first()->id,
            'role'       => $this->targetUser->roles->first()->id,
        ]);

        /* Assert */
        $response->assertStatus(302);
        $this->assertEquals($originalPassword, $this->targetUser->refresh()->password);
    }
}
