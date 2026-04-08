<?php

namespace Tests\Unit\Controllers\User;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('authorization-fix')]
class UserAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    private User $targetUser;

    private User $userWithPermission;

    private User $userWithoutPermission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->targetUser = factory(User::class)->create();

        // Create role with user-delete permission
        $roleWithPermission = Role::create([
            'name' => 'user-deleter',
            'display_name' => 'User Deleter',
            'description' => 'Can delete users',
        ]);
        $deletePermission = Permission::where('name', 'user-delete')->first();
        $roleWithPermission->attachPermission($deletePermission);

        // Create role without user-delete permission
        $roleWithoutPermission = Role::create([
            'name' => 'user-viewer',
            'display_name' => 'User Viewer',
            'description' => 'Cannot delete users',
        ]);

        // Create users
        $this->userWithPermission = factory(User::class)->create();
        $this->userWithPermission->attachRole($roleWithPermission);

        $this->userWithoutPermission = factory(User::class)->create();
        $this->userWithoutPermission->attachRole($roleWithoutPermission);

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function user_with_user_delete_permission_can_delete_user()
    {
        $this->actingAs($this->userWithPermission);

        $response = $this->json('DELETE', route('users.destroy', $this->targetUser->external_id));

        $response->assertStatus(302); // Redirect on success
        $this->assertSoftDeleted('users', ['id' => $this->targetUser->id]);
    }

    #[Test]
    public function user_without_user_delete_permission_cannot_delete_user()
    {
        $this->actingAs($this->userWithoutPermission);

        $response = $this->json('DELETE', route('users.destroy', $this->targetUser->external_id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $this->targetUser->id, 'deleted_at' => null]);
    }

    #[Test]
    public function owner_user_cannot_be_deleted_even_with_permission()
    {
        $this->actingAs($this->userWithPermission);

        $ownerRole = Role::where('name', 'owner')->first();
        $ownerUser = factory(User::class)->create();
        $ownerUser->attachRole($ownerRole);

        $response = $this->json('DELETE', route('users.destroy', $ownerUser->external_id));

        // Owner deletion is blocked by application logic and does not redirect
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $ownerUser->id, 'deleted_at' => null]);
    }
}
