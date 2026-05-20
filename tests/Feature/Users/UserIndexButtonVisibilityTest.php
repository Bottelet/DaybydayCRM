<?php

namespace Tests\Feature\Users;

use App\Enums\PermissionName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[Group('button-visibility')]
class UserIndexButtonVisibilityTest extends AbstractTestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_new_user_button_when_user_has_user_create_permission()
    {
        /* Arrange */
        $this->withPermissions(PermissionName::USER_CREATE);

        /* Act */
        $response = $this->get(route('users.index'));

        /* Assert */
        $response->assertOk();
        $response->assertSee(
            '<a href="'.route('users.create').'" class="btn btn-brand cta-btn pull-right">New User</a>',
            false
        );
    }

    #[Test]
    public function it_does_not_show_new_user_button_when_user_lacks_user_create_permission()
    {
        /* Arrange */
        $unprivilegedUser = User::factory()->withRole('employee')->create();
        $this->actingAs($unprivilegedUser);

        /* Act */
        $response = $this->get(route('users.index'));

        /* Assert */
        $response->assertOk();
        $response->assertDontSee(
            '<a href="'.route('users.create').'" class="btn btn-brand cta-btn pull-right">New User</a>',
            false
        );
    }
}
