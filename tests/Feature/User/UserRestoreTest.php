<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class UserRestoreTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_restores_soft_deleted_users()
    {
        /* Arrange */
        $user = User::factory()->create();
        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $user->id]);

        /** Act */
        $result = $user->restore();

        /* Assert */
        $this->assertTrue($result);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }
}
