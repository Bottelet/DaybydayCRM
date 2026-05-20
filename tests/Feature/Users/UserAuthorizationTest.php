<?php

namespace Tests\Feature\Users;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('authorization-fix')]
class UserAuthorizationTest extends AbstractTestCase
{
    use RefreshDatabase;

    private User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->targetUser = User::factory()->create();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    #[Test]
    public function it_user_with_user_delete_permission_can_delete_user()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::USER_DELETE);

        /* Act */
        $response = $this->json('DELETE', route('users.destroy', $this->targetUser->external_id));

        /* Assert */
        $response->assertStatus(302);
        $this->assertSoftDeleted('users', ['id' => $this->targetUser->id]);
    }

    #[Test]
    public function it_user_without_user_delete_permission_cannot_delete_user()
    {
        /* Arrange */
        $this->actingAs(User::factory()->create());

        /* Act */
        $response = $this->json('DELETE', route('users.destroy', $this->targetUser->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $this->targetUser->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_owner_user_cannot_be_deleted_even_with_permission()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::USER_DELETE);
        $ownerUser = User::factory()->withRole('owner')->create();

        /* Act */
        $response = $this->json('DELETE', route('users.destroy', $ownerUser->external_id));

        /* Assert */
        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['id' => $ownerUser->id, 'deleted_at' => null]);
    }
}
